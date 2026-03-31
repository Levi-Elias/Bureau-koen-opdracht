<?php
session_start();
require_once __DIR__ . '/assets/includes/conn.php';

$dbConnection = new DbhConnection();
$pdo = $dbConnection->connect();

$loginError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['username'], $_POST['password'])) {
    if ($pdo) {
        $stmt = $pdo->prepare('SELECT ID, Username, Password, role FROM users WHERE Username = :username LIMIT 1');
        $stmt->execute(['username' => $_POST['username']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        $passwordValid = $user && (password_verify($_POST['password'], $user['Password']) || hash_equals($user['Password'], $_POST['password']));

        if ($passwordValid) {
            $_SESSION['user'] = [
                'id' => $user['ID'],
                'username' => $user['Username'],
                'role' => $user['role'],
            ];
            header('Location: index.php');
            exit;
        }
        $loginError = 'Onjuiste gebruikersnaam of wachtwoord.';
    } else {
        $loginError = 'Geen databaseverbinding beschikbaar.';
    }
}

function escape(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="nl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/webp" href="assets/img/logo.webp">
    <link rel="stylesheet" href="assets/css/style.css?v=2">
    <link href="https://fonts.googleapis.com/css2?family=Chivo:wght@400;700;900&display=swap" rel="stylesheet">
    <title>Inloggen — Het Bureau</title>
</head>

<body>
    <div class="login-page">
        <div class="shape-top-left"></div>
        <div class="shape-bottom-right"></div>

        <div class="login-wrap">
            <img src="assets/img/logo.webp" alt="Het Bureau" class="login-logo">

            <div class="login-card">
                <div class="login-card-header">Inloggen</div>

                <div class="login-card-body">
                    <?php if ($loginError): ?>
                    <div class="login-error"><?= escape($loginError); ?></div>
                    <?php endif; ?>

                    <form method="post">
                        <div class="login-field">
                            <label for="username">Gebruikersnaam</label>
                            <input type="text" id="username" name="username" autocomplete="username" required>
                        </div>

                        <div class="login-field" style="margin-top:14px;">
                            <label for="password">Wachtwoord</label>
                            <input type="password" id="password" name="password" autocomplete="current-password"
                                required>
                        </div>

                        <button type="submit" class="login-btn">Inloggen</button>
                    </form>
                </div>

                <div class="login-card-footer">
                    <p><a href="index.php">Terug naar overzicht</a></p>
                </div>
            </div>
        </div>
    </div>
</body>

</html>