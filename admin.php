<?php
require __DIR__ . '/includes/bootstrap.php';
require __DIR__ . '/includes/layout.php';

require_login();
if (current_role($pdo) !== 'admin') {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_post_with_csrf('admin.php');
    $id = (int) ($_POST['delete_user'] ?? 0);
    if ($id && $id !== (int) $_SESSION['user_id']) {
        // zadaci se brišu sami (ON DELETE CASCADE)
        $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$id]);
        flash('Korisnik je obrisan.');
    }
    header('Location: admin.php');
    exit;
}

$users = $pdo->query("SELECT u.id, u.username, u.role, u.created_at,
        (SELECT COUNT(*) FROM tasks t WHERE t.user_id = u.id) AS task_count,
        (SELECT COUNT(*) FROM tasks t WHERE t.user_id = u.id AND t.status = 'completed') AS done_count
    FROM users u ORDER BY u.created_at DESC")->fetchAll();
$totalTasks = (int) $pdo->query("SELECT COUNT(*) FROM tasks")->fetchColumn();
$totalDone = (int) $pdo->query("SELECT COUNT(*) FROM tasks WHERE status = 'completed'")->fetchColumn();

$message = flash();
layout_start('Admin panel', 'admin', true);
?>
<header class="topbar">
    <div>
        <h1>Admin panel</h1>
        <p class="muted">Korisnici i zadaci u celom sistemu</p>
    </div>
</header>

<?php if ($message): ?><p class="toast" role="status"><?= e($message) ?></p><?php endif; ?>

<section class="row3">
    <article class="panel panel--dark stat"><?= icon('users') ?><strong><?= count($users) ?></strong><span><?= plural(count($users), 'korisnik', 'korisnika', 'korisnika') ?></span></article>
    <article class="panel stat"><?= icon('tasks') ?><strong><?= $totalTasks ?></strong><span><?= plural($totalTasks, 'zadatak', 'zadatka', 'zadataka') ?> u sistemu</span></article>
    <article class="panel stat"><?= icon('check-all') ?><strong><?= $totalDone ?></strong><span>završeno</span></article>
</section>

<section class="panel panel--table">
    <div class="panel__head"><h2>Korisnici</h2></div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Korisnik</th><th>Uloga</th><th>Zadaci</th><th>Registrovan</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($users as $u): ?>
                <tr>
                    <td><span class="avatar avatar--sm"><?= e(mb_strtoupper(mb_substr($u['username'], 0, 1))) ?></span> <?= e($u['username']) ?></td>
                    <td><span class="pill <?= $u['role'] === 'admin' ? 'pill--dark' : '' ?>"><?= $u['role'] === 'admin' ? 'Admin' : 'Korisnik' ?></span></td>
                    <td class="num"><?= (int) $u['done_count'] ?> / <?= (int) $u['task_count'] ?></td>
                    <td class="muted"><?= e(date('d.m.Y.', strtotime($u['created_at']))) ?></td>
                    <td class="right">
                        <?php if ((int) $u['id'] !== (int) $_SESSION['user_id']): ?>
                            <form method="POST" onsubmit="return confirm('Obrisati korisnika i sve njegove zadatke?')">
                                <?= csrf_field() ?><input type="hidden" name="delete_user" value="<?= (int) $u['id'] ?>">
                                <button type="submit" class="icon-btn" title="Obriši korisnika" aria-label="Obriši korisnika"><?= icon('trash') ?></button>
                            </form>
                        <?php else: ?>
                            <span class="muted small">Ti</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
<?php layout_end(); ?>
