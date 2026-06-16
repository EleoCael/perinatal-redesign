<?php
require_once "../../../module/db.config.php";
session_start();

// CRITICAL: Verify session is valid
if (!isset($_SESSION['health_center_id']) || empty($_SESSION['health_center_id'])) {
    echo "<tr><td colspan='7' class='text-center text-danger'>Access denied: No health center assigned</td></tr>";
    exit();
}

$health_center_id = $_SESSION['health_center_id'];

// Get filter type
$filterType = isset($_POST['filter_type']) ? $_POST['filter_type'] : 'all';

// Base query with all necessary JOINs
$query = "SELECT DISTINCT p.* FROM patient p
          LEFT JOIN bcg ON p.patient_id = bcg.patient_id
          LEFT JOIN hepab ON p.patient_id = hepab.patient_id
          LEFT JOIN pentavalent ON p.patient_id = pentavalent.patient_id
          LEFT JOIN opv ON p.patient_id = opv.patient_id
          LEFT JOIN ipv ON p.patient_id = ipv.patient_id
          LEFT JOIN mcv ON p.patient_id = mcv.patient_id
          LEFT JOIN rota_virus_vaccine rvv ON p.patient_id = rvv.patient_id
          LEFT JOIN pcv ON p.patient_id = pcv.patient_id
          LEFT JOIN vitamin_a_infant vai ON p.patient_id = vai.patient_id
          LEFT JOIN iron_infant ii ON p.patient_id = ii.patient_id
          LEFT JOIN deworming_infant di ON p.patient_id = di.patient_id
          WHERE p.patient_type = 'infant' AND p.health_center_id = ?";

// Add filter conditions
$additional_where = "";

switch ($filterType) {
    case 'no_immunization':
        $additional_where = " AND (
            bcg.bcg_check IS NULL OR bcg.bcg_check = 0)
            AND hepab.hepaB_day IS NULL
            AND pentavalent.pentavalent_type IS NULL";
        break;
    
    case 'incomplete_immunization':
        $additional_where = " AND (
            (bcg.bcg_check IS NULL OR bcg.bcg_check = 0) OR
            hepab.hepaB_day IS NULL OR
            (pentavalent.pentavalent_type IS NULL OR pentavalent.pentavalent_type != 'Pentavalent 3') OR
            (opv.opv_type IS NULL OR opv.opv_type != 'Opv 3') OR
            (ipv.ipv_1 IS NULL OR ipv.ipv_1 = 0) OR
            mcv.mcv_type IS NULL
        )";
        break;
    
    case 'complete_immunization':
        $additional_where = " AND bcg.bcg_check = 1
                                  AND hepab.hepaB_day IS NOT NULL
                                  AND pentavalent.pentavalent_type = 'Pentavalent 3'
                                  AND opv.opv_type = 'Opv 3'
                                  AND ipv.ipv_1 = 1
                                  AND mcv.mcv_type IS NOT NULL";
        break;
    
    case 'complete_supplementation':
        $additional_where = " AND vai.vitamin_type IS NOT NULL
                                  AND ii.iron_type IS NOT NULL
                                  AND di.deworming_check = 1";
        break;
    
    case 'missing_bcg':
        $additional_where = " AND (bcg.bcg_check IS NULL OR bcg.bcg_check = 0)";
        break;
    
    case 'missing_hepaB':
        $additional_where = " AND hepab.hepaB_day IS NULL";
        break;
    
    case 'incomplete_pentavalent':
        $additional_where = " AND (pentavalent.pentavalent_type IS NULL OR pentavalent.pentavalent_type != 'Pentavalent 3')";
        break;
    
    case 'incomplete_opv':
        $additional_where = " AND (opv.opv_type IS NULL OR opv.opv_type != 'Opv 3')";
        break;
    
    case 'missing_ipv':
        $additional_where = " AND (ipv.ipv_1 IS NULL OR ipv.ipv_1 = 0)";
        break;
    
    case 'incomplete_mcv':
        $additional_where = " AND (mcv.mcv_type IS NULL OR mcv.mcv_type != 'MCV2 (MMR)')";
        break;
    
    case 'incomplete_rvv':
        $additional_where = " AND (rvv.rvv_type IS NULL OR rvv.rvv_type != 'Rota Virus Vaccine 2')";
        break;
    
    case 'incomplete_pcv':
        $additional_where = " AND (pcv.pcv_type IS NULL OR pcv.pcv_type != 'PCV 3')";
        break;
    
    case 'incomplete_vitA':
        $additional_where = " AND vai.vitamin_type IS NULL";
        break;
    
    case 'incomplete_iron':
        $additional_where = " AND ii.iron_type IS NULL";
        break;
    
    case 'missing_deworming':
        $additional_where = " AND (di.deworming_check IS NULL OR di.deworming_check = 0)";
        break;
    
    case 'all':
    default:
        // No additional filter
        break;
}

$query .= $additional_where;
$query .= " ORDER BY p.date_of_registration DESC";

// Prepare and execute the query
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $health_center_id);
$stmt->execute();
$result = $stmt->get_result();

// Display data in table
echo getData($result);

$stmt->close();

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
            $name_of_mother = htmlspecialchars($row['name_of_mother'] ?? 'N/A', ENT_QUOTES, 'UTF-8');
            $socio = htmlspecialchars($row['socio_economic_status'], ENT_QUOTES, 'UTF-8');
            $reg_date = htmlspecialchars($row['date_of_registration'], ENT_QUOTES, 'UTF-8');
            $family_serial = htmlspecialchars($row['family_serial_number'], ENT_QUOTES, 'UTF-8');

            $output .= "
                <tr>
                    <td>{$patient_id}</td>
                    <td>{$reg_date}</td>
                    <td>{$family_serial}</td>
                    <td>{$full_name}</td>
                    <td>{$name_of_mother}</td>
                    <td>{$socio}</td>
                    <td> 
                        <button class='btn btn-sm btn-success view_infant_btn' data-id='{$patient_id}'>
                            <i class='bi bi-eye-fill' style='color: white;'></i>
                        </button>
                        <button class='btn btn-sm btn-danger delete_infant_btn' data-id='{$patient_id}'>
                            <i class='bi bi-trash3-fill' style='color: white;'></i>
                        </button>
                    </td>
                </tr>
            ";
            
        }
    } else {
        $output = "
            <tr>
                <td colspan='7' class='text-center'>No infant records found for this filter</td>
            </tr>
        ";
    }
    return $output;
}
?>