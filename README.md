# ProTask

PHP aplikacija za lične zadatke: prioriteti, rokovi, pretraga i pregled napretka.
Svaki korisnik vidi samo svoje zadatke, admin vidi listu korisnika.
Nov nalog dobija nekoliko primera zadataka.

Uživo: https://pro-task-manager-vryi.onrender.com (besplatan Render server, prvo otvaranje traje oko pola minuta)

## Šta radi

- Registracija i prijava (lozinke preko `password_hash`)
- Dodavanje, izmena, brisanje i označavanje zadataka kao završenih
- Prioriteti (nizak, srednji, visok) i rokovi, zadatak koji kasni je označen
- Pregled: ukupno, u toku, hitno, završeno, rokovi za narednih 7 dana i procenat završenog
- Pretraga i filteri (svi, u toku, hitno, završeni)
- Admin panel: svi korisnici, broj njihovih zadataka i brisanje naloga

## Bezbednost

Upiti idu preko PDO prepared statements, forme imaju CSRF token, lozinke su heširane.
Svako vidi i menja samo svoje zadatke, a adresa baze je u `DATABASE_URL`, ne u kodu.

## Tehnologije

PHP 8.3 (PDO), PostgreSQL (Neon), Apache u Docker-u (Render), čist CSS bez frameworka, SVG ikonice ugrađene u kod.

## Objavljivanje (Neon + Render)

1. Na [Neon](https://neon.tech)-u napravi bazu i kopiraj connection string (`postgresql://...?sslmode=require`).
2. Na [Render](https://render.com)-u napravi Web Service iz ovog repozitorijuma; Render sam nađe `Dockerfile`.
3. U Environment dodaj:
   - `DATABASE_URL`: connection string sa Neon-a
   - `ADMIN_USERNAME`: korisničko ime koje će biti admin
4. Tabele se prave same pri startu (`bin/migrate.php`).
5. Registruj se pod imenom iz `ADMIN_USERNAME` i taj nalog postaje admin.

## Lokalno

```bash
export DATABASE_URL="postgresql://korisnik:lozinka@localhost:5432/task_manager?sslmode=disable"
php bin/migrate.php
ADMIN_USERNAME=tvoje_ime php -S localhost:8000
```

## Autor

Bogdan Šarčević
