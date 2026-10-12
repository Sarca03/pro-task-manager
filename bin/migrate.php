<?php
// pokreće se iz start.sh pri svakom startu
require __DIR__ . '/../config/db.php';

$pdo->exec(file_get_contents(__DIR__ . '/../database.sql'));
echo "Baza je spremna.\n";
