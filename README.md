# ProTask

A PHP app for personal tasks: priorities, due dates, search and progress overview.
Every user sees only their own tasks, the admin sees the list of users.
A new account gets a few sample tasks.

Live: https://pro-task-manager-vryi.onrender.com (free Render server, the first visit takes about half a minute)

The interface is in Serbian.

## What it does

- Sign up and login (passwords with `password_hash`)
- Add, edit, delete and mark tasks as done
- Priorities (low, medium, high) and due dates, overdue tasks are marked
- Overview: total, in progress, urgent, done, due in the next 7 days and percent done
- Search and filters (all, in progress, urgent, done)
- Admin panel: all users, how many tasks each has, and deleting accounts

## Security

Queries go through PDO prepared statements, forms have a CSRF token, passwords are hashed.
Everyone sees and changes only their own tasks, and the database address is in `DATABASE_URL`, not in the code.

## Stack

PHP 8.3 (PDO), PostgreSQL (Neon), Apache in Docker (Render), plain CSS without a framework, inline SVG icons.

## Deploying (Neon + Render)

1. Create a database on [Neon](https://neon.tech) and copy the connection string (`postgresql://...?sslmode=require`).
2. Create a Web Service on [Render](https://render.com) from this repo; Render finds the `Dockerfile` on its own.
3. Add to Environment:
   - `DATABASE_URL`: the Neon connection string
   - `ADMIN_USERNAME`: the username that will be admin
4. Tables are created on startup (`bin/migrate.php`).
5. Sign up with the name from `ADMIN_USERNAME` and that account becomes admin.

## Running locally

```bash
export DATABASE_URL="postgresql://user:password@localhost:5432/task_manager?sslmode=disable"
php bin/migrate.php
ADMIN_USERNAME=your_name php -S localhost:8000
```

## Author

Bogdan Šarčević, Užice, Serbia
