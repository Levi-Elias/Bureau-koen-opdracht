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

    if ($clean === '') {
        return 'text';
    }

    if (strlen($clean) <= $max) {
        return $clean;
    }

    return substr($clean, 0, $max - 3) . '...';
}

$stages = [];
$demoText = 'Demo tekst: je werkt mee aan echte projecten, leert van het team en bouwt stap voor stap aan je portfolio.';

try {
    $dbConnection = new DbhConnection();
    $pdo = $dbConnection->connect();

    if ($pdo) {
        $stmt = $pdo->prepare('SELECT bedrijf, beschrijving, foto FROM stages ORDER BY datum DESC');
        $stmt->execute();
        $stages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (Throwable $e) {
    $stages = [];
}

while (count($stages) < 1) {
    $stages[] = [
        'bedrijf' => 'NAME',
        'beschrijving' => $demoText,
        'foto' => '',
    ];
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Het Bureau Stages</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <main class="page">
        <section class="board">
            <div class="shape-top-left" aria-hidden="true"></div>
            <div class="shape-bottom-right" aria-hidden="true"></div>

            <header class="board-header">
                <img src="assets/img/logo.webp" alt="Het Bureau" class="brand-logo">
                <?php if ($canSeeCms): ?>
            
                <?php else: ?>
                    <a href="login.php" class="nav-link">login</a>
                <?php endif; ?>
            </header>

            <div class="marquee-wrap">
                <div class="marquee-track">
                    <?php foreach ($stages as $stage): ?>
                        <?php
                        $name = trim($stage['bedrijf'] ?? 'NAME');
                        $image = trim($stage['foto'] ?? '');
                        ?>
                        <article class="stage-card">
                            <h2 class="stage-name"><?= escape($name !== '' ? $name : 'NAME'); ?></h2>

                            <div class="stage-image-wrap">
                                <?php if ($image !== ''): ?>
                                    <img
                                        src="<?= escape($image); ?>"
                                        alt="<?= escape($name !== '' ? $name : 'Stage afbeelding'); ?>"
                                        class="stage-image"
                                        onerror="this.style.display='none'; this.nextElementSibling.style.display='grid';"
                                    >
                                    <div class="stage-image-fallback" style="display: none;">img</div>
                                <?php else: ?>
                                    <div class="stage-image-fallback">img</div>
                                <?php endif; ?>
                            </div>

                            <p class="stage-text"><?= escape(excerpt($demoText, 115)); ?></p>
                        </article>
                    <?php endforeach; ?>
                </div><!-- /.marquee-track -->
            </div><!-- /.marquee-wrap -->
        </section>
    </main>
    <script src="assets/js/script.js"></script>
</body>
</html>
