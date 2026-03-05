# FeedsForLess — Local Development Setup Guide

Welcome to the FeedsForLess B2B platform repository. This guide covers the complete setup process for local development, including both the Laravel backend (API) and the Vue.js frontend.

## Prerequisites

Ensure your local machine has the following software installed before proceeding:
- **PHP** >= 8.1
- **Composer** (Dependency Manager for PHP)
- **Node.js** >= 18.x and **NPM** (or Yarn/pnpm)
- **MySQL** >= 8.0 (or PostgreSQL/SQLite equivalent)
- **Git**

---

## 1. Backend Setup (Laravel API)

The backend handles the core business logic, database, and authentication via Laravel Sanctum.

### Step-by-step Installation

1. **Navigate to the backend directory:**
   ```bash
   cd backend

2. **Install PHP dependencies:**
   ```bash
   composer install

3. **Create the `.env` file:**
   ```bash
   cp .env.example .env

4. **Generate the application key:**
   ```bash
   php artisan key:generate

5. **Configure the database:**
   Edit `.env` and set your database credentials:
   ```env
    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=feedsforless_db
    DB_USERNAME=root
    DB_PASSWORD=your_password

    # Ensure CORS and Frontend URL are properly linked
    FRONTEND_URL=http://localhost:5173
    SANCTUM_STATEFUL_DOMAINS=localhost:5173
   ```

6. **Create the database:**
   ```bash
   php artisan migrate --seed

7. Create the storage symlink:
    Required to serve images and documents locally.

    Bash
    php artisan storage:link
    Start the local server:

    Bash
    php artisan serve

    The backend API will be available at http://localhost:8000.

---

## 2. Frontend Setup (Vue 3 + Vite)

The frontend is a Single Page Application (SPA) built with Vue 3, Vite, Vue Router, and Pinia.

Step-by-step Installation
Navigate to the frontend directory:

Bash
cd frontend
Install Node dependencies:

Bash
    npm install
Start the development server:

Bash
npm run dev
The frontend application will be available at http://localhost:5173.