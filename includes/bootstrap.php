<?php
require __DIR__ . '/../config/db.php';

session_set_cookie_params([
    'httponly' => true,
    'samesite' => 'Lax',
    // Render prosleđuje HTTPS preko proksija, pa PHP sam ne vidi https
    'secure'   => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || strtolower($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https',
]);
session_start();

function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

// 1 zadatak, 2 zadatka, 5 zadataka, 21 zadatak, 12 zadataka
function plural(int $n, string $one, string $few, string $many): string
{
    if ($n % 10 === 1 && $n % 100 !== 11) {
        return $one;
    }
    if ($n % 10 >= 2 && $n % 10 <= 4 && ($n % 100 < 12 || $n % 100 > 14)) {
        return $few;
    }
    return $many;
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

function require_post_with_csrf(string $back = 'index.php'): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        exit('Neispravan zahtev.');
    }
    if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
        // najčešće je sesija istekla dok je forma bila otvorena
        flash('Sesija je istekla, pokušaj ponovo.');
        header('Location: ' . $back);
        exit;
    }
}

function require_login(): int
{
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit;
    }
    return (int) $_SESSION['user_id'];
}

function current_role(PDO $pdo): string
{
    $stmt = $pdo->prepare('SELECT role FROM users WHERE id = ?');
    $stmt->execute([$_SESSION['user_id'] ?? 0]);
    return (string) ($stmt->fetchColumn() ?: 'user');
}

const PRIORITIES = ['low' => 'Nizak', 'medium' => 'Srednji', 'high' => 'Visok'];

function valid_priority($value): string
{
    return array_key_exists($value, PRIORITIES) ? $value : 'medium';
}

function valid_date($value): ?string
{
    if (empty($value)) {
        return null;
    }
    $d = DateTime::createFromFormat('Y-m-d', $value);
    return ($d && $d->format('Y-m-d') === $value) ? $value : null;
}

function flash(?string $message = null): ?string
{
    if ($message !== null) {
        $_SESSION['flash'] = $message;
        return null;
    }
    $m = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $m;
}

function add_starter_tasks(PDO $pdo, int $userId): void
{
    $today = new DateTimeImmutable('today');
    $tasks = [
        ['Otvori meni sa tri tačke na ovoj kartici', 'Tu su Izmeni i Obriši.', 'high', $today],
        ['Označi ovaj zadatak kao završen', 'Klikni crno dugme sa kvačicom dole desno.', 'medium', $today->modify('+1 day')],
        ['Dodaj svoj prvi zadatak', 'Dugme „Novi zadatak“ gore desno ili kartica sa plusom.', 'medium', $today->modify('+3 day')],
        ['Probaj pretragu i filtere', null, 'low', null],
    ];
    $stmt = $pdo->prepare("INSERT INTO tasks (user_id, title, description, priority, due_date) VALUES (?, ?, ?, ?, ?)");
    foreach ($tasks as [$title, $description, $priority, $due]) {
        $stmt->execute([$userId, $title, $description, $priority, $due?->format('Y-m-d')]);
    }
}
