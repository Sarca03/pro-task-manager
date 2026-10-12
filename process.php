<?php
require __DIR__ . '/includes/bootstrap.php';

$user_id = require_login();
require_post_with_csrf();

if (isset($_POST['add_task'])) {
    $title = trim($_POST['title'] ?? '');
    if ($title !== '') {
        $stmt = $pdo->prepare("INSERT INTO tasks (user_id, title, description, priority, due_date) VALUES (?, ?, ?, ?, ?)");
        try {
            $stmt->execute([
                $user_id,
                mb_substr($title, 0, 150),
                trim($_POST['description'] ?? ''),
                valid_priority($_POST['priority'] ?? ''),
                valid_date($_POST['due_date'] ?? null),
            ]);
        } catch (PDOException $ex) {
            // nalog je obrisan dok je korisnik bio prijavljen
            if ($ex->getCode() !== '23503') {
                throw $ex;
            }
            session_unset();
            session_destroy();
            header('Location: login.php');
            exit;
        }
        flash('Zadatak je dodat.');
    }
} elseif (isset($_POST['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM tasks WHERE id = ? AND user_id = ?");
    $stmt->execute([(int) $_POST['delete'], $user_id]);
    flash('Zadatak je obrisan.');
} elseif (isset($_POST['toggle'])) {
    $stmt = $pdo->prepare("UPDATE tasks SET status = CASE status WHEN 'completed' THEN 'pending' ELSE 'completed' END
                           WHERE id = ? AND user_id = ?");
    $stmt->execute([(int) $_POST['toggle'], $user_id]);
}

header('Location: index.php');
exit;
