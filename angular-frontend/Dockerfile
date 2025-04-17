# angular-frontend/Dockerfile

# 1. Imagen base oficial de Node
FROM node:18-alpine

# 2. Directorio de trabajo dentro del contenedor
WORKDIR /app

# 3. Copiar package.json y package-lock.json primero para aprovechar la cache
COPY package*.json ./

# 4. Instalar dependencias de Node
RUN npm install

# 5. Copiar el resto del proyecto
COPY . .

# 6. Exponer el puerto 4200 para Angular
EXPOSE 4200

# 7. Comando de arranque: lanzar sólo el servidor Angular
CMD ["npm", "start"]
