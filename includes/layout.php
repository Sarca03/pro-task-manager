<?php
// SVG ikonice, 24x24
const ICONS = [
    'check'        => '<path d="M5 12.5l4.5 4.5L19 7.5"/>',
    'done'         => '<path d="M4.5 12.5l5 5L19.5 7"/>',
    'check-circle' => '<circle cx="12" cy="12" r="9"/><path d="M8 12.3l2.8 2.8L16.5 9.5"/>',
    'check-all'    => '<path d="M2.5 12.5l4.5 4.5L16 8"/><path d="M11.5 16l1 1L21.5 8"/>',
    'dashboard'    => '<rect x="3.5" y="3.5" width="7" height="17" rx="2"/><rect x="13.5" y="3.5" width="7" height="7" rx="2"/><rect x="13.5" y="13.5" width="7" height="7" rx="2"/>',
    'admin'        => '<path d="M12 3l7.5 3v5.5c0 4.5-3.2 8-7.5 9.5-4.3-1.5-7.5-5-7.5-9.5V6z"/><rect x="9.5" y="11" width="5" height="4" rx="1"/><path d="M10.5 11V9.8a1.5 1.5 0 0 1 3 0V11"/>',
    'logout'       => '<path d="M14 4.5H6.5a2 2 0 0 0-2 2v11a2 2 0 0 0 2 2H14"/><path d="M10 12h10m-3.5-3.5L20 12l-3.5 3.5"/>',
    'search'       => '<circle cx="11" cy="11" r="6.5"/><path d="M16 16l4.5 4.5"/>',
    'plus'         => '<path d="M12 4.5v15M4.5 12h15"/>',
    'clear'        => '<path d="M7 7l10 10M17 7L7 17"/>',
    'close'        => '<path d="M5.5 5.5l13 13M18.5 5.5l-13 13"/>',
    'dots'         => '<circle cx="5.5" cy="12" r="1.4" fill="currentColor"/><circle cx="12" cy="12" r="1.4" fill="currentColor"/><circle cx="18.5" cy="12" r="1.4" fill="currentColor"/>',
    'menu'         => '<circle cx="12" cy="5.5" r="1.4" fill="currentColor"/><circle cx="12" cy="12" r="1.4" fill="currentColor"/><circle cx="12" cy="18.5" r="1.4" fill="currentColor"/>',
    'circle'       => '<circle cx="12" cy="12" r="8" stroke-dasharray="3 2.6"/>',
    'fire'         => '<path d="M12 21c-3.9 0-6.5-2.6-6.5-6.2 0-3.4 2.6-5.2 3.6-8.3 2 1.4 2.7 3 2.9 4.6 1-.7 1.6-1.9 1.7-3.3 2.5 2 4.8 4.3 4.8 7.2 0 3.5-2.6 6-6.5 6z"/>',
    'bolt'         => '<path d="M13 3L5 13.5h6L10 21l8-10.5h-6z"/>',
    'cup'          => '<path d="M5 10h11v5a5 5 0 0 1-5 5h-1a5 5 0 0 1-5-5z"/><path d="M16 11.5h1.5a2.5 2.5 0 0 1 0 5H16"/><path d="M9 3.5c-.8 1 .8 2-.1 3M12.5 3.5c-.8 1 .8 2-.1 3"/>',
    'calendar'     => '<rect x="3.5" y="5" width="17" height="15.5" rx="3"/><path d="M3.5 10h17M8 3v4M16 3v4"/>',
    'chart'        => '<path d="M4 4v16h16"/><path d="M7.5 15l4-4.5 3 2.5 5-6"/>',
    'arrow-right'  => '<path d="M4.5 12h15m-5-5l5 5-5 5"/>',
    'arrow-left'   => '<path d="M19.5 12h-15m5-5l-5 5 5 5"/>',
    'undo'         => '<path d="M5 9.5A7.5 7.5 0 1 1 4.6 14"/><path d="M4.5 4.5v5h5"/>',
    'edit'         => '<path d="M15.5 4.5l4 4L9 19l-5 1 1-5z"/>',
    'trash'        => '<path d="M4.5 7h15M9.5 7V4.5h5V7M6.5 7l1 12.5h9l1-12.5M10 11v5.5M14 11v5.5"/>',
    'users'        => '<circle cx="9" cy="8.5" r="3.5"/><path d="M2.5 20c.6-3.6 3.2-5.5 6.5-5.5s5.9 1.9 6.5 5.5"/><path d="M15.5 5.2a3.5 3.5 0 0 1 0 6.6M17.5 14.8c2 .6 3.4 2.4 3.9 5.2"/>',
    'tasks'        => '<rect x="5" y="3.5" width="14" height="17" rx="2.5"/><path d="M9 12.3l2.2 2.2L15.5 10"/>',
];

function icon(string $name): string
{
    $paths = ICONS[$name] ?? ICONS['circle'];
    return '<svg class="ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $paths . '</svg>';
}

function head(string $title): void
{
    ?><!DOCTYPE html>
<html lang="sr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Jednostavan menadžer zadataka, PHP i PostgreSQL.">
    <title><?= e($title) ?> | ProTask</title>
    <link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'%3E%3Crect width='32' height='32' rx='9' fill='%23111'/%3E%3Cpath d='M9 16.5l4.5 4.5L23 11' fill='none' stroke='white' stroke-width='3' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/style.css">
</head>
<?php
}

function layout_start(string $title, string $active, bool $isAdmin): void
{
    head($title);
    $links = [
        'tasks' => ['index.php', 'dashboard', 'Pregled'],
    ];
    if ($isAdmin) {
        $links['admin'] = ['admin.php', 'admin', 'Admin panel'];
    }
    ?>
<body>
<div class="shell">
    <aside class="sidebar">
        <a class="brand" href="index.php">
            <span class="brand__mark"><?= icon('check') ?></span>
            <span>ProTask</span>
        </a>
        <nav class="menu" aria-label="Glavni meni">
            <?php foreach ($links as $key => [$href, $ic, $label]): ?>
                <a href="<?= e($href) ?>" class="<?= $key === $active ? 'is-active' : '' ?>"><?= icon($ic) ?><span><?= e($label) ?></span></a>
            <?php endforeach; ?>
        </nav>
        <p class="menu__label">Prioritet</p>
        <ul class="legend">
            <li><span class="dot dot--high"></span>Visok</li>
            <li><span class="dot dot--medium"></span>Srednji</li>
            <li><span class="dot dot--low"></span>Nizak</li>
        </ul>
        <form class="logout" method="POST" action="logout.php">
            <?= csrf_field() ?>
            <button type="submit"><?= icon('logout') ?><span>Odjavi se</span></button>
        </form>
    </aside>
    <main class="main">
<?php
}

function layout_end(): void
{
    ?>
    </main>
</div>
</body>
</html>
<?php
}
