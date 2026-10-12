<?php
require __DIR__ . '/includes/bootstrap.php';
require __DIR__ . '/includes/layout.php';

$user_id = require_login();
$isAdmin = current_role($pdo) === 'admin';

$stmt = $pdo->prepare("SELECT * FROM tasks WHERE id = ? AND user_id = ?");
$stmt->execute([(int) ($_GET['id'] ?? 0), $user_id]);
$task = $stmt->fetch();
if (!$task) {
    header('Location: index.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_post_with_csrf('edit.php?id=' . (int) $task['id']);
    $title = trim($_POST['title'] ?? '');
    if ($title === '') {
        $error = 'Naziv ne sme biti prazan.';
    } else {
        $update = $pdo->prepare("UPDATE tasks SET title = ?, description = ?, priority = ?, due_date = ? WHERE id = ? AND user_id = ?");
        $update->execute([
            mb_substr($title, 0, 150),
            trim($_POST['description'] ?? ''),
            valid_priority($_POST['priority'] ?? ''),
            valid_date($_POST['due_date'] ?? null),
            $task['id'],
            $user_id,
        ]);
        flash('Izmene su sačuvane.');
        header('Location: index.php');
        exit;
    }
}

layout_start('Izmena zadatka', 'tasks', $isAdmin);
?>
<header class="topbar">
    <div>
        <a class="back" href="index.php"><?= icon('arrow-left') ?> Nazad</a>
        <h1>Izmena zadatka</h1>
    </div>
</header>
<section class="panel panel--form">
    <?php if ($error): ?><p class="alert"><?= e($error) ?></p><?php endif; ?>
    <form method="POST" class="form">
        <?= csrf_field() ?>
        <label>Naziv<input type="text" name="title" maxlength="150" value="<?= e($task['title']) ?>" required></label>
        <label>Opis<textarea name="description" rows="4"><?= e($task['description']) ?></textarea></label>
        <div class="form__row">
            <label>Prioritet
                <select name="priority">
                    <?php foreach (PRIORITIES as $key => $label): ?>
                        <option value="<?= $key ?>" <?= $task['priority'] === $key ? 'selected' : '' ?>><?= $label ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>Rok<input type="date" name="due_date" value="<?= e($task['due_date']) ?>"></label>
        </div>
        <div class="form__actions">
            <a class="btn btn--outline" href="index.php">Otkaži</a>
            <button type="submit" class="btn btn--dark">Sačuvaj izmene</button>
        </div>
    </form>
</section>
<?php layout_end(); ?>
