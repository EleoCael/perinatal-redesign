<?php
require_once "../../../module/db.config.php";
session_start();

if (!isset($_SESSION['health_center_id']) || empty($_SESSION['health_center_id'])) {
    header('Content-Type: application/json');
    die(json_encode(['error' => 'Access denied: No health center assigned']));
}

$health_center_id = $_SESSION['health_center_id'];

if (isset($_POST['action']) && $_POST['action'] == 'delete_record') {
    if (!isset($_POST['patient_id']) || empty($_POST['patient_id'])) {
        header('Content-Type: application/json');
        die(json_encode(['error' => 'Invalid patient ID']));
    }
    
    $patient_id = (int)$_POST['patient_id'];

    // VERIFY patient belongs to this health center BEFORE deleting
    $verify_query = "SELECT patient_id FROM patient WHERE patient_id = ? AND health_center_id = ?";
    $stmt_verify = $conn->prepare($verify_query);
    $stmt_verify->bind_param('ii', $patient_id, $health_center_id);
    $stmt_verify->execute();
    $verify_result = $stmt_verify->get_result();

    if ($verify_result->num_rows === 0) {
        header('Content-Type: application/json');
        die(json_encode(['error' => 'Access denied: Patient not found or does not belong to your health center']));
    }
    $stmt_verify->close();

    // Now delete - filtered by both patient_id AND health_center_id
    $delete_query = "DELETE FROM patient WHERE patient_id = ? AND health_center_id = ?";
    $stmt_delete = $conn->prepare($delete_query);
    $stmt_delete->bind_param('ii', $patient_id, $health_center_id);
    $stmt_delete->execute();
    $stmt_delete->close();

    header('Content-Type: application/json');
    echo json_encode(['success' => true]);
    exit();
}

// search record
if (isset($_POST['action']) && $_POST['action'] == 'search_record') {
    if (!isset($_POST['maternal_name']) || empty($_POST['maternal_name'])) {
        header('Content-Type: application/json');
        die(json_encode(['error' => 'Search term is required']));
    }

    $search_by_name = '%' . $_POST['maternal_name'] . '%';
    
    // CRITICAL: Filter by health_center_id AND patient_type
    $search_query = "SELECT * FROM patient WHERE 
        (first_name LIKE ? OR middle_name LIKE ? OR last_name LIKE ?) AND
        patient_type = 'mother' AND
        health_center_id = ?
        ORDER BY patient.patient_id ASC";
    
    $stmt_search = $conn->prepare($search_query);
    $stmt_search->bind_param('sssi', $search_by_name, $search_by_name, $search_by_name, $health_center_id);
    $stmt_search->execute();
    $search_result = $stmt_search->get_result();

    echo getData($search_result);
    $stmt_search->close();
    exit();
}

// fetching data to display records in table
if (isset($_POST['action']) && $_POST['action'] === 'fetchData') {

    // pagination
    $limit = 15;
    $page = isset($_POST['page']) ? (int)$_POST['page'] : 1;
    $start = ($page - 1) * $limit;

    // CRITICAL: Filter by health_center_id for count
    $count_query = "SELECT count(*) AS total FROM patient WHERE patient_type = 'mother' AND health_center_id = ?";
    $count_stmt = $conn->prepare($count_query);
    $count_stmt->bind_param('i', $health_center_id);
    $count_stmt->execute();
    $total_records = $count_stmt->get_result()->fetch_assoc()['total'];
    $count_stmt->close();

    $pages = ceil($total_records / $limit);

    // CRITICAL: Filter by health_center_id in fetch query
    $fetch_maternal_record = "SELECT * FROM patient 
                              WHERE patient_type = 'mother' AND health_center_id = ?
                              ORDER BY patient.patient_id ASC 
                              LIMIT ? OFFSET ?";
    $stmt = $conn->prepare($fetch_maternal_record);
    $stmt->bind_param("iii", $health_center_id, $limit, $start);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();

    $table_data = getData($result);
    $pagination_links = generatePaginationLinks($pages, $page);
    
    header('Content-Type: application/json');
    echo json_encode([
        'table_data' => $table_data,
        'pagination_links' => $pagination_links
    ]);

    exit();
}


// pagination function
function generatePaginationLinks($total_pages, $current_page): string
{
    $output = "";
    $output .= '<ul class="pagination justify-content-center">';

    // Previous button
    $prev_disabled = ($current_page <= 1) ? 'disabled' : '';
    $output .= "<li class='page-item {$prev_disabled}'><a class='page-link' href='#' data-page='" . ($current_page - 1) . "'>&laquo;</a></li>";

    // Page links
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
        $active_class = ($i === $current_page) ? 'active' : '';
        $output .= "<li class='page-item {$active_class}'><a class='page-link' href='#' data-page='{$i}'>{$i}</a></li>";
    }

    if ($end_page < $total_pages) {
        if ($end_page < $total_pages - 1) {
            $output .= "<li class='page-item disabled'><span class='page-link'>...</span></li>";
        }
        $output .= "<li class='page-item'><a class='page-link' href='#' data-page='{$total_pages}'>{$total_pages}</a></li>";
    }

    // Next button
    $next_disabled = ($current_page >= $total_pages) ? 'disabled' : '';
    $output .= "<li class='page-item {$next_disabled}'><a class='page-link' href='#' data-page='" . ($current_page + 1) . "'>&raquo;</a></li>";

    $output .= '</ul>';
    return $output;
}

// display data in table
function getData($result): string
{
    $output = "";

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $full_name = htmlspecialchars($row['last_name'], ENT_QUOTES, 'UTF-8') . ", " . 
                        htmlspecialchars($row['first_name'], ENT_QUOTES, 'UTF-8');
            if (!empty($row['middle_name'])) {
                $full_name .= " " . htmlspecialchars($row['middle_name'], ENT_QUOTES, 'UTF-8');
            }

            $patient_id = (int)$row['patient_id'];
            $address = htmlspecialchars($row['address'], ENT_QUOTES, 'UTF-8');
            $socio = htmlspecialchars($row['socio_economic_status'], ENT_QUOTES, 'UTF-8');
            $reg_date = htmlspecialchars($row['date_of_registration'], ENT_QUOTES, 'UTF-8');
            $family_serial = htmlspecialchars($row['family_serial_number'], ENT_QUOTES, 'UTF-8');

            $output .= "
                <tr>
                    <td>{$patient_id}</td>
                    <td>{$reg_date}</td>
                    <td>{$family_serial}</td>
                    <td>{$full_name}</td>
                    <td>{$address}</td>
                    <td>{$socio}</td>
                    <td> 
                        <button class='btn btn-sm btn-success view_btn' data-id='{$patient_id}'>
                            <i class='bi bi-eye-fill' style='color: white;'></i>
                        </button>
                        <button class='btn btn-sm btn-primary edit_btn' data-id='{$patient_id}'>
                            <i class='bi bi-pencil-square' style='color: white;'></i>
                        </button>
                        <button class='btn btn-sm btn-danger delete_btn' data-id='{$patient_id}'>
                            <i class='bi bi-trash3-fill' style='color: white;'></i>
                        </button>
                    </td>
                </tr>
            ";
        }
    } else {
        $output = "
            <tr>
                <td colspan='7' class='text-center'>No records found</td>
            </tr>
        ";
    }
    return $output;
}
?>