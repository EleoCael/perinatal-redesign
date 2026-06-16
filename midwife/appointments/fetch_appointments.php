<?php
// appointments/fetch_appointments.php
session_start();
require_once "../../module/db.config.php";


header('Content-Type: application/json');


if ($_POST['action'] == 'fetchAppointments') {
    $page = isset($_POST['page']) ? (int)$_POST['page'] : 1;
    $limit = 10;
    $offset = ($page - 1) * $limit;

    // Build WHERE conditions based on filters
    $whereConditions = ["a.status != 'completed'"];
    $params = [];
    $types = "";

    if (isset($_SESSION['health_center_id'])) {
    $whereConditions[] = "a.health_center_id = ?";
    $params[] = $_SESSION['health_center_id'];
    $types .= "i";
        }

    // Date filter
    if (!empty($_POST['filter_date'])) {
        $whereConditions[] = "a.appointment_date = ?";
        $params[] = $_POST['filter_date'];
        $types .= "s";
    }

    // Status filter
    if (!empty($_POST['filter_status'])) {
        $whereConditions[] = "a.status = ?";
        $params[] = $_POST['filter_status'];
        $types .= "s";
    }

    // Type filter
    if (!empty($_POST['filter_type'])) {
        $whereConditions[] = "a.appointment_type = ?";
        $params[] = $_POST['filter_type'];
        $types .= "s";
    }

    $whereClause = implode(" AND ", $whereConditions);

    // Get appointments
    $sql = "SELECT a.*, p.first_name, p.last_name, p.contact_number 
            FROM appointments a 
            JOIN patient p ON a.patient_id = p.patient_id 
            WHERE $whereClause 
            ORDER BY a.appointment_date ASC, a.appointment_time ASC 
            LIMIT ? OFFSET ?";

    $params[] = $limit;
    $params[] = $offset;
    $types .= "ii";

    $stmt = $conn->prepare($sql);
    if ($types) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();

    $table_data = '';

    if ($result->num_rows > 0) {
        $counter = $offset + 1;
        while ($row = $result->fetch_assoc()) {
            // In fetch_appointments.php, update the Complete button line:
            $table_data .= '
                    <tr>
                        <td>' . htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) . '</td>
                        <td>' . htmlspecialchars($row['appointment_date']) . '</td>
                        <td>' . htmlspecialchars(date('g:i A', strtotime($row['appointment_time']))) . '</td>
                        <td><span class="badge bg-info">' . htmlspecialchars($row['appointment_type']) . '</span></td>
                        <td><span class="badge bg-warning">' . htmlspecialchars($row['status']) . '</span></td>
                        <td>' . htmlspecialchars($row['contact_number']) . '</td>
                        <td>
                            <button class="btn btn-sm btn-success complete-appointment-btn" 
                                    data-id="' . $row['appointment_id'] . '" 
                                    data-patient-id="' . $row['patient_id'] . '"
                                    data-patient-name="' . htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) . '">
                                Complete
                            </button>
                            <button class="btn btn-sm btn-primary set-next-btn" 
                                    data-id="' . $row['patient_id'] . '" 
                                    data-name="' . htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) . '">
                                 Set Next
                            </button>
                        </td>
                    </tr>';
        }
    } else {
        $table_data = '<tr><td colspan="7" class="text-center">No appointments found.</td></tr>';
    }

    // For now, we'll just return the table data
    // We can add pagination later if needed
    echo json_encode([
        'table_data' => $table_data,
        'pagination_links' => '' // We'll implement pagination later if needed
    ]);

    $stmt->close();
    $conn->close();
}
