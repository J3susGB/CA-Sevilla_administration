# 🛠️ Backend Setup - Symfony 6.4 + PostgreSQL + Docker

Este documento describe paso a paso la creación y configuración del entorno backend de este proyecto: stack Symfony 6.4, PostgreSQL 15 y Docker, desde cero hasta dejarlo totalmente operativo y preparado para desarrollo.

---

## 📁 Estructura inicial del backend

El proyecto está organizado así:

```
symfony-backend/
│   ├── app/
│   └── docker/php/Dockerfile

```

---

## 1️⃣ Crear proyecto Symfony con Composer

```bash
composer create-project symfony/skeleton:"6.4.*" app
```

---

## 2️⃣ Configurar Docker

### 🐘 `docker-compose.yml`

```yaml
version: '3.8'

services:
  db:
    image: postgres:15
    container_name: postgres_db
    restart: unless-stopped
    environment:
      POSTGRES_USER: ca_sevilla_user
      POSTGRES_PASSWORD: 8QJrUjvs
      POSTGRES_DB: ca_sevilla_db
    volumes:
      - pgdata:/var/lib/postgresql/data
    ports:
      - "5432:5432"
    healthcheck:
      test: ["CMD-SHELL", "pg_isready -U ca_sevilla_user"]
      interval: 5s
      timeout: 5s
      retries: 5

  backend:
    build:
      context: ./symfony-backend
      dockerfile: ./docker/php/Dockerfile
    container_name: symfony_backend
    working_dir: /var/www/app
    volumes:
      - ./symfony-backend/app:/var/www/app
    depends_on:
      db:
        condition: service_healthy
    environment:
      DATABASE_URL: pgsql://ca_sevilla_user:8QJrUjvs@db:5432/ca_sevilla_db
    ports:
      - "8000:8000"
    command: >
      bash -c "
        until pg_isready -h db -U ca_sevilla_user -d ca_sevilla_db; do
          sleep 2;
        done;
        composer install;
        php -S 0.0.0.0:8000 -t public
      "

volumes:
  pgdata:
```

---

### 🐳 `Dockerfile` (PHP + Extensiones necesarias)

```Dockerfile
FROM php:8.2-cli

RUN apt-get update && apt-get install -y \
    unzip \
    libpq-dev \
    libicu-dev \
    libzip-dev \
    zip \
    && docker-php-ext-install intl pdo pdo_pgsql

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/app
```

---

## 3️⃣ Configurar `.env` (opcional, sobreescrito por Docker)

```dotenv
DATABASE_URL="pgsql://ca_sevilla_user:8QJrUjvs@db:5432/ca_sevilla_db"
```

---

## 4️⃣ Levantar el entorno

```bash
docker compose down -v        # Limpia todo por si acaso
docker compose up --build     # Levanta contenedores y construye
```

---

## 5️⃣ Verificar conexión desde el contenedor

```bash
docker exec -it symfony_backend bash
```

Luego dentro:

```bash
printenv DATABASE_URL
php bin/console doctrine:query:sql "SELECT 1"
```

✅ Resultado esperado:

```
?column?
---------
1
```

---

## 6️⃣ Instalar Doctrine ORM

```bash
composer require symfony/orm-pack
composer require --dev symfony/maker-bundle
```

---

## 7️⃣ Crear y sincronizar la base de datos

```bash
php bin/console doctrine:database:create
php bin/console doctrine:schema:update --force
```

(Si aún no hay entidades, mostrará “No Metadata Classes to process”)

---

## ✅ Entorno listo

- Symfony responde en http://localhost:8000
- La base de datos está conectada y viva
- Doctrine está instalado
- Listo para crear la primera entidad

---

Hecho con ❤️ por: Jesús G.B 🚀