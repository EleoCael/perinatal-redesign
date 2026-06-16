<?php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../module/db.config.php'; 


if (!function_exists('h')) {
    function h($s)
    {
        return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
    }
}
if (!function_exists('d')) {
    function d($dateStr)
    {
        if (!$dateStr || $dateStr === '0000-00-00') return '—';
        $ts = strtotime($dateStr);
        return $ts ? date('M d, Y', $ts) : '—';
    }
}
if (!function_exists('getcol')) {
    function getcol(array $row, string $key, $fallback = '—')
    {
        return array_key_exists($key, $row) && $row[$key] !== '' ? $row[$key] : $fallback;
    }
}
if (!function_exists('fetch_one')) {
    function fetch_one(mysqli $conn, string $sql, array $params = [], string $types = '')
    {
        $stmt = $conn->prepare($sql);
        if (!$stmt) throw new Exception($conn->error);
        if ($params) {
            $stmt->bind_param($types ?: str_repeat('s', count($params)), ...$params);
        }
        $stmt->execute();
        $res = $stmt->get_result();
        return $res ? $res->fetch_assoc() : null;
    }
}
if (!function_exists('fetch_all')) {
    function fetch_all(mysqli $conn, string $sql, array $params = [], string $types = '')
    {
        $stmt = $conn->prepare($sql);
        if (!$stmt) throw new Exception($conn->error);
        if ($params) {
            $stmt->bind_param($types ?: str_repeat('s', count($params)), ...$params);
        }
        $stmt->execute();
        $res = $stmt->get_result();
        return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    }
}

if (empty($_SESSION['user_id']) || (($_SESSION['role'] ?? '') !== 'Patient')) {
    header('Location: ../system/login/login.php');
    exit;
}

$userId = (int)$_SESSION['user_id'];

$mother = fetch_one($conn, "SELECT * FROM patient WHERE user_id=? LIMIT 1", [$userId], "i");
if (!$mother && !empty($_GET['mother_id'])) {
    $mother = fetch_one($conn, "SELECT * FROM patient WHERE patient_id=? LIMIT 1", [(int)$_GET['mother_id']], "i");
}
$motherId   = $mother ? (int)$mother['patient_id'] : 0;
$motherName = $mother ? trim(($mother['first_name'] ?? '') . ' ' . ($mother['middle_name'] ?? '') . ' ' . ($mother['last_name'] ?? '')) : 'Patient';

$children = [];
if ($motherId) {
    $children = fetch_all(
        $conn,
        "SELECT p.*, i.sex
     FROM patient p
     LEFT JOIN infant i ON i.patient_id = p.patient_id
     WHERE p.patient_type='infant' AND p.mother_id=?
     ORDER BY p.birth_date DESC",
        [$motherId],
        "i"
    );
}
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Patient Portal - Perinatal Care System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="../assets/css/patient.css">
</head>

<body>
    <nav class="navbar navbar-expand-lg main-navbar">
        <div class="container">
            <a class="navbar-brand" href="#" onclick="loadHomePage(); return false;">
                Perinatal Care
            </a>
            <a class="btn btn-logout" href="../module/logout.php" onclick="confirmLogout(); return false;">
                <i class="bi bi-box-arrow-right"></i>Logout
            </a>
        </div>
    </nav>

    <main class="container main-content">
        <div class="welcome-header">
            <h1>Welcome back, <?= h($motherName) ?>!</h1>
        </div>

        <?php if ($mother): ?>
            <div class="row g-4">
                <!-- Left: Patient's Profile -->
                <div class="col-lg-6">
                    <div class="card-modern ">
                        <div class="card-header-modern bg-light">
                            <h5 class="m-0 fw-bold text-primary"><i class="bi bi-person-fill"></i>Mother's Info</h5>
                        </div>
                        <div class="card-body-modern">
                            <div class="info-item">
                                <div class="info-label">Full Name</div>
                                <div class="info-value"><?= h($motherName) ?></div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="info-item">
                                        <div class="info-label">Birthday</div>
                                        <div class="info-value"><?= d(getcol($mother, 'birth_date', '')) ?></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="info-item">
                                        <div class="info-label">Contact</div>
                                        <div class="info-value"><?= h(getcol($mother, 'contact_number')) ?></div>
                                    </div>
                                </div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Address</div>
                                <div class="info-value">
                                    <?php
                                    $addr = getcol($mother, 'address', '');
                                    $brgy = array_key_exists('barangay', $mother) ? $mother['barangay'] : '';
                                    echo h(trim($addr . ' ' . $brgy)) ?: '—';
                                    ?>
                                </div>
                            </div>
                            <div class="info-item mb-0">
                                <div class="info-label">Email</div>
                                <div class="info-value"><?= h(getcol($mother, 'email')) ?></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right: My Children -->
                <div class="col-lg-6">
                    <div class="card-modern">
                        <div class="card-header-modern  bg-light">
                            <h5 class="m-0 fw-bold text-primary"><i class="bi bi-people-fill"></i>My Children</h5>
                        </div>
                        <div class="card-body-modern">
                            <?php if (!$children): ?>
                                <div class="alert alert-modern alert-info m-0">
                                    <i class="bi bi-info-circle-fill"></i>
                                    <span>No children linked to this account.</span>
                                </div>
                            <?php else: ?>
                                <?php foreach ($children as $c):
                                    $cid  = (int)$c['patient_id'];
                                    $name = trim(($c['first_name'] ?? '') . ' ' . ($c['middle_name'] ?? '') . ' ' . ($c['last_name'] ?? ''));
                                    $sex  = strtolower($c['sex'] ?? '');
                                    $sexIcon = ($sex === 'female' ? '♀' : ($sex === 'male' ? '♂' : ''));
                                    $badgeClass = ($sex === 'female' ? 'bg-danger-gradient' : 'bg-primary-gradient');
                                ?>
                                    <div class="child-card" data-child-id="<?= $cid ?>">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <div class="d-flex align-items-center gap-3">
                                                
                                                <div>
                                                    <div class="child-name ">
                                                        <?= h($name) ?>
                                                        
                                                    </div>
                                                   
                                                </div>
                                            </div>
                                            <button class="btn btn-view view-child" data-id="<?= $cid ?>">
                                                
                                            View</button>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SEPARATE SECTION FOR CHILD INFO -->
            <div id="child-info-section"></div>

        <?php else: ?>
            <div class="alert alert-modern alert-warning">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <span>We couldn't find your patient profile. Please contact your midwife.</span>
            </div>
        <?php endif; ?>
    </main>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script>
        $(document).on('click', '.view-child', function() {
            const id = $(this).data('id');
            const $section = $('#child-info-section');

            // Remove active state from all child cards and buttons
            $('.child-card').removeClass('active');
            $('.btn-view').removeClass('active');

            // Add active state to clicked card and button
            $(this).closest('.child-card').addClass('active');
            $(this).addClass('active');

            $section.html('<div class="loading-overlay"><div class="loading-spinner"></div></div>');

            // Smooth scroll to the section
            $('html, body').animate({
                scrollTop: $section.offset().top - 100
            }, 500);

            $.get('child_portal.php', {
                    child_id: id
                }, function(res) {
                    if (!res || res.error) {
                        $section.html('<div class="alert alert-modern alert-danger fade-in"><i class="bi bi-exclamation-triangle-fill"></i><span>Unable to load child data.</span></div>');
                        return;
                    }
                    const c = res.child || {};
                    const im = res.immun || {};
                    const percent = res.percent || 0;
                    const remaining = Math.max(0, 100 - percent);
                    const fic = im.FIC || null;

                    const label = {
                        BCG: 'BCG',
                        HEPA1: 'Hepatitis B',
                        PENTA1: 'Pentavalent (Dose 1)',
                        PENTA2: 'Pentavalent (Dose 2)',
                        PENTA3: 'Pentavalent (Dose 3)',
                        OPV1: 'OPV (Dose 1)',
                        OPV2: 'OPV (Dose 2)',
                        OPV3: 'OPV (Dose 3)',
                        PCV1: 'PCV (Dose 1)',
                        PCV2: 'PCV (Dose 2)',
                        PCV3: 'PCV (Dose 3)',
                        RVV1: 'RVV (Dose 1)',
                        RVV2: 'RVV (Dose 2)',
                        IPV: 'IPV',
                        MCV1: 'MCV1 (AMV)',
                        MCV2: 'MCV2 (MMR)'
                    };

                    const sections = {
                        'BCG Vaccine': ['BCG'],
                        'HEPA B1': ['HEPA1'],
                        'PENTAVALENT': ['PENTA1', 'PENTA2', 'PENTA3'],
                        'OPV': ['OPV1', 'OPV2', 'OPV3'],
                        'Inactivated Polio Vaccine(IPV)': ['IPV'],
                        'MCV': ['MCV1', 'MCV2'],
                        'Rota Virus Vaccine (RVV)': ['RVV1', 'RVV2'],
                        'Pneumococcal Conjugate Vaccines (PCV)' : ['PCV1', 'PCV2', 'PCV3']
                    };

                    const fmt = d => {
                        if (!d || d === '0000-00-00') return '—';
                        const dt = new Date(d + 'T00:00:00');
                        return dt.toLocaleDateString(undefined, {
                            month: 'short',
                            day: '2-digit',
                            year: 'numeric'
                        });
                    };

                    let vaccineHtml = '';
                    let sectionIndex = 1;

                    for (const [sectionTitle, vaccines] of Object.entries(sections)) {
                            vaccineHtml += `
                        <h6 class="vaccine-section-header">
                        ${sectionTitle}
                        </h6>
                        <div class="vaccine-grid">
                    `;

                        vaccines.forEach(k => {
                            const isGiven = im[k] ? true : false;
                            const cardClass = isGiven ? 'vaccine-card-given' : 'vaccine-card-not-given';
                            const badgeClass = isGiven ? 'badge-given' : 'badge-not-given';
                            const badgeText = isGiven ? 'Given' : 'Not Given';
                            const icon = isGiven ? 'check-circle-fill' : 'calendar-event';

                            vaccineHtml += `
                                <div class="vaccine-card ${cardClass}">
                                    <div class="vaccine-icon-wrapper">
                                    <div class="vaccine-icon">
                                        <i class="bi bi-${icon}"></i>
                                    </div>
                                    </div>
                                    <div class="vaccine-content">
                                    <div class="vaccine-header">
                                        <h6 class="vaccine-name">${label[k] || k}</h6>
                                        <span class="vaccine-badge ${badgeClass}">${badgeText}</span>
                                    </div>
                                    <div class="vaccine-date">
                                        <i class="bi bi-calendar3"></i>
                                        <span>${isGiven ? fmt(im[k]) : 'No Date'}</span>
                                    </div>
                                    </div>
                                </div>
                                `;
                        });

                        vaccineHtml += '</div>';
                        sectionIndex++;
                    }

                    const progressColor = percent >= 80 ? 'success' : (percent >= 50 ? 'warning' : 'danger');

                    const html = `
                <div class="card-modern fade-in">
                    <div class="card-header-modern  bg-light">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <h5 class="mb-0 fw-bold text-primary">
                        <i class="bi bi-clipboard2-pulse-fill"></i>
                        ${[c.first_name||'', c.middle_name||'', c.last_name||''].join(' ').replace(/\s+/g,' ').trim()}'s Health Record
                        </h5>
                        
                    </div>
                    </div>
                    <div class="card-body-modern">
                    <h6 class="section-header">
                        <i class="bi bi-info-circle-fill"></i>
                        General Information
                    </h6>
                    <div class="info-tile-grid">
                        <div class="info-tile">
                        <div class="info-tile-label">Date of Birth</div>
                        <div class="info-tile-value">${fmt(c.birth_date)}</div>
                        </div>
                        <div class="info-tile">
                        <div class="info-tile-label">Birth Weight</div>
                        <div class="info-tile-value">${c.birth_weight ? (c.birth_weight+' g') : '—'}</div>
                        </div>
                        <div class="info-tile">
                        <div class="info-tile-label">Birth Height</div>
                        <div class="info-tile-value">${c.birth_height ? (c.birth_height+' cm') : '—'}</div>
                        </div>
                        <div class="info-tile">
                        <div class="info-tile-label">Sex</div>
                        <div class="info-tile-value">${(c.sex||'').toString().charAt(0).toUpperCase()+ (c.sex||'').toString().slice(1)}</div>
                        </div>
                    </div>

                    <h6 class="section-header">
                        <i class="bi bi-heart-pulse-fill"></i>
                        Immunization Records
                    </h6>
                    ${vaccineHtml}
                    </div>
                </div>
                `;
                    $section.html(html);
                }, 'json')
                .fail(function() {
                    $section.html('<div class="alert alert-modern alert-danger fade-in"><i class="bi bi-exclamation-triangle-fill"></i><span>Network error while loading child data.</span></div>');
                });
        });

        function loadHomePage() {
            // Remove active state from all child cards
            $('.child-card').removeClass('active');
            $('.btn-view').removeClass('active');

            // Clear child info section
            $('#child-info-section').html('');

            // Scroll to top
            $('html, body').animate({
                scrollTop: 0
            }, 500);
        }

         function confirmLogout() {
            Swal.fire({
                title: 'Are you sure?',
                text: "You will be logged out of the system!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, logout!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Redirect to logout.php
                    window.location.href = '../module/logout.php';
                }
            });
        }
    </script>
</body>

</html>