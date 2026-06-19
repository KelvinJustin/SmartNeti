# Stage 1: Build frontend
FROM node:20-alpine AS frontend-builder
WORKDIR /app/frontend
COPY frontend/package*.json ./
RUN npm install
COPY frontend/ ./
RUN npm run build

# Stage 2: Production
FROM node:20-alpine
WORKDIR /app

# Install backend deps
COPY backend/package*.json ./
RUN npm install --production

# Copy backend source
COPY backend/ ./

# Copy built frontend
COPY --from=frontend-builder /app/frontend/dist ./dist

EXPOSE 8080
CMD ["node", "src/index.js"]
