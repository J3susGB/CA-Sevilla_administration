# 🎨 Frontend Setup - Angular 17 + TailwindCSS 3.3 + Docker

Este documento detalla cómo levantar y desarrollar el entorno frontend de este proyecto usando Angular 17, TailwindCSS y Docker.

---

## 📁 Estructura del frontend

```bash
angular-frontend/
│   ├── src/
│   ├── Dockerfile
│   ├── package.json
│   └── tailwind.config.js
```

---

## 1️⃣ Crear proyecto Angular (si se parte desde cero)

```bash
ng new angular-frontend --style=css --routing
```

---

## 2️⃣ Instalar TailwindCSS

```bash
npm uninstall tailwindcss
npm install -D tailwindcss@3.3.5
```

Asegúrate de tener también `postcss` y `autoprefixer` instalados:

```bash
npm install -D postcss autoprefixer
```

---

## 3️⃣ Inicializar configuración de Tailwind

```bash
npx tailwindcss init
```

Edita `tailwind.config.js`:

```js
/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./src/**/*.{html,ts}"
  ],
  theme: {
    extend: {},
  },
  plugins: [],
}
```

---

## 4️⃣ Configurar `styles.css`

Edita `src/styles.css` y agrega:

```css
@tailwind base;
@tailwind components;
@tailwind utilities;
```

---

## 5️⃣ Dockerfile del frontend

```Dockerfile
FROM node:18-alpine

WORKDIR /app

COPY package*.json ./
RUN npm install

COPY . .

EXPOSE 4200

CMD ["npm", "run", "start"]
```

---

## 6️⃣ Docker Compose (extracto relevante)

```yaml
frontend:
  container_name: angular_frontend
  build:
    context: ./angular-frontend
    dockerfile: Dockerfile
  ports:
    - "4200:4200"
  volumes:
    - ./angular-frontend:/app
  working_dir: /app
  command: npm run start
```

---

## 7️⃣ Levantar el frontend

```bash
docker compose up --build frontend
```

---

## ✅ Verificación

Accede en el navegador a:

```
http://localhost:4200
```

Deberías ver la aplicación Angular corriendo correctamente.

---

Hecho con ❤️ por: Jesús G.B 🚀