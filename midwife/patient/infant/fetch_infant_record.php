<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once "../../../module/db.config.php";
session_start();

// CRITICAL: Verify session is valid
if (!isset($_SESSION['health_center_id']) || empty($_SESSION['health_center_id'])) {
    header('Content-Type: application/json');
    die(json_encode(['error' => 'Access denied: No health center assigned']));
}

$health_center_id = $_SESSION['health_center_id'];

// delete infant record
if (isset($_POST['action']) && $_POST['action'] == 'delete_record') {
    if (!isset($_POST['patient_id']) || empty($_POST['patient_id'])) {
        header('Content-Type: application/json');
        die(json_encode(['error' => 'Invalid patient ID']));
    }

    $id = (int)$_POST['patient_id'];

    // VERIFY infant belongs to this health center BEFORE deleting
    $verify_query = "SELECT patient_id FROM patient WHERE patient_id = ? AND health_center_id = ?";
    $stmt_verify = $conn->prepare($verify_query);
    $stmt_verify->bind_param('ii', $id, $health_center_id);
    $stmt_verify->execute();
    $verify_result = $stmt_verify->get_result();

    if ($verify_result->num_rows === 0) {
        header('Content-Type: application/json');
        die(json_encode(['error' => 'Access denied: Infant record not found or does not belong to your health center']));
    }
    $stmt_verify->close();

    // Now delete - filtered by both patient_id AND health_center_id
    $delete_query = "DELETE FROM patient WHERE patient_id = ? AND health_center_id = ?";
    $stmt = $conn->prepare($delete_query);
    $stmt->bind_param('ii', $id, $health_center_id);
    $stmt->execute();
    $stmt->close();

    header('Content-Type: application/json');
    echo json_encode(['success' => true]);
    exit();
}

// search infant record
if (isset($_POST['action']) && $_POST['action'] == 'search_record') {
    if (!isset($_POST['infant_name']) || empty($_POST['infant_name'])) {
        header('Content-Type: application/json');
        die(json_encode(['error' => 'Search term is required']));
    }

    $search = '%' . $_POST['infant_name'] . '%';
    
    // CRITICAL: Filter by health_center_id AND patient_type
    $search_query = "SELECT * FROM patient 
                    WHERE (first_name LIKE ? OR middle_name LIKE ? OR last_name LIKE ?)
                    AND patient_type = 'infant'
                    AND health_center_id = ?
                    ORDER BY patient_id ASC";

    $stmt = $conn->prepare($search_query);
    $stmt->bind_param('sssi', $search, $search, $search, $health_center_id);
    $stmt->execute();
    $result = $stmt->get_result();

    echo getInfantData($result);
    $stmt->close();
    exit();
}

// fetch infant data
if (isset($_POST['action']) && $_POST['action'] === 'fetchData') {
    $limit = 15;
    $page = isset($_POST['page']) ? (int)$_POST['page'] : 1;
    $start = ($page - 1) * $limit;

    // CRITICAL: Filter by health_center_id for count
    $count_query = "SELECT COUNT(*) AS total FROM patient WHERE patient_type = 'infant' AND health_center_id = ?";
    $count_stmt = $conn->prepare($count_query);
    $count_stmt->bind_param('i', $health_center_id);
    $count_stmt->execute();
    $total_records = $count_stmt->get_result()->fetch_assoc()['total'];
    $count_stmt->close();
    
    $pages = ceil($total_records / $limit);

    // CRITICAL: Filter by health_center_id in fetch query
    $fetch_query = "SELECT * FROM patient WHERE patient_type = 'infant' AND health_center_id = ? ORDER BY patient_id ASC LIMIT ? OFFSET ?";
    $stmt = $conn->prepare($fetch_query);
    $stmt->bind_param("iii", $health_center_id, $limit, $start);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();

    $table_data = getInfantData($result);
    $pagination_links = generatePaginationLinks($pages, $page);

    header('Content-Type: application/json');
    echo json_encode([
        'table_data' => $table_data,
        'pagination_links' => $pagination_links
    ]);
    exit();
}

// --- FUNCTIONS ---

function generatePaginationLinks($total_pages, $current_page): string {
    $output = '<ul class="pagination justify-content-center">';
    $prev_disabled = ($current_page <= 1) ? 'disabled' : '';
    $output .= "<li class='page-item $prev_disabled'><a class='page-link' href='#' data-page='" . ($current_page - 1) . "'>&laquo;</a></li>";

    // Smarter pagination (like the maternal one)
    $links_per_side = 2;
    $start_page = max(1, $current_page - $links_per_side);
    $end_page = min($total_pages, $current_page + $links_per_side);

    if ($start_page > 1) {
        $output .= "<li class='page-item'><a class='page-link' href='#' data-page='1'>1</a></li>";
        if ($start_page > 2) {
            $output .= "<li class='page-item disabled'><span class='page-link'>...</span></li>";
        }
    }

    for ($i = $start_page; $i <= $end_page; $i++) {
        $active = ($i == $current_page) ? 'active' : '';
        $output .= "<li class='page-item $active'><a class='page-link' href='#' data-page='$i'>$i</a></li>";
    }

    if ($end_page < $total_pages) {
        if ($end_page < $total_pages - 1) {
            $output .= "<li class='page-item disabled'><span class='page-link'>...</span></li>";
        }
        $output .= "<li class='page-item'><a class='page-link' href='#' data-page='$total_pages'>$total_pages</a></li>";
    }

    $next_disabled = ($current_page >= $total_pages) ? 'disabled' : '';
    $output .= "<li class='page-item $next_disabled'><a class='page-link' href='#' data-page='" . ($current_page + 1) . "'>&raquo;</a></li>";
    $output .= '</ul>';
    return $output;
}

function getInfantData($result): string {
    $output = "";
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $full_name = htmlspecialchars($row['last_name'], ENT_QUOTES, 'UTF-8') . ", " . 
                        htmlspecialchars($row['first_name'], ENT_QUOTES, 'UTF-8');
            if (!empty($row['middle_name'])) {
                $full_name .= " " . htmlspecialchars($row['middle_name'], ENT_QUOTES, 'UTF-8');
            }

            $patient_id = (int)$row['patient_id'];
            $reg_date = htmlspecialchars($row['date_of_registration'], ENT_QUOTES, 'UTF-8');
            $family_serial = htmlspecialchars($row['family_serial_number'], ENT_QUOTES, 'UTF-8');
            $mother_name = htmlspecialchars($row['name_of_mother'] ?? 'N/A', ENT_QUOTES, 'UTF-8');
            $socio_status = htmlspecialchars($row['socio_economic_status'], ENT_QUOTES, 'UTF-8');

            $output .= "
                <tr>
                    <td>{$patient_id}</td>
                    <td>{$reg_date}</td>
                    <td>{$family_serial}</td>
                    <td>{$full_name}</td>
                    <td>{$mother_name}</td>
                    <td>{$socio_status}</td>
                    <td>
                        <button class='btn btn-sm btn-success view_infant_btn' data-id='{$patient_id}'>
                            <i class='bi bi-eye-fill' style='color:white;'></i>
                        </button>
                        <button class='btn btn-sm btn-primary edit_infant_btn' data-id='{$patient_id}'>
                            <i class='bi bi-pencil-square' style='color:white;'></i>
                        </button>
                        <button class='btn btn-sm btn-danger delete_infant_btn' data-id='{$patient_id}'>
                            <i class='bi bi-trash3-fill' style='color:white;'></i>
                        </button>
                    </td>
                </tr>
            ";
        }
    } else {
        $output = "<tr><td colspan='7' class='text-center'>No infant records found</td></tr>";
    }
    return $output;
}
?>