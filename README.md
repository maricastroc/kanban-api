# Kanban Task Management API
![image](https://github.com/user-attachments/assets/6876f831-7719-4f52-bf24-3e84eb236bea)

## 📚 Project Description

RESTful API for the fullstack Kanban app, built with Laravel. This backend manages users, boards, columns, tasks, subtasks, and tags. It uses a PostgreSQL database and provides secure access via token-based authentication.

## 📦 Features

- CRUD operations for boards, columns, tasks, subtasks, and tags
- Mark subtasks as complete
- Associate tags with tasks for better organization
- Automatically set one active board per user
- Assign due dates to tasks with automatic status updates
- Token-based authentication using Laravel Sanctum
- Search, pagination, and filters for better performance
- Feature tests with PHPUnit

## 📌 What did I learn?

This backend project was a great opportunity to strengthen my understanding of:

- Laravel policy-based authorization
- Resource transformation using API Resources
- Clean controller logic and service extraction
- Writing unit and feature tests with Laravel's test suite
- API structure and RESTful standards

## 🚀 Tech Stack
- Laravel 11
- PHP 8.3
- PostgreSQL
- PHPUnit
- Laravel Sanctum
- Laravel Resource API
- Eloquent ORM

## 🔍 Links
This API is used by the frontend application available at:
[Deploy](https://kanban-app-maricastroc.vercel.app/)

## ℹ️ How to run the application?

> Clone the repository:

```bash
git clone https://github.com/maricastroc/kanban-api.git
cd kanban-api
```

> Install the dependencies:

```bash
composer install
```

> Copy .env.example and configure your environment:
```bash
cp .env.example .env
```

> Generate application key:
```bash
php artisan key:generate
```

> Run migrations:
```bash
php artisan migrate
```

> Serve the application:
```bash
php artisan serve
```

> Run all tests:
```bash
php artisan test
```

> ⏩ By default, the API will be available at http://localhost:8000.
