<?php
session_start();
require_once __DIR__ . '/assets/includes/conn.php';

$dbConnection = new DbhConnection();
$pdo = $dbConnection->connect();

$signupError = '';
$signupSuccess = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = trim($_POST['username']);
    $email    = trim($_POST['email']);
    $password = $_POST['password'];

    if ($username === '' || $password === '' || $email === '') {
        $signupError = 'Alle velden zijn verplicht.';
    } else {

        // ⚠ check of email of username al bestaat
        $check = $pdo->prepare('SELECT 1 FROM users WHERE Username = :u OR `e-mail` = :e LIMIT 1');
        $check->execute([
            'u' => $username,
            'e' => $email
        ]);

        if ($check->fetch()) {
            $signupError = 'Gebruikersnaam of e-mail bestaat al.';
        } 
        else {
            // wachtwoord hash
            $hashed = password_hash($password, PASSWORD_DEFAULT);

            // nieuwe user opslaan
            $insert = $pdo->prepare('INSERT INTO users (Username, `e-mail`, Password, role) 
                                     VALUES (:u, :e, :p, :r)');
            $insert->execute([
                'u' => $username,
                'e' => $email,
                'p' => $hashed,
                'r' => 'student'
            ]);

            $_SESSION['user'] = [
                'id'       => (int)$pdo->lastInsertId(),
                'username' => $username,
                'email'    => $email,
                'role'     => 'student'
            ];

            header("Location: index.php");
            exit;
        }
    }
}

function escape(string $v): string {
    return htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="nl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/css/style.css">
    <title>Registreren - Het Utrechts Archief</title>
</head>

<body>
    <header class="site_header">
        <div class="warp">
            <div class="branding">
                <div class="logo">
                    <div class="logo-background">
                        <img src="assets/images/logo-web.svg" alt="Logo">
                    </div>
                </div>
                <div class="title">
                    <h1>Het Utrechts Archief</h1>
                </div>
            </div>
        </div>
    </header>

    <main>
        <section class="auth">
            <div class="warp">
                <div class="auth-card">

                    <h2>Account aanmaken</h2>

                    <?php if ($signupError): ?>
                        <p class="auth-message error"><?= escape($signupError) ?></p>
                    <?php endif; ?>

                    <form method="post" class="auth-form">

                        <label>E-mail</label>
                        <input type="email" name="email" required>

                        <label>Gebruikersnaam</label>
                        <input type="text" name="username" required>

                        <label>Wachtwoord</label>
                        <input type="password" name="password" required>

                        <button class="btn" type="submit">Registreren</button>
                    </form>

                    <p class="auth-status">Heb je een account? <a href="login.php">Inloggen</a></p>
                    <p><a href="index.php">🏠 Terug naar start</a></p>

                </div>
            </div>
        </section>
    </main>

</body>
</html>
