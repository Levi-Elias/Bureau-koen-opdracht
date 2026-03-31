<?php
session_start();
require_once __DIR__ . '/assets/includes/conn.php';

$dbConnection = new DbhConnection();
$pdo = $dbConnection->connect();

if (!$pdo) {
    die('Geen databaseverbinding beschikbaar.');
}

// =========================
// AUTH CHECK
// =========================
$user = $_SESSION['user'] ?? null;
$role = $user['role'] ?? null;
$cmsAllowedRoles = ['admin', 'docenten', 'assistant'];

if (!$user || !in_array($role, $cmsAllowedRoles, true)) {
    http_response_code(403);
    echo 'Toegang geweigerd. Log in met een account dat toegang heeft tot de CMS.';
    exit;
}

// =========================
// HELPERS
// =========================
function escape(string $v): string
{
    return htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
}

$statusMessage = '';
$statusType = 'success';
$roleOptions = ['student', 'docenten', 'assistant', 'admin'];

// =========================
// FORM HANDLING
// =========================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $action = $_POST['action'] ?? '';

    // =========================
    // ADD USER
    // =========================
    if ($action === 'add_user') {
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $roleValue = $_POST['role'] ?? '';

        if ($username === '' || $email === '' || $password === '' || !in_array($roleValue, $roleOptions, true)) {
            $statusMessage = 'Alle velden zijn verplicht voor nieuwe gebruikers.';
            $statusType = 'error';
        } else {
            $check = $pdo->prepare('SELECT 1 FROM users WHERE Username = :u OR `e-mail` = :e LIMIT 1');
            $check->execute(['u' => $username, 'e' => $email]);

            if ($check->fetch()) {
                $statusMessage = 'Gebruikersnaam of e-mail bestaat al.';
                $statusType = 'error';
            } else {
                $hashed = password_hash($password, PASSWORD_DEFAULT);

                $insert = $pdo->prepare('
                    INSERT INTO users (Username, `e-mail`, Password, role) 
                    VALUES (:u, :e, :p, :r)
                ');
                $insert->execute([
                    'u' => $username,
                    'e' => $email,
                    'p' => $hashed,
                    'r' => $roleValue,
                ]);

                $statusMessage = 'Gebruiker toegevoegd!';
            }
        }
    }

    // =========================
    // UPDATE USER
    // =========================
    if ($action === 'update_user') {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $roleValue = $_POST['role'] ?? '';
        $password = $_POST['password'] ?? '';

        if ($id <= 0 || $username === '' || $email === '' || !in_array($roleValue, $roleOptions, true)) {
            $statusMessage = 'Alle velden behalve wachtwoord zijn verplicht bij het aanpassen van gebruikers.';
            $statusType = 'error';
        } else {
            $duplicate = $pdo->prepare('
                SELECT 1 FROM users 
                WHERE (Username = :u OR `e-mail` = :e) 
                AND ID != :id 
                LIMIT 1
            ');
            $duplicate->execute([
                'u' => $username,
                'e' => $email,
                'id' => $id
            ]);

            if ($duplicate->fetch()) {
                $statusMessage = 'Gebruikersnaam of e-mail is al in gebruik.';
                $statusType = 'error';
            } else {
                if ($password !== '') {
                    $hashed = password_hash($password, PASSWORD_DEFAULT);

                    $stmt = $pdo->prepare('
                        UPDATE users 
                        SET Username = :u, `e-mail` = :e, role = :r, Password = :p 
                        WHERE ID = :id
                    ');
                    $stmt->execute([
                        'u' => $username,
                        'e' => $email,
                        'r' => $roleValue,
                        'p' => $hashed,
                        'id' => $id
                    ]);
                } else {
                    $stmt = $pdo->prepare('
                        UPDATE users 
                        SET Username = :u, `e-mail` = :e, role = :r 
                        WHERE ID = :id
                    ');
                    $stmt->execute([
                        'u' => $username,
                        'e' => $email,
                        'r' => $roleValue,
                        'id' => $id
                    ]);
                }

                $statusMessage = 'Gebruiker bijgewerkt!';
            }
        }
    }

    // =========================
    // DELETE USER
    // =========================
    if ($action === 'delete_user') {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

        if ($id > 0) {
            $stmt = $pdo->prepare('DELETE FROM users WHERE ID = :id');
            $stmt->execute(['id' => $id]);

            $statusMessage = 'Gebruiker verwijderd.';
        }
    }

    // =========================
    // ADD STAGE
    // =========================
    if ($action === 'add_stage') {
        $bedrijf     = trim($_POST['bedrijf'] ?? '');
        $link        = trim($_POST['link'] ?? '');
        $beschrijving = trim($_POST['beschrijving'] ?? '');
        $datum       = trim($_POST['datum'] ?? '');
        $foto        = trim($_POST['foto'] ?? '');

        if ($bedrijf === '' || $beschrijving === '') {
            $statusMessage = 'Bedrijfsnaam en beschrijving zijn verplicht.';
            $statusType = 'error';
        } else {
            $stmt = $pdo->prepare('INSERT INTO stages (bedrijf, link, beschrijving, datum, foto) VALUES (:bedrijf, :link, :beschrijving, :datum, :foto)');
            $stmt->execute([
                'bedrijf'      => $bedrijf,
                'link'         => $link,
                'beschrijving' => $beschrijving,
                'datum'        => $datum ?: null,
                'foto'         => $foto,
            ]);
            $statusMessage = 'Stage toegevoegd!';
        }
    }

    // =========================
    // UPDATE STAGE
    // =========================
    if ($action === 'update_stage') {
        $id          = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $bedrijf     = trim($_POST['bedrijf'] ?? '');
        $link        = trim($_POST['link'] ?? '');
        $beschrijving = trim($_POST['beschrijving'] ?? '');
        $datum       = trim($_POST['datum'] ?? '');
        $foto        = trim($_POST['foto'] ?? '');

        if ($id <= 0 || $bedrijf === '' || $beschrijving === '') {
            $statusMessage = 'Bedrijfsnaam en beschrijving zijn verplicht.';
            $statusType = 'error';
        } else {
            $stmt = $pdo->prepare('UPDATE stages SET bedrijf = :bedrijf, link = :link, beschrijving = :beschrijving, datum = :datum, foto = :foto WHERE id = :id');
            $stmt->execute([
                'bedrijf'      => $bedrijf,
                'link'         => $link,
                'beschrijving' => $beschrijving,
                'datum'        => $datum ?: null,
                'foto'         => $foto,
                'id'           => $id,
            ]);
            $statusMessage = 'Stage bijgewerkt!';
        }
    }

    // =========================
    // DELETE STAGE
    // =========================
    if ($action === 'delete_stage') {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

        if ($id > 0) {
            $stmt = $pdo->prepare('DELETE FROM stages WHERE id = :id');
            $stmt->execute(['id' => $id]);
            $statusMessage = 'Stage verwijderd.';
        }
    }
}

// =========================
// FETCH USERS
// =========================
$users = $pdo->query("SELECT ID,`e-mail` AS email, Username, role FROM users ORDER BY ID ASC")->fetchAll(PDO::FETCH_ASSOC);

// =========================
// FETCH STAGES
// =========================
$stages = $pdo->query('SELECT * FROM stages ORDER BY datum DESC')->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="nl">

<head>
    <meta charset="UTF-8">
    <title>Stages CMS</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>
    <header class="site_header cms-header">
        <div class="warp cms-header__content">
            <div>
                <p class="eyebrow">Beheer</p>
                <h1>Stages CMS</h1>
            </div>
            <div class="cms-nav">
                <a class="btn btn--ghost" href="index.php">← Terug naar site</a>
                <a class="btn btn--outline" href="logout.php">Uitloggen</a>
            </div>
        </div>
    </header>


    <main class="cms">
        <div class="warp">
            <nav class="cms-quicklinks" aria-label="Snel naar sectie">

                <a href="#accounts">Accounts</a>
                <a href="#stages">Stages</a>
            </nav>
            <?php if ($statusMessage): ?>
                <p class="cms-status cms-status--<?= escape($statusType) ?>"><?= escape($statusMessage) ?></p>
            <?php endif; ?>

            <section class="cms-section" id="afbeeldingen">
                <div class="cms-section__header">
                    <div>
                        <p class="eyebrow">Accounts</p>
                        <h2>📂 Accounts</h2>
                        <p>Beheer de gebruikersaccounts in het systeem.</p>
                    </div>
                </div>
                <div class="cms-card">
                    <div class="table">
                        <div class="table__header">
                            <div>ID</div>
                            <div>E-mail</div>
                            <div>Gebruiker</div>
                            <div>Rol</div>
                            <div>Nieuw wachtwoord</div>
                            <div></div>
                        </div>

                        <?php foreach ($users as $u): ?>
                            <form method="post" class="table__row">
                                <input type="hidden" name="action" value="update_user">
                                <input type="hidden" name="id" value="<?= $u['ID'] ?>">

                                <div class="table__cell-id">#<?= $u['ID'] ?></div>
                                <div><input type="email" name="email" value="<?= escape($u['email']) ?>" required></div>
                                <div><input name="username" value="<?= escape($u['Username']) ?>" required></div>
                                <div>
                                    <select name="role" required>
                                        <?php foreach ($roleOptions as $roleOption): ?>
                                            <option value="<?= escape($roleOption) ?>" <?= $u['role'] === $roleOption ? 'selected' : '' ?>><?= escape($roleOption) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div><input type="password" name="password" placeholder="Ongewijzigd laten"></div>

                                <div class="table__actions"><button class="btn">Opslaan</button></div>
                            </form>

                            <form method="post" class="table__row table__row--delete">
                                <input type="hidden" name="action" value="delete_user">
                                <input type="hidden" name="id" value="<?= $u['ID'] ?>">
                                <div class="table__delete-label">Verwijder gebruiker <?= $u['Username'] ?></div>
                                <div class="table__actions"><button class="btn btn--danger" onclick="return confirm('Weet je zeker dat je deze gebruiker wilt verwijderen?')">Verwijderen</button></div>
                            </form>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>
            <section class="cms-section" id="stages">
                <div class="cms-section__header">
                    <div>
                        <p class="eyebrow">Beheer</p>
                        <h2>🏢 Stages</h2>
                        <p>Voeg stageplekken toe, bewerk of verwijder ze. Ze verschijnen op de homepage in de marquee.</p>
                    </div>
                </div>

                <form method="post" class="cms-card cms-card--form">
                    <h3 class="cms-card__title">Nieuwe stage toevoegen</h3>
                    <input type="hidden" name="action" value="add_stage">
                    <div class="form-grid">
                        <label>Bedrijfsnaam <input name="bedrijf" required></label>
                        <label>Website URL <input name="link" type="url" placeholder="https://"></label>
                        <label>Datum <input name="datum" type="date"></label>
                        <label class="form-grid__full foto-field">Foto URL
                            <input name="foto" placeholder="https://..." oninput="previewFoto(this)">
                            <img class="foto-thumb" src="" alt="" hidden>
                        </label>
                        <label class="form-grid__full">Beschrijving <textarea name="beschrijving" rows="3" required></textarea></label>
                    </div>
                    <div class="cms-actions">
                        <button class="btn">Stage toevoegen</button>
                    </div>
                </form>

                <div class="cms-card">
                    <div class="table">
                        <div class="table__header table__header--stages">
                            <div>ID</div>
                            <div>Bedrijf</div>
                            <div>Website URL</div>
                            <div>Datum</div>
                            <div>Beschrijving</div>
                            <div>Foto</div>
                            <div></div>
                        </div>

                        <?php foreach ($stages as $s): ?>
                            <form method="post" class="table__row table__row--stages">
                                <input type="hidden" name="action" value="update_stage">
                                <input type="hidden" name="id" value="<?= (int)$s['id'] ?>">

                                <div class="table__cell-id">#<?= (int)$s['id'] ?></div>
                                <div><input name="bedrijf" value="<?= escape($s['bedrijf']) ?>" required></div>
                                <div><input name="link" type="url" value="<?= escape($s['link'] ?? '') ?>" placeholder="https://"></div>
                                <div><input name="datum" type="date" value="<?= escape($s['datum'] ?? '') ?>"></div>
                                <div><textarea name="beschrijving" rows="2" required><?= escape($s['beschrijving']) ?></textarea></div>
                                <div class="foto-cell">
                                    <div class="foto-thumb-wrap">
                                        <img class="foto-thumb" src="<?= escape($s['foto'] ?? '') ?>" alt="" <?= empty($s['foto']) ? 'style="opacity:0"' : '' ?>>
                                    </div>
                                    <input name="foto" value="<?= escape($s['foto'] ?? '') ?>" placeholder="https://..." oninput="previewFoto(this)">
                                </div>
                                <div class="table__actions"><button class="btn">Opslaan</button></div>
                            </form>

                            <form method="post" class="table__row table__row--delete">
                                <input type="hidden" name="action" value="delete_stage">
                                <input type="hidden" name="id" value="<?= (int)$s['id'] ?>">
                                <div class="table__delete-label">Verwijder stage <?= escape($s['bedrijf']) ?></div>
                                <div class="table__actions"><button class="btn btn--danger" onclick="return confirm('Weet je zeker dat je deze stage wilt verwijderen?')">Verwijderen</button></div>
                            </form>
                        <?php endforeach; ?>

                        <?php if (empty($stages)): ?>
                            <p style="padding:16px;color:#666;">Nog geen stages gevonden.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </section>

        </div>
    </main>
    <script>
        function previewFoto(input) {
            var url = input.value.trim();
            var img = (input.nextElementSibling && input.nextElementSibling.tagName === 'IMG')
                ? input.nextElementSibling
                : null;
            if (!img) {
                var cell = input.closest('.foto-cell');
                if (cell) img = cell.querySelector('.foto-thumb');
            }
            if (!img) return;
            if (url) {
                img.src = url;
                img.style.opacity = '1';
                img.hidden = false;
            } else {
                img.src = '';
                img.style.opacity = '0';
            }
        }
    </script>
</body>

</html>