<?php
require __DIR__ . '/includes/bootstrap.php';
require __DIR__ . '/includes/layout.php';

$error = flash() ?? '';
$username = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_post_with_csrf('register.php');
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (mb_strlen($username) < 3 || mb_strlen($username) > 50) {
        $error = 'Korisničko ime mora imati od 3 do 50 znakova.';
    } elseif (strlen($password) < 8) {
        $error = 'Lozinka mora imati bar 8 znakova.';
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            $error = 'Korisničko ime je zauzeto.';
        } else {
            // admin može da bude samo ADMIN_USERNAME, i to dok admin ne postoji
            $adminName = getenv('ADMIN_USERNAME');
            $hasAdmin = (bool) $pdo->query("SELECT 1 FROM users WHERE role = 'admin' LIMIT 1")->fetchColumn();
            $role = ($adminName && $username === $adminName && !$hasAdmin) ? 'admin' : 'user';

            try {
                $pdo->beginTransaction();
                $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?) RETURNING id");
                $stmt->execute([$username, password_hash($password, PASSWORD_DEFAULT), $role]);
                add_starter_tasks($pdo, (int) $stmt->fetchColumn());
                $pdo->commit();
                header('Location: login.php?registered=1');
                exit;
            } catch (PDOException $ex) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                if ($ex->getCode() !== '23505') {
                    throw $ex;
                }
                $error = 'Korisničko ime je zauzeto.';
            }
        }
    }
}

head('Registracija');
?>
<body class="auth">
    <main class="auth__card">
        <a class="brand" href="index.php"><span class="brand__mark"><?= icon('check') ?></span><span>ProTask</span></a>
        <h1>Registracija</h1>
        <p class="muted">Napravi nalog. Svako vidi samo svoje zadatke.</p>
        <?php if ($error): ?><p class="alert"><?= e($error) ?></p><?php endif; ?>
        <form method="POST" class="form">
            <?= csrf_field() ?>
            <label>Korisničko ime<input type="text" name="username" value="<?= e($username) ?>" minlength="3" maxlength="50" autocomplete="username" required autofocus></label>
            <label>Lozinka<input type="password" name="password" minlength="8" autocomplete="new-password" required></label>
            <button type="submit" class="btn btn--dark btn--block">Registruj se</button>
        </form>
        <p class="auth__foot">Već imaš nalog? <a href="login.php">Prijavi se</a></p>
    </main>
</body>
</html>
