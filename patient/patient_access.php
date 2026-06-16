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

// Get mother's record
$mother = fetch_one($conn, "SELECT * FROM patient WHERE user_id=? AND patient_type='mother' LIMIT 1", [$userId], "i");

// Get ALL infants for this user
$children = fetch_all(
    $conn,
    "SELECT p.*, i.sex FROM patient p 
     LEFT JOIN infant i ON i.patient_id = p.patient_id 
     WHERE p.patient_type='infant' AND p.user_id=?
     ORDER BY p.birth_date DESC",
    [$userId],
    "i"
);

// Simple detection - if no mother but has infants, it's standalone infant
$isStandaloneInfant = (!$mother && !empty($children));
$motherName = 'Patient';

if ($mother) {
    $motherName = trim(($mother['first_name'] ?? '') . ' ' . ($mother['middle_name'] ?? '') . ' ' . ($mother['last_name'] ?? ''));
} elseif ($isStandaloneInfant && !empty($children[0]['name_of_mother'])) {
    $motherName = $children[0]['name_of_mother'];
} elseif ($isStandaloneInfant && !empty($children)) {
    $motherName = $children[0]['first_name'] . ' ' . $children[0]['last_name'] . ' (Guardian)';
}
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5" />
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
            <a class="btn btn-logout" href="../module/logout.php" onclick="confirmLogout(event); return false;">
                <i class="bi bi-box-arrow-right"></i>
                <span class="d-none d-sm-inline">Logout</span>
            </a>
        </div>
    </nav>

    <main class="container main-content">
       <div class="welcome-header">
            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-3">
                <div>
                    <h1 class="mb-1">Welcome back, <?= h($motherName) ?>!</h1>
                    <?php if ($isStandaloneInfant): ?>
                        <p class="text-muted mb-0">Infant Immunization Tracking</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

       <?php if ($mother || $isStandaloneInfant): ?>    
            <div class="row g-3 g-lg-4">
                <!-- Left: Patient's Profile -->
                <div class="col-12 col-lg-6">
                    <div class="card-modern h-100">
                        <div class="card-header-modern bg-light">
                            <h5 class="m-0 fw-bold text-primary">
                                <i class="bi bi-person-fill"></i>
                                <?= $isStandaloneInfant ? 'Guardian Information' : 'Mother\'s Info' ?>
                            </h5>
                        </div>
                        <div class="card-body-modern">
                            <div class="info-item">
                                <div class="info-label">Full Name</div>
                                <div class="info-value"><?= h($motherName) ?></div>
                            </div>
                            
                            <?php if ($mother && !$isStandaloneInfant): ?>
                            <!-- Only show birthday for actual mothers -->
                            <div class="row g-3">
                                <div class="col-12 col-sm-6">
                                    <div class="info-item">
                                        <div class="info-label">Birthday</div>
                                        <div class="info-value"><?= d(getcol($mother, 'birth_date', '')) ?></div>
                                    </div>
                                </div>
                                <div class="col-12 col-sm-6">
                                    <div class="info-item">
                                        <div class="info-label">Contact</div>
                                        <div class="info-value">
                                            <?php
                                            if ($isStandaloneInfant && !empty($children)) {
                                                echo h(getcol($children[0], 'contact_number', '—'));
                                            } elseif ($mother) {
                                                echo h(getcol($mother, 'contact_number', '—'));
                                            } else {
                                                echo '—';
                                            }
                                            ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php else: ?>
                            <div class="info-item">
                                <div class="info-label">Contact</div>
                                <div class="info-value">
                                    <?php
                                    if ($isStandaloneInfant && !empty($children)) {
                                        echo h(getcol($children[0], 'contact_number', '—'));
                                    } else {
                                        echo '—';
                                    }
                                    ?>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <div class="info-item">
                                <div class="info-label">Address</div>
                                <div class="info-value text-break">
                                    <?php
                                    if ($isStandaloneInfant && !empty($children)) {
                                        echo h(getcol($children[0], 'address', '—'));
                                    } elseif ($mother) {
                                        $addr = getcol($mother, 'address', '');
                                        echo h($addr) ?: '—';
                                    } else {
                                        echo '—';
                                    }
                                    ?>
                                </div>
                            </div>
                            
                             <div class="info-item mb-0">
                                <div class="info-label">Email</div>
                                <div class="info-value text-break">
                                    <?php
                                    if ($isStandaloneInfant && !empty($children)) {
                                        echo h(getcol($children[0], 'email', '—'));
                                    } elseif ($mother) {
                                        echo h(getcol($mother, 'email', '—'));
                                    } else {
                                        echo h($_SESSION['user_email'] ?? '—');
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right: My Children -->
                <div class="col-12 col-lg-6">
                    <div class="card-modern h-100">
                        <div class="card-header-modern bg-light">
                            <h5 class="m-0 fw-bold text-primary">
                                <i class="bi bi-people-fill"></i>
                                My Children
                            </h5>
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
                                        <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap flex-sm-nowrap">
                                            <div class="d-flex align-items-center gap-3 flex-grow-1 min-w-0">
                                                <i class="bi bi-file-person fs-6 text-danger"></i>
                                                <div class="flex-grow-1 min-w-0">
                                                    <div class="child-name text-truncate">
                                                        <?= h($name) ?>
                                                    </div>
                                                </div>
                                            </div>
                                            <button class="btn btn-view view-child flex-shrink-0" data-id="<?= $cid ?>">
                                                <i class="bi bi-eye d-none d-sm-inline me-1"></i>
                                                View
                                            </button>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SEPARATE SECTION FOR CHILD INFO -->
            <div id="child-info-section" class="mt-3 mt-lg-4"></div>

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
                            <div class="card-header-modern bg-light">
                                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                                    <h5 class="mb-0 fw-bold text-primary">
                                        <i class="bi bi-clipboard2-pulse-fill"></i>
                                        <span class="d-inline d-sm-inline">${[c.first_name||'', c.middle_name||'', c.last_name||''].join(' ').replace(/\s+/g,' ').trim()}'s Health Record</span>
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

        function confirmLogout(e) {
            e.preventDefault();
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
                    window.location.href = '../module/logout.php';
                }
            });
        }

        // Add touch-friendly click to child cards
        $(document).on('click', '.child-card', function(e) {
            if (!$(e.target).hasClass('btn-view') && !$(e.target).closest('.btn-view').length) {
                $(this).find('.btn-view').click();
            }
        });
    </script>
</body>

</html>