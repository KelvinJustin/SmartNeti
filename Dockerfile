# Stage 1: Build frontend
FROM node:20-alpine AS frontend-builder
WORKDIR /app/frontend
COPY frontend/package*.json ./
RUN npm install
COPY frontend/ ./
RUN npm run build
RUN echo "=== Frontend build output ===" && ls -la /app/frontend/dist && ls -la /app/frontend/dist/assets || echo "No assets dir"

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
RUN echo "=== Copied dist to /app/dist ===" && ls -la /app/dist && ls -la /app/dist/assets || echo "No assets dir in /app/dist"

EXPOSE 8080
CMD ["node", "src/index.js"]
