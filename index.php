<?php
session_start();
require_once __DIR__ . '/assets/includes/conn.php';

$sessionUser = $_SESSION['user'] ?? null;
$cmsAllowedRoles = ['admin', 'docenten', 'assistant'];
$canSeeCms = $sessionUser && in_array($sessionUser['role'] ?? '', $cmsAllowedRoles, true);

function escape(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function excerpt(string $value, int $max = 70): string
{
    $clean = trim(preg_replace('/\s+/', ' ', $value));
    if ($clean === '') return 'text';
    if (strlen($clean) <= $max) return $clean;
    return substr($clean, 0, $max - 3) . '...';
}

$svgCamera = '<svg xmlns="http://www.w3.org/2000/svg" width="52" height="52" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg>';

function renderCard(array $stage, string $svgCamera, string $demoText): void
{
    $name      = trim($stage['bedrijf']      ?? 'NAME');
    $image     = trim($stage['foto']         ?? '');
    $desc      = trim($stage['beschrijving'] ?? '');
    $link      = trim($stage['link']         ?? '');
    $datum     = trim($stage['datum']        ?? '');
    $dateLabel = $datum !== '' ? date('d M Y', strtotime($datum)) : '';
    $displayDesc = excerpt($desc !== '' ? $desc : $demoText, 130);
    $displayName = $name !== '' ? $name : 'NAME';
    ?>
    <article class="stage-card">
        <h2 class="stage-name"><?= escape($displayName) ?></h2>

        <div class="stage-image-wrap">
            <?php if ($image !== ''): ?>
                <img
                    src="<?= escape($image) ?>"
                    alt="<?= escape($displayName) ?>"
                    class="stage-image"
                    onerror="this.style.display='none';this.nextElementSibling.style.display='grid';"
                >
                <div class="stage-image-fallback" style="display:none;"><?= $svgCamera ?></div>
            <?php else: ?>
                <div class="stage-image-fallback"><?= $svgCamera ?></div>
            <?php endif; ?>
        </div>

        <p class="stage-text"><?= escape($displayDesc) ?></p>

        <div class="stage-footer">
            <?php if ($dateLabel !== ''): ?>
                <span class="stage-date"><?= escape($dateLabel) ?></span>
            <?php else: ?>
                <span></span>
            <?php endif; ?>
            <?php if ($link !== ''): ?>
                <a href="<?= escape($link) ?>" class="stage-link-btn" target="_blank" rel="noopener">Bekijk &rarr;</a>
            <?php endif; ?>
        </div>
    </article>
    <?php
}

$stages  = [];
$demoText = 'Demo tekst: je werkt mee aan echte projecten, leert van het team en bouwt stap voor stap aan je portfolio.';

try {
    $dbConnection = new DbhConnection();
    $pdo = $dbConnection->connect();

    if ($pdo) {
        $stmt = $pdo->prepare('SELECT bedrijf, beschrijving, foto, link, datum FROM stages ORDER BY datum DESC');
        $stmt->execute();
        $stages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (Throwable $e) {
    $stages = [];
}

// Stats from real data
$stageCount   = count($stages);
$companyCount = count(array_unique(array_column($stages, 'bedrijf')));
$currentYear  = date('Y');

// Pad with demo card if DB is empty
while (count($stages) < 1) {
    $stages[] = [
        'bedrijf'      => 'NAME',
        'beschrijving' => $demoText,
        'foto'         => '',
        'link'         => '',
        'datum'        => '',
    ];
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Het Bureau Stages</title>
    <link rel="icon" type="image/webp" href="assets/img/logo.webp">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <main class="page">

        <!-- ── Hero board ── -->
        <section class="board">
            <div class="shape-top-left" aria-hidden="true"></div>
            <div class="shape-bottom-right" aria-hidden="true"></div>

            <header class="board-header">
                <img src="assets/img/logo.webp" alt="Het Bureau" class="brand-logo">
                <?php if ($canSeeCms): ?>
                    <div class="nav-actions">
                        <a href="admin.php" class="nav-link">Beheer</a>
                        <a href="logout.php" class="nav-link nav-link--ghost">Uitloggen</a>
                    </div>
                <?php else: ?>
                    <a href="login.php" class="nav-link">Login</a>
                <?php endif; ?>
            </header>

            <div class="hero">
                <span class="hero-eyebrow">Het Bureau — Stageplaatsen</span>
                <h1 class="hero-title">Jouw volgende<br>stap begint hier.</h1>
                <p class="hero-sub">Ontdek stageplaatsen bij toonaangevende bedrijven</p>
            </div>

            <!-- Stats bar -->
            <div class="stats-bar">
                <div class="stats-item">
                    <span class="stats-number"><?= $stageCount ?></span>
                    <span class="stats-label">Stageplaatsen</span>
                </div>
                <div class="stats-divider"></div>
                <div class="stats-item">
                    <span class="stats-number"><?= $companyCount ?></span>
                    <span class="stats-label">Bedrijven</span>
                </div>
                <div class="stats-divider"></div>
                <div class="stats-item">
                    <span class="stats-number"><?= $currentYear ?></span>
                    <span class="stats-label">Schooljaar</span>
                </div>
            </div>

            <!-- Marquee -->
            <div class="marquee-wrap">
                <div class="marquee-track">
                    <?php foreach ($stages as $stage): ?>
                        <?php renderCard($stage, $svgCamera, $demoText); ?>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>


        <!-- ── Footer ── -->
        <footer class="site-footer">
            <div class="site-footer__inner">
                <img src="assets/img/logo.webp" alt="Het Bureau" class="footer-logo">
                <p class="footer-copy">&copy; <?= $currentYear ?> Het Bureau — Stageplaatsen</p>
                <?php if (!$canSeeCms): ?>
                    <a href="login.php" class="footer-link">Inloggen als docent</a>
                <?php else: ?>
                    <a href="admin.php" class="footer-link">Naar beheer</a>
                <?php endif; ?>
            </div>
        </footer>

    </main>
    <script src="assets/js/script.js"></script>
</body>
</html>
