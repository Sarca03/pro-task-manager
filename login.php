<?php
require __DIR__ . '/includes/bootstrap.php';
require __DIR__ . '/includes/layout.php';

if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$error = flash() ?? '';
$username = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_post_with_csrf('login.php');
    $username = trim($_POST['username'] ?? '');

    if (($_SESSION['login_blocked'] ?? 0) > time()) {
        $error = 'Previše pokušaja, sačekaj minut.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($_POST['password'] ?? '', $user['password'])) {
            session_regenerate_id(true);
            unset($_SESSION['login_fails'], $_SESSION['login_blocked']);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            header('Location: index.php');
            exit;
        }

        sleep(1);
        $_SESSION['login_fails'] = ($_SESSION['login_fails'] ?? 0) + 1;
        if ($_SESSION['login_fails'] >= 5) {
            $_SESSION['login_fails'] = 0;
            $_SESSION['login_blocked'] = time() + 60;
        }
        $error = 'Pogrešno korisničko ime ili lozinka.';
    }
}

head('Prijava');
?>
<body class="auth">
    <main class="auth__card">
        <a class="brand" href="index.php"><span class="brand__mark"><?= icon('check') ?></span><span>ProTask</span></a>
        <h1>Prijava</h1>
        <p class="muted">Prijavi se da vidiš svoje zadatke.</p>
        <?php if (isset($_GET['registered'])): ?><p class="toast">Nalog je napravljen. Prijavi se.</p><?php endif; ?>
        <?php if ($error): ?><p class="alert"><?= e($error) ?></p><?php endif; ?>
        <form method="POST" class="form">
            <?= csrf_field() ?>
            <label>Korisničko ime<input type="text" name="username" value="<?= e($username) ?>" autocomplete="username" required autofocus></label>
            <label>Lozinka<input type="password" name="password" autocomplete="current-password" required></label>
            <button type="submit" class="btn btn--dark btn--block">Prijavi se</button>
        </form>
        <p class="auth__foot">Nemaš nalog? <a href="register.php">Registruj se</a></p>
    </main>
</body>
</html>
