# --- Dev image for Docusaurus ---
FROM node:22-slim

ENV NODE_ENV=development
WORKDIR /app

# 1) Install deps with npm (fresh install to avoid cache issues)
COPY package.json package-lock.json ./
RUN npm install

# 2) App source
COPY . .

EXPOSE 3000
# Bind to all interfaces so Compose/DevContainer can forward
CMD ["npm", "run", "dev"]
