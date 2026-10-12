<?php
require __DIR__ . '/includes/bootstrap.php';
require __DIR__ . '/includes/layout.php';

$user_id = require_login();
$isAdmin = current_role($pdo) === 'admin';

$filter = $_GET['filter'] ?? 'all';
if (!in_array($filter, ['all', 'pending', 'urgent', 'completed'], true)) {
    $filter = 'all';
}
$search = trim($_GET['search'] ?? '');

$sql = "SELECT * FROM tasks WHERE user_id = ?";
$params = [$user_id];
if ($filter === 'pending' || $filter === 'completed') {
    $sql .= " AND status = ?";
    $params[] = $filter;
} elseif ($filter === 'urgent') {
    $sql .= " AND status = 'pending' AND priority = 'high'";
}
if ($search !== '') {
    $sql .= " AND (title ILIKE ? OR description ILIKE ?)";
    $like = '%' . addcslashes($search, '%_\\') . '%';
    $params[] = $like;
    $params[] = $like;
}
$sql .= " ORDER BY CASE priority WHEN 'high' THEN 1 WHEN 'medium' THEN 2 ELSE 3 END,
          CASE WHEN due_date IS NULL THEN 1 ELSE 0 END, due_date, created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$tasks = $stmt->fetchAll();

$pending = array_values(array_filter($tasks, fn($t) => $t['status'] !== 'completed'));
$done = array_values(array_filter($tasks, fn($t) => $t['status'] === 'completed'));

// brojevi gore su uvek za sve zadatke, bez filtera
$all = $pdo->prepare("SELECT status, priority, due_date FROM tasks WHERE user_id = ?");
$all->execute([$user_id]);
$allTasks = $all->fetchAll();

$today = new DateTimeImmutable('today');
$total = count($allTasks);
$completed = count(array_filter($allTasks, fn($t) => $t['status'] === 'completed'));
$open = $total - $completed;
$urgent = count(array_filter($allTasks, fn($t) => $t['status'] !== 'completed' && $t['priority'] === 'high'));
$overdue = count(array_filter($allTasks, fn($t) => $t['status'] !== 'completed' && $t['due_date'] && $t['due_date'] < $today->format('Y-m-d')));
$percent = $total ? (int) round($completed / $total * 100) : 0;

$week = [];
$dayNames = ['N', 'P', 'U', 'S', 'Č', 'P', 'S'];
for ($i = 0; $i < 7; $i++) {
    $d = $today->modify("+$i day");
    $key = $d->format('Y-m-d');
    $week[] = [
        'label' => $dayNames[(int) $d->format('w')],
        'date'  => $d->format('d.m.'),
        'count' => count(array_filter($allTasks, fn($t) => $t['status'] !== 'completed' && $t['due_date'] === $key)),
        'today' => $i === 0,
    ];
}
$weekMax = max(1, max(array_column($week, 'count')));
$weekTotal = array_sum(array_column($week, 'count'));

$priorityIcon = ['high' => 'fire', 'medium' => 'bolt', 'low' => 'cup'];

function due_label(?string $due, DateTimeImmutable $today): string
{
    if (!$due) {
        return 'Bez roka';
    }
    $d = new DateTimeImmutable($due);
    $diff = (int) $today->diff($d)->format('%r%a');
    return match (true) {
        $diff < 0   => 'Kasni ' . abs($diff) . ' ' . plural(abs($diff), 'dan', 'dana', 'dana'),
        $diff === 0 => 'Danas',
        $diff === 1 => 'Sutra',
        default     => $d->format('d.m.Y.'),
    };
}

$message = flash();
layout_start('Pregled', 'tasks', $isAdmin);
?>
<header class="topbar">
    <div>
        <h1>Tvoji zadaci</h1>
        <p class="muted"><?= e($today->format('d.m.Y.')) ?> · <?= $open ?> <?= plural($open, 'otvoren zadatak', 'otvorena zadatka', 'otvorenih zadataka') ?></p>
    </div>
    <div class="topbar__actions">
        <form class="search" method="GET" role="search">
            <?= icon('search') ?>
            <input type="search" name="search" placeholder="Pretraži zadatke..." value="<?= e($search) ?>" aria-label="Pretraga">
            <?php if ($filter !== 'all'): ?><input type="hidden" name="filter" value="<?= e($filter) ?>"><?php endif; ?>
        </form>
        <button type="button" class="btn btn--dark" onclick="document.getElementById('new-task').showModal()"><?= icon('plus') ?> Novi zadatak</button>
        <span class="avatar" title="<?= e($_SESSION['username'] ?? '') ?>"><?= e(mb_strtoupper(mb_substr($_SESSION['username'] ?? '?', 0, 1))) ?></span>
    </div>
</header>

<?php if ($message): ?><p class="toast" role="status"><?= e($message) ?></p><?php endif; ?>

<section class="row3">
    <article class="panel panel--dark">
        <div class="panel__head"><h2>Pregled</h2><?= icon('menu') ?></div>
        <div class="big-stats">
            <div><strong><?= $total ?></strong><span><?= plural($total, 'zadatak', 'zadatka', 'zadataka') ?><br>ukupno</span></div>
            <div class="sep"></div>
            <div><strong><?= $overdue ?></strong><span><?= plural($overdue, 'zadatak<br>kasni', 'zadatka<br>kasne', 'zadataka<br>kasni') ?></span></div>
        </div>
        <div class="bar"><span style="width: <?= $percent ?>%"></span></div>
        <div class="mini-tiles">
            <a href="?filter=pending#lista" class="mini-tile"><?= icon('circle') ?><strong><?= $open ?></strong><span>U toku</span></a>
            <a href="?filter=urgent#lista" class="mini-tile"><?= icon('fire') ?><strong><?= $urgent ?></strong><span>Hitno</span></a>
            <a href="?filter=completed#lista" class="mini-tile mini-tile--white"><?= icon('check-circle') ?><strong><?= $completed ?></strong><span>Završeno</span></a>
        </div>
    </article>

    <article class="panel">
        <div class="panel__head"><h2>Narednih 7 dana</h2><?= icon('calendar') ?></div>
        <p class="muted small"><?= $weekTotal ?> <?= plural($weekTotal, 'rok', 'roka', 'rokova') ?> do <?= e($today->modify('+6 day')->format('d.m.')) ?></p>
        <div class="week" role="img" aria-label="Broj rokova po danu">
            <?php foreach ($week as $d): ?>
                <div class="week__col" title="<?= e($d['date']) ?>: <?= $d['count'] ?>">
                    <span class="week__n"><?= $d['count'] ?: '' ?></span>
                    <span class="week__bar <?= $d['count'] ? '' : 'is-empty' ?>" style="height: <?= $d['count'] ? max(12, $d['count'] / $weekMax * 100) : 6 ?>%"></span>
                    <span class="week__day <?= $d['today'] ? 'is-today' : '' ?>"><?= e($d['label']) ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </article>

    <article class="panel">
        <div class="panel__head"><h2>Napredak</h2><?= icon('chart') ?></div>
        <p class="muted small"><strong class="ink"><?= $completed ?> od <?= $total ?></strong> <?= plural($total, 'zadatka', 'zadatka', 'zadataka') ?> završeno</p>
        <div class="progress">
            <ul class="legend legend--panel">
                <li><span class="dot dot--ink"></span>Završeno</li>
                <li><span class="dot dot--soft"></span>U toku</li>
            </ul>
            <svg class="ring" viewBox="0 0 120 120" aria-label="<?= $percent ?>% završeno" role="img">
                <circle cx="60" cy="60" r="50" class="ring__track"/>
                <?php if ($percent > 0): ?><circle cx="60" cy="60" r="50" class="ring__value" pathLength="100" stroke-dasharray="<?= $percent ?> 100"/><?php endif; ?>
                <circle cx="60" cy="60" r="38" class="ring__inner"/>
                <text x="60" y="60" class="ring__label"><?= $percent ?>%</text>
                <text x="60" y="76" class="ring__sub">završeno</text>
            </svg>
        </div>
        <a class="btn btn--outline btn--block" href="?filter=completed#lista">Vidi završene <?= icon('arrow-right') ?></a>
    </article>
</section>

<section id="lista">
    <div class="section-head">
        <h2><?= ['completed' => 'Završeni', 'urgent' => 'Hitno'][$filter] ?? 'U toku' ?> <span class="muted">(<?= $filter === 'completed' ? count($done) : count($pending) ?>)</span></h2>
        <nav class="chips" aria-label="Filter">
            <?php foreach (['all' => 'Svi', 'pending' => 'U toku', 'urgent' => 'Hitno', 'completed' => 'Završeni'] as $key => $label): ?>
                <a href="?filter=<?= $key ?><?= $search !== '' ? '&search=' . urlencode($search) : '' ?>#lista" class="<?= $filter === $key ? 'is-active' : '' ?>"><?= $label ?></a>
            <?php endforeach; ?>
            <?php if ($search !== ''): ?><a href="index.php#lista" class="chip-clear"><?= icon('clear') ?> „<?= e($search) ?>“</a><?php endif; ?>
        </nav>
    </div>

    <?php if ($filter !== 'completed'): ?>
    <div class="tasks">
        <?php foreach ($pending as $t): ?>
            <article class="task">
                <div class="task__top">
                    <span class="task__icon task__icon--<?= e($t['priority']) ?>" title="Prioritet: <?= e(PRIORITIES[$t['priority']] ?? '') ?>"><?= icon($priorityIcon[$t['priority']] ?? 'circle') ?></span>
                    <details class="menu-pop">
                        <summary aria-label="Opcije"><?= icon('dots') ?></summary>
                        <div class="menu-pop__list">
                            <a href="edit.php?id=<?= (int) $t['id'] ?>">Izmeni <?= icon('edit') ?></a>
                            <form method="POST" action="process.php" onsubmit="return confirm('Obrisati zadatak?')">
                                <?= csrf_field() ?><input type="hidden" name="delete" value="<?= (int) $t['id'] ?>">
                                <button type="submit">Obriši <?= icon('trash') ?></button>
                            </form>
                        </div>
                    </details>
                </div>
                <h3><?= e($t['title']) ?></h3>
                <?php if (!empty($t['description'])): ?><p class="task__desc"><?= e($t['description']) ?></p><?php endif; ?>
                <div class="task__foot">
                    <span class="<?= ($t['due_date'] && $t['due_date'] < $today->format('Y-m-d')) ? 'late' : 'muted' ?>"><?= e(due_label($t['due_date'], $today)) ?></span>
                    <form method="POST" action="process.php">
                        <?= csrf_field() ?><input type="hidden" name="toggle" value="<?= (int) $t['id'] ?>">
                        <button type="submit" class="task__check" title="Označi kao završeno" aria-label="Označi kao završeno"><?= icon('done') ?></button>
                    </form>
                </div>
            </article>
        <?php endforeach; ?>
        <?php if ($filter === 'all' && $search === ''): ?>
            <button type="button" class="task task--add" onclick="document.getElementById('new-task').showModal()"><?= icon('plus') ?> Dodaj zadatak</button>
        <?php elseif (!$pending): ?>
            <p class="empty">Nema zadataka za prikaz.</p>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <?php if ($filter !== 'pending' && $done): ?>
        <?php if ($filter === 'all'): ?><div class="section-head"><h2>Završeni <span class="muted">(<?= count($done) ?>)</span></h2></div><?php endif; ?>
        <div class="tasks tasks--done">
            <?php foreach ($done as $t): ?>
                <article class="task task--dark">
                    <div class="task__top">
                        <h3><?= e($t['title']) ?></h3>
                        <form method="POST" action="process.php">
                            <?= csrf_field() ?><input type="hidden" name="toggle" value="<?= (int) $t['id'] ?>">
                            <button type="submit" class="ring-btn" title="Vrati u toku" aria-label="Vrati u toku"><?= icon('undo') ?></button>
                        </form>
                    </div>
                    <p class="status"><span class="dot dot--white"></span>Završeno</p>
                    <?php if (!empty($t['description'])): ?><p class="task__desc"><?= e($t['description']) ?></p><?php endif; ?>
                    <form method="POST" action="process.php" class="task__del" onsubmit="return confirm('Obrisati zadatak?')">
                        <?= csrf_field() ?><input type="hidden" name="delete" value="<?= (int) $t['id'] ?>">
                        <button type="submit"><?= icon('trash') ?> Obriši</button>
                    </form>
                </article>
            <?php endforeach; ?>
        </div>
    <?php elseif ($filter === 'completed'): ?>
        <p class="empty">Još nema završenih zadataka.</p>
    <?php endif; ?>
</section>

<dialog id="new-task" class="modal">
    <form method="POST" action="process.php" class="form">
        <?= csrf_field() ?>
        <input type="hidden" name="add_task" value="1">
        <div class="modal__head"><h2>Novi zadatak</h2><button type="button" class="icon-btn" onclick="this.closest('dialog').close()" aria-label="Zatvori"><?= icon('close') ?></button></div>
        <label>Naziv<input type="text" name="title" maxlength="150" placeholder="npr. Redizajn sajta" required autofocus></label>
        <label>Opis<textarea name="description" rows="3" placeholder="Detalji..."></textarea></label>
        <div class="form__row">
            <label>Prioritet
                <select name="priority">
                    <?php foreach (PRIORITIES as $key => $label): ?>
                        <option value="<?= $key ?>" <?= $key === 'medium' ? 'selected' : '' ?>><?= $label ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>Rok<input type="date" name="due_date"></label>
        </div>
        <button type="submit" class="btn btn--dark btn--block">Sačuvaj zadatak</button>
    </form>
</dialog>
<?php layout_end(); ?>
