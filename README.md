
# 📦 CA-SEVILLA_ADMINISTRATION

Este proyecto es una aplicación web full-stack desarrollada con **Angular** en el frontend y **Symfony** en el backend, diseñada para administrar y gestionar entidades internas (como tareas, usuarios u otros módulos que se irán añadiendo). Todo el entorno está montado y orquestado con **Docker Compose**.

---

## 🚀 Tecnologías Utilizadas

| Parte       | Tecnología         | Versión aproximada |
|-------------|--------------------|---------------------|
| Frontend    | Angular            |                     |
| Estilos     | Tailwind CSS       | Pendiente           |
| Backend     | Symfony            | 6.4.20              |
| Base de datos | PostgreSQL       | 15.0                |
| Orquestación | Docker Compose    | Última estable      |

---

## 📂 Estructura del Proyecto

```
📁 angular-frontend       → Aplicación Angular (standalone)
📁 symfony-backend        → Proyecto Symfony (API REST)
📁 documentation          → Documentación del proyecto
├── backend-setup.md      → Guía detallada del backend
docker-compose.yml        → Orquestación de servicios (Angular, Symfony, PostgreSQL)
README.md                 → Este archivo 😄
```

---

## ⚙️ Puesta en Marcha Rápida

> Asegúrate de tener [Docker](https://docs.docker.com/get-docker/) y [Docker Compose](https://docs.docker.com/compose/) instalados en tu máquina.

1. Clona el repositorio:
```bash
git clone <url-del-repo>
cd CA-Sevilla_administration
```

2. Lanza todos los servicios con Docker:
```bash
docker-compose up -d --build
```

3. Accede a las interfaces:
   - **Frontend (Angular):** http://localhost:4200
   - **Backend (Symfony API):** http://localhost:8000

---

## 📘 Documentación del Backend

Toda la configuración tanto de Frontend como del backend están en:

👉 [Backend](https://github.com/J3susGB/CA-Sevilla_administration/blob/main/documentacion/backend-setup.md)

---

## 🧪 Funcionalidades Implementadas (Hasta Ahora)

- Puesta en marcha del Backend de la aplicación.

---

## 🧪 Comandos Útiles

```bash
# Acceder al contenedor de Symfony
docker exec -it symfony_backend bash

# Crear migraciones (dentro del contenedor)
php bin/console make:migration

# Ejecutar migraciones
php bin/console doctrine:migrations:migrate

# Ver estado del servidor Symfony
php bin/console server:status
```

---

## 📝 Próximos Pasos (Roadmap)

- [ ] Puesta en marcha del Frontend

---

## 🧠 Créditos

Este proyecto ha sido desarrollado como parte del Trabajo Fin de Grado, con el que aprendo desarrollo fullstack con Symfony + Angular en entornos Dockerizados.

---

## `📫 Contacta conmigo:`

[![Gmail](https://img.shields.io/badge/-GMAIL-D14836?style=for-the-badge&logo=gmail&logoColor=white)](mailto:jgomezbeltran88@gmail.com)
[![LinkedIn](https://img.shields.io/badge/LinkedIn-informational?style=for-the-badge&logo=linkedin&logoColor=fff&color=0077B5)](https://www.linkedin.com/in/jesusgb-dev/)
[![Portfolio](https://img.shields.io/badge/-Portfolio-lightgray?style=for-the-badge&logo=stackoverflow&logoColor=white)](https://j3susgb.github.io/Portfolio/)
[![Linktree](https://img.shields.io/badge/-Linktree-323330?style=for-the-badge&logo=linktree&logoColor=#41e45f)](https://linktr.ee/jesusgb)

## `✔️ Consulta los Repositorios y no olvides dar una estrella ⬇️`

:star: From [J3susGB](https://github.com/J3susGB?tab=repositories)

[![ForTheBadge built-with-love](http://ForTheBadge.com/images/badges/built-with-love.svg)](hhttps://github.com/J3susGB?tab=repositories)  

 
***************************************************************

Hecho con ❤️ por: Jesús G.B 🚀