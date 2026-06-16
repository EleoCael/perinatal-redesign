<?php
if (session_status() === PHP_SESSION_NONE) session_start();

if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'Patient') {
    header('Location: ../login/login.php');
    exit;
}

require_once __DIR__ . '/../module/db.config.php'; 

$uid = (int)$_SESSION['user_id'];
$me   = null;
$infants = [];

// Fetch mother's patient profile linked to this user_id
if ($stmt = $conn->prepare("
    SELECT patient_id, first_name, middle_name, last_name,
           birth_date, contact_number, address
    FROM patient
    WHERE user_id = ?
    LIMIT 1
")) {
    $stmt->bind_param("i", $uid);
    $stmt->execute();
    $res = $stmt->get_result();
    $me  = $res->fetch_assoc();
    $stmt->close();
}

// If profile exists, load infants linked to her patient_id
if ($me && !empty($me['patient_id'])) {
    $pid = (int)$me['patient_id'];
    if ($c = $conn->prepare("
        SELECT first_name, middle_name, last_name, birth_date
        FROM patient
        WHERE patient_type='infant' AND mother_id=?
        ORDER BY birth_date DESC, last_name ASC, first_name ASC
    ")) {
        $c->bind_param("i", $pid);
        $c->execute();
        $r = $c->get_result();
        while ($row = $r->fetch_assoc()) {
            $infants[] = $row;
        }
        $c->close();
    }
}
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Patient Portal</title>
    <link rel="stylesheet" href="/assets/css/index.css">

</head>

<body>
    <header>
        <div class="wrap">
            <div class="row" style="justify-content:space-between;align-items:center;">
                <h1>Perinatal Patient Portal</h1>
                <nav>
                    <a href="./patient_access.php">Home</a>
                    <a href="../logout.php">Logout</a>
                </nav>
            </div>
        </div>
    </header>

    <main class="wrap">
        <div class="card">
            <div class="row" style="justify-content:space-between;align-items:center;">
                <div>
                    <div class="muted">Welcome</div>
                    <div style="font-size:1.25rem;font-weight:700;">
                        <?=
                        htmlspecialchars($_SESSION['first_name'] ?? 'Mother')
                        ?>
                    </div>
                </div>
                <div><span class="pill">Patient</span></div>
            </div>
        </div>

        <?php if ($me): ?>
            <div class="grid">
                <section class="card">
                    <h2 style="margin:0 0 8px 0;font-size:1.1rem;">Your Profile</h2>
                    <div class="row">
                        <div class="label">Name</div>
                        <div class="value">
                            <?= htmlspecialchars(trim(($me['first_name'] ?? '') . ' ' . ($me['middle_name'] ?? '') . ' ' . ($me['last_name'] ?? ''))) ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="label">Birth date</div>
                        <div class="value">
                            <?= htmlspecialchars($me['birth_date'] ?? '—') ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="label">Contact</div>
                        <div class="value">
                            <?= htmlspecialchars($me['contact_number'] ?? '—') ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="label">Address</div>
                        <div class="value">
                            <?= htmlspecialchars($me['address'] ?? '—') ?>
                        </div>
                    </div>
                </section>

                <section class="card">
                    <h2 style="margin:0 0 8px 0;font-size:1.1rem;">Your Babies</h2>
                    <?php if (count($infants)): ?>
                        <ul>
                            <?php foreach ($infants as $b): ?>
                                <li>
                                    <?= htmlspecialchars(trim(($b['first_name'] ?? '') . ' ' . ($b['middle_name'] ?? '') . ' ' . ($b['last_name'] ?? ''))) ?>
                                    — <span class="muted"><?= htmlspecialchars($b['birth_date'] ?? '—') ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <div class="empty">No infant records yet.</div>
                    <?php endif; ?>
                </section>
            </div>
        <?php else: ?>
            <section class="card">
                <h2 style="margin:0 0 8px 0;font-size:1.1rem;">No profile found</h2>
                <p class="muted">We couldn’t find your patient profile. Please contact your midwife to confirm your registration.</p>
            </section>
        <?php endif; ?>
    </main>
</body>

</html>