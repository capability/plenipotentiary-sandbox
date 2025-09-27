# --- Dev image for Docusaurus ---
FROM node:22-slim

ARG PNPM_VERSION=10.17.1
ENV NODE_ENV=development
WORKDIR /app

# 1) Install deps with good cache use
COPY package.json pnpm-lock.yaml ./
RUN corepack enable \
 && corepack prepare pnpm@${PNPM_VERSION} --activate \
 && pnpm install --frozen-lockfile

# 2) App source
COPY . .

EXPOSE 3000
# Bind to all interfaces so Compose/DevContainer can forward
CMD ["pnpm", "dev"]
