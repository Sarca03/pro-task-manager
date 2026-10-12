<?php
// DATABASE_URL: postgresql://korisnik:lozinka@host/baza?sslmode=require
$url = getenv('DATABASE_URL');
if (!$url) {
    http_response_code(500);
    exit('DATABASE_URL nije podešen.');
}

$parts = parse_url($url);
parse_str($parts['query'] ?? '', $query);

$dsn = sprintf(
    'pgsql:host=%s;port=%d;dbname=%s;sslmode=%s',
    $parts['host'],
    $parts['port'] ?? 5432,
    ltrim($parts['path'] ?? '', '/'),
    $query['sslmode'] ?? 'prefer'
);
$pdo = new PDO($dsn, urldecode($parts['user'] ?? ''), urldecode($parts['pass'] ?? ''), [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
    // Neon pooler (PgBouncer) ne radi dobro sa imenovanim prepared statements
    PDO::PGSQL_ATTR_DISABLE_PREPARES => true,
]);
