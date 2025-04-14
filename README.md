
# 📦 CA-SEVILLA_ADMINISTRATION

Proyecto de administración para la Delegación de Árbitros de Sevilla desarrollado con Symfony (backend) y Angular (frontend).

---

## 🧭 Descripción del proyecto

Esta aplicación web surge como una solución integral para automatizar la gestión interna de la Delegación de Árbitros de Sevilla. Actualmente, muchas tareas se realizan de forma manual y dispersa utilizando archivos Excel. Este sistema centraliza y digitaliza todos los procesos, con el objetivo de generar automáticamente la clasificación final de cada árbitro o árbitra en base a su rendimiento y participación.

Entre sus funcionalidades principales destacan:

- Control de asistencia a clases teóricas y prácticas (puntuable)
- Control de asistencia a entrenamientos (puntuable)
- Registro de notas de test online, exámenes teóricos e informes arbitrales
- Gestión de bonificaciones según participación
- Elaboración automática de clasificaciones
- Sistema interno de reportes de talento (con acceso mediante token)
- Roles personalizados por tipo de usuario (Administrador, Capacitación, Información, Clasificación, Profesor, Público)

Con este sistema, se pretende facilitar enormemente el trabajo administrativo, evitando procesos repetitivos y mejorando la trazabilidad y transparencia de la información.

## 🚀 Tecnologías Utilizadas

| Parte       | Tecnología         | Versión aproximada |
|-------------|--------------------|---------------------|
| Frontend    | Angular            | Pendiente           |
| Estilos     | Tailwind CSS       | Pendiente           |
| Backend     | Symfony            | 6.4.20              |
| Base de datos | PostgreSQL       | 15.0                |
| Orquestación | Docker Compose    | Última estable      |

---

## 📂 Estructura del Proyecto

```
📁 angular-frontend       → Frontend Angular (por desarrollar)
📁 symfony-backend        → Backend Symfony 6.4
📁 documentation          
├── backend-setup.md      → Documentación técnica completa del backend
├── frontend-setup.md     → Documentación técnica del frontend (pendiente)
docker-compose.yml        
README.md                 
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

## 📘 Puesta en marcha del Backend

Consulta la guía detallada de instalación y configuración del entorno Symfony + PostgreSQL aquí:

👉 [Backend](https://github.com/J3susGB/CA-Sevilla_administration/blob/main/documentacion/backend-setup.md)

---

## ✅ Funcionalidades Implementadas (Hasta Ahora)

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

- [ ] Iniciar el desarrollo del frontend (Angular)

---

## 🎯 Motivación

El proyecto nace de una necesidad real: actualmente, el control de entrenamientos y la generación de clasificaciones se hace de forma manual, usando archivos Excel enviados por compañero.  
Este sistema permitirá que cada persona suba directamente sus datos a través de la plataforma, automatizando la generación de clasificaciones y reduciendo la carga de trabajo administrativa.

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

[![ForTheBadge built-with-love](http://ForTheBadge.com/images/badges/built-with-love.svg)](https://github.com/J3susGB?tab=repositories)

 
***************************************************************

Hecho con ❤️ por: Jesús G.B 🚀