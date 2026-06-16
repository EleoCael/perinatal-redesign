<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../module/db.config.php';
header('Content-Type: application/json');

if (empty($_SESSION['user_id']) || (($_SESSION['role'] ?? '') !== 'Patient')) {
    http_response_code(403);
    echo json_encode(['error' => 'unauthorized']);
    exit;
}

$childId = isset($_GET['child_id']) ? (int)$_GET['child_id'] : 0;
if ($childId <= 0) {
    echo json_encode(['error' => 'missing child_id']);
    exit;
}

/* helpers */
function fetch_one(mysqli $conn, string $sql, array $params = [], string $types = '')
{
    $stmt = $conn->prepare($sql);
    if (!$stmt) return null;
    if ($params) {
        $stmt->bind_param($types ?: str_repeat('s', count($params)), ...$params);
    }
    $stmt->execute();
    $res = $stmt->get_result();
    return $res ? $res->fetch_assoc() : null;
}
function fetch_all(mysqli $conn, string $sql, array $params = [], string $types = '')
{
    $stmt = $conn->prepare($sql);
    if (!$stmt) return [];
    if ($params) {
        $stmt->bind_param($types ?: str_repeat('s', count($params)), ...$params);
    }
    $stmt->execute();
    $res = $stmt->get_result();
    return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
}

/* child + infant (general info) */
$child = fetch_one(
    $conn,
    "SELECT p.first_name, p.middle_name, p.last_name, p.birth_date,
          i.sex, i.birth_weight, i.birth_height
   FROM patient p
   LEFT JOIN infant i ON i.patient_id = p.patient_id
   WHERE p.patient_id = ?
   LIMIT 1",
    [$childId],
    "i"
);
if (!$child) {
    echo json_encode(['error' => 'not found']);
    exit;
}

/* immunizations (match your tables/columns) */
$data = [
    'BCG' => null,
    'HEPA1' => null,
    'PENTA1' => null,
    'PENTA2' => null,
    'PENTA3' => null,
    'OPV1' => null,
    'OPV2' => null,
    'OPV3' => null,
    'PCV1' => null,
    'PCV2' => null,
    'PCV3' => null,
    'RVV1' => null,
    'RVV2' => null,
    'IPV' => null,
    'MCV1' => null,
    'MVC2' => null,
    'FIC' => null
];

/* BCG */
$r = fetch_one($conn, "SELECT bcg_date FROM bcg WHERE patient_id=? AND bcg_check=1 AND bcg_date<>'0000-00-00' ORDER BY bcg_date DESC LIMIT 1", [$childId], "i");
if ($r) $data['BCG'] = $r['bcg_date'];

/* Hepa B (at birth) */
$r = fetch_one($conn, "SELECT hepaB_date FROM hepab WHERE patient_id=? AND hepaB_date<>'0000-00-00' ORDER BY hepaB_date DESC LIMIT 1", [$childId], "i");
if ($r) $data['HEPA1'] = $r['hepaB_date'];

/* Pentavalent 1–3 */
foreach (fetch_all($conn, "SELECT pentavalent_type, pentavalent_date FROM pentavalent WHERE patient_id=? AND pentavalent_date<>'0000-00-00'", [$childId], "i") as $x) {
    if ($x['pentavalent_type'] === 'Pentavalent 1') $data['PENTA1'] = $x['pentavalent_date'];
    if ($x['pentavalent_type'] === 'Pentavalent 2') $data['PENTA2'] = $x['pentavalent_date'];
    if ($x['pentavalent_type'] === 'Pentavalent 3') $data['PENTA3'] = $x['pentavalent_date'];
}

/* OPV 1–3 */
foreach (fetch_all($conn, "SELECT opv_type, opv_date FROM opv WHERE patient_id=? AND opv_date<>'0000-00-00'", [$childId], "i") as $x) {
    if ($x['opv_type'] === 'Opv 1') $data['OPV1'] = $x['opv_date'];
    if ($x['opv_type'] === 'Opv 2') $data['OPV2'] = $x['opv_date'];
    if ($x['opv_type'] === 'Opv 3') $data['OPV3'] = $x['opv_date'];
}

/* PCV 1–3 */
foreach (fetch_all($conn, "SELECT pcv_type, pcv_date FROM pcv WHERE patient_id=? AND pcv_date<>'0000-00-00'", [$childId], "i") as $x) {
    if ($x['pcv_type'] === 'PCV 1') $data['PCV1'] = $x['pcv_date'];
    if ($x['pcv_type'] === 'PCV 2') $data['PCV2'] = $x['pcv_date'];
    if ($x['pcv_type'] === 'PCV 3') $data['PCV3'] = $x['pcv_date'];
}

// RVV
foreach (fetch_all($conn, "SELECT rvv_type, rvv_date FROM rota_virus_vaccine WHERE patient_id=? AND rvv_date<>'0000-00-00'",[$childId], "i") as $x) {
   if ($x['rvv_type'] === 'Rota Virus Vaccine 1') $data['RVV1'] = $x['rvv_date'];
   if ($x['rvv_type'] === 'Rota Virus Vaccine 2') $data['RVV2'] = $x['rvv_date'];
}

/* IPV (single) */
$r = fetch_one($conn, "SELECT ipv_date FROM ipv WHERE patient_id=? AND ipv_1=1 AND ipv_date<>'0000-00-00' ORDER BY ipv_date DESC LIMIT 1", [$childId], "i");
if ($r) $data['IPV'] = $r['ipv_date'];

/* MCV1 & MCV2 */
foreach (fetch_all($conn, "SELECT mcv_type, mcv_date FROM mcv WHERE patient_id=?  AND mcv_date<>'0000-00-00'", [$childId], "i") as $x) {
    if ($x['mcv_type'] === 'MCV1 (AMV)') $data['MCV1'] = $x['mcv_date'];
    if ($x['mcv_type'] === 'MCV2 (MMR)') $data['MVC2'] = $x['mcv_date'];   
}

/* FIC flag/date */
$r = fetch_one($conn, "SELECT fic_check, fic_date FROM fic WHERE patient_id=? ORDER BY fic_id DESC LIMIT 1", [$childId], "i");
if ($r && (int)$r['fic_check'] === 1 && !empty($r['fic_date']) && $r['fic_date'] !== '0000-00-00') $data['FIC'] = $r['fic_date'];

/* progress */
$required = ['BCG', 'HEPA1', 'PENTA1', 'PENTA2', 'PENTA3', 'OPV1', 'OPV2', 'OPV3', 'PCV1', 'PCV2', 'PCV3', 'IPV', 'MCV1', 'MVC2'];
$done = 0;
foreach ($required as $k) {
    if (!empty($data[$k])) $done++;
}
$total = count($required);
$percent = $total ? floor(($done / $total) * 100) : 0;

/* output */
echo json_encode([
    'child'   => $child,
    'immun'   => $data,
    'percent' => $percent
]);
