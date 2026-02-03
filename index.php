<?php
require_once __DIR__ . '/assets/includes/conn.php';

$dbConnection = new DbhConnection();
$pdo = $dbConnection->connect();

$stmt = $pdo->prepare('SELECT * FROM stages');
$stmt->execute();
$stages = $stmt->fetchAll(PDO::FETCH_ASSOC);

include __DIR__ . '/assets/includes/header.php';
?>
<div>
    <h2>Stages</h2>
    <ul>
        <?php foreach ($stages as $stage): ?>
            <li>
                <h3><?= htmlspecialchars($stage['bedrijf'], ENT_QUOTES, 'UTF-8'); ?></h3>
                <p><?= htmlspecialchars($stage['beschrijving'], ENT_QUOTES, 'UTF-8'); ?></p>
                <p>link <?= htmlspecialchars($stage['link'], ENT_QUOTES, 'UTF-8'); ?></p>
                <p>datum <?= htmlspecialchars($stage['datum'], ENT_QUOTES, 'UTF-8'); ?></p>
                <p>foto <?= htmlspecialchars($stage['foto'], ENT_QUOTES, 'UTF-8'); ?></p>

            </li>
        <?php endforeach; ?>
    </ul>   
</div>
<?php
include __DIR__ . '/assets/includes/footer.php';
?>