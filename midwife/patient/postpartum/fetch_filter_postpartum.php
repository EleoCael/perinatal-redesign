<?php
require_once '../../../module/db.config.php';
session_start();

header('Content-Type: text/html; charset=utf-8');

// Verify session
if (!isset($_SESSION['health_center_id']) || empty($_SESSION['health_center_id'])) {
    echo "<tr><td colspan='7' class='text-center text-danger'>Access denied: No health center assigned</td></tr>";
    exit;
}

$health_center_id = $_SESSION['health_center_id'];
$filter_type = $_POST['filter_type'] ?? 'all';

try {
    $query = "SELECT p.patient_id, p.date_of_registration, p.family_serial_number, 
              p.first_name, p.middle_name, p.last_name, p.address, p.socio_economic_status
              FROM patient p 
              WHERE p.patient_type = 'postpartum_mother' 
              AND p.health_center_id = ?";
    
    $additional_where = "";
    
    // Build filter conditions
    switch ($filter_type) {      
        case 'complete_supplementation':
            $additional_where = " AND p.patient_id IN (
                SELECT pps.patient_id
                FROM post_partum_supp pps
                INNER JOIN post_vitamin pv ON pps.patient_id = pv.patient_id
                WHERE pps.patient_id = p.patient_id
                AND pv.vitamin_a = 1
                GROUP BY pps.patient_id
                HAVING COUNT(DISTINCT CASE 
                    WHEN pps.iron_folic_month_given IN ('1st month postpartum', '2nd month postpartum', '3rd month postpartum')
                    THEN pps.iron_folic_month_given 
                END) = 3
            )";
            break;
            
        case 'missing_iron_post':
            $additional_where = " AND p.patient_id NOT IN (
                SELECT pps.patient_id
                FROM post_partum_supp pps
                WHERE pps.patient_id = p.patient_id
                GROUP BY pps.patient_id
                HAVING COUNT(DISTINCT CASE 
                    WHEN pps.iron_folic_month_given IN ('1st month postpartum', '2nd month postpartum', '3rd month postpartum')
                    THEN pps.iron_folic_month_given 
                END) = 3
            )";
            break;
            
        case 'missing_vitA':
            $additional_where = " AND p.patient_id IN (
                SELECT DISTINCT p2.patient_id 
                FROM patient p2
                LEFT JOIN post_vitamin pv ON p2.patient_id = pv.patient_id
                WHERE p2.patient_id = p.patient_id
                AND (
                    pv.vitamin_a_id IS NULL OR
                    pv.vitamin_a IS NULL OR 
                    pv.vitamin_a = 0
                )
            )";
            break;

         case 'all':
             default:
      
            break;
    }
    
    $query .= $additional_where;
    $query .= " ORDER BY p.date_of_registration DESC";
    
    $stmt = $conn->prepare($query);
    
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $stmt->bind_param('i', $health_center_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $count = 1;
    $output = '';
    
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $full_name = htmlspecialchars($row['last_name'], ENT_QUOTES, 'UTF-8') . ", " . 
                        htmlspecialchars($row['first_name'], ENT_QUOTES, 'UTF-8');
            if (!empty($row['middle_name'])) {
                $full_name .= " " . htmlspecialchars($row['middle_name'], ENT_QUOTES, 'UTF-8');
            }
            
            $output .= "<tr>";
            $output .= "<td>" . htmlspecialchars($row['patient_id']) . "</td>";
            $output .= "<td>" . htmlspecialchars($row['date_of_registration']) . "</td>";
            $output .= "<td>" . htmlspecialchars($row['family_serial_number']) . "</td>";
            $output .= "<td>" . $full_name . "</td>";
            $output .= "<td>" . htmlspecialchars($row['address']) . "</td>";
            $output .= "<td>" . htmlspecialchars($row['socio_economic_status']) . "</td>";
            $output .= "<td>
                    <button class='btn btn-sm btn-success view_postpartum_btn' data-id='" . $row['patient_id'] . "'>
                        <i class='bi bi-eye-fill' style='color: white;'></i>
                    </button>
                    <button class='btn btn-sm btn-primary edit_postpartum_btn' data-id='" . $row['patient_id'] . "'>
                        <i class='bi bi-pencil-square' style='color: white;'></i>
                    </button>
                    <button class='btn btn-sm btn-danger delete_postpartum_btn' data-id='" . $row['patient_id'] . "'>
                        <i class='bi bi-trash3-fill' style='color: white;'></i>
                    </button>
                </td>";
            $output .= "</tr>";
        }
    } else {
        $output = "<tr><td colspan='7' class='text-center'>No records found for selected filter</td></tr>";
    }
    
    echo $output;
    $stmt->close();
    
} catch (Exception $e) {
    echo "<tr><td colspan='7' class='text-center text-danger'>Error: " . htmlspecialchars($e->getMessage()) . "</td></tr>";
}

$conn->close();
?>