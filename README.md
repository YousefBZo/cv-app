# CV Builder

A full-stack web application for building and managing professional CVs/resumes. Users can create a profile and add structured sections — education, experience, projects, certifications, skills, languages, and volunteer work — all served through a RESTful API.

## Tech Stack

| Layer        | Technology                                    |
|------------- |---------------------------------------------- |
| Frontend     | Vue 3 (Composition API), Pinia, Vue Router    |
| Styling      | Tailwind CSS 4                                |
| Backend      | Laravel 12, PHP 8.2+                          |
| Auth         | Laravel Sanctum (token-based), Fortify        |
| Database     | PostgreSQL 16                                 |
| Bundler      | Vite 7                                        |
| Infra        | Docker (PHP-FPM, Nginx, PostgreSQL)           |

## Project Structure

```
cv-app/
├── backend/            # Laravel 12 API
│   ├── app/
│   │   ├── Http/       # Controllers, Requests, Resources
│   │   ├── Models/     # Eloquent models
│   │   ├── Services/   # Business logic layer
│   │   └── Providers/  # Service providers
│   ├── config/         # Application configuration
│   ├── database/       # Migrations & seeders
│   ├── docker/         # Dockerfile configs (PHP-FPM, Nginx)
│   ├── routes/         # API route definitions
│   └── docker-compose.yaml
│
├── frontend/           # Vue 3 SPA
│   ├── src/
│   │   ├── api/        # Axios HTTP client
│   │   ├── modules/    # Feature modules (auth, cv, user)
│   │   ├── router/     # Vue Router configuration
│   │   └── shared/     # Shared components & composables
│   └── vite.config.js
│
└── README.md
```

## Features

- **Authentication** — Register, login, and logout with Sanctum token auth
- **Profile Management** — Create, update, and delete personal profile with photo upload
- **Education** — Add degrees with institution, field of study, and date range
- **Work Experience** — Track positions with company, role, and description
- **Projects** — Showcase projects with links, GitHub URLs, and cover images
- **Certifications** — Store certificates with issuing organization and dates
- **Skills** — Select from a predefined list with proficiency levels
- **Languages** — Add languages with proficiency levels
- **Volunteer Work** — Record volunteer experiences
- **Full CV View** — Aggregated read-only endpoint returning the complete CV

## Prerequisites

- [Docker](https://www.docker.com/) & Docker Compose
- [Node.js](https://nodejs.org/) ≥ 18 and npm

## Getting Started

### 1. Clone the repository

```bash
git clone https://github.com/YousefBZo/cv-app.git
cd cv-app
```

### 2. Backend setup

```bash
cd backend

# Copy environment file and adjust values if needed
cp .env.example .env

# Start Docker containers (PHP-FPM, Nginx, PostgreSQL)
docker compose up -d

# Install PHP dependencies
docker compose exec php composer install

# Generate application key
docker compose exec php php artisan key:generate

# Run database migrations
docker compose exec php php artisan migrate

# (Optional) Seed sample data
docker compose exec php php artisan db:seed
```

The API will be available at **http://localhost:8082**.

### 3. Frontend setup

```bash
cd frontend

# Install dependencies
npm install

# Start development server
npm run dev
```

The SPA will be available at **http://localhost:3000**.

## API Endpoints

All endpoints are prefixed with `/api/v1` and require authentication (`Authorization: Bearer <token>`) unless noted otherwise.

| Method             | Endpoint                          | Description                     |
|------------------- |---------------------------------- |-------------------------------- |
| `POST`             | `/register`                       | Register a new user ¹           |
| `POST`             | `/login`                          | Authenticate and receive token ¹|
| `POST`             | `/logout`                         | Revoke current token            |
| `GET`              | `/user`                           | Get authenticated user          |
| `PUT`              | `/user/name`                      | Update user name                |
| `PUT`              | `/user/password`                  | Update user password            |
| `GET`              | `/cv`                             | Get complete CV (all sections)  |
| `GET/POST/PUT/DEL` | `/profile`                       | Profile CRUD                    |
| `GET/POST/PUT/DEL` | `/education`                     | Education CRUD                  |
| `GET/POST/PUT/DEL` | `/experience`                    | Experience CRUD                 |
| `GET/POST/PUT/DEL` | `/project`                       | Project CRUD                    |
| `GET/POST/PUT/DEL` | `/certification`                 | Certification CRUD              |
| `GET/POST/PUT/DEL` | `/volunteer`                     | Volunteer Experience CRUD       |
| `GET/POST/PUT/DEL` | `/skill`                         | Skill CRUD                      |
| `GET/POST/PUT/DEL` | `/language`                      | Language CRUD                   |
| `GET`              | `/skill/available`                | List predefined skills          |
| `GET`              | `/language/available`             | List predefined languages       |
| `GET`              | `/education/fields`               | List predefined fields of study |

¹ Public endpoint — no token required.

## Architecture

```
Client (Vue 3 SPA)
  │
  ├─► Axios HTTP client (/api/*)
  │
  ▼
Nginx (reverse proxy :8082)
  │
  ▼
PHP-FPM (Laravel 12)
  ├─► Routes ──► Controllers ──► Services ──► Models
  ├─► FormRequest validation (with bail)
  ├─► JSON API Resources (response shaping)
  └─► Sanctum token authentication
  │
  ▼
PostgreSQL 16
```

## Running Tests

```bash
docker compose exec php php artisan test
```

## License

This project is for educational purposes.
