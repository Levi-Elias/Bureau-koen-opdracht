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
require_once __DIR__ . '/assets/includes/header.php';
?>

    <main>
        <section class="auth">
            <div class="warp">
                <div class="auth-card">
                    <h2>Log in</h2>
                    <?php if ($loginError): ?>
                        <p class="auth-message error"><?= escape($loginError); ?></p>
                    <?php endif; ?>
                    <form method="post" class="auth-form">
                        <label for="username">Gebruikersnaam</label>
                        <input type="text" id="username" name="username" required><br>

                        <label for="password">Wachtwoord</label>
                        <input type="password" id="password" name="password" required><br>

                        <button type="submit" class="btn">Inloggen</button>
                    </form>
                    <!-- <p class="auth-status">Nog geen account? <a href="signup.php">Maak er één aan</a>.</p> -->
                    <p class="auth-status"><a href="index.php">Terug naar startpagina</a></p>
                </div>
            </div>
        </section>
    </main>
</body>

</html>