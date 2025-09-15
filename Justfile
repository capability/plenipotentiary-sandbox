# ============================================================================
# Justfile — host/container aware helpers for Laravel backend + pnpm frontend
# ============================================================================
set shell := ["bash", "-euo", "pipefail", "-c"]

set dotenv-load
project_guess := `basename "$PWD"`
PROJECT_SLUG := if env_var("PROJECT_SLUG") != "" { env_var("PROJECT_SLUG") } else { project_guess }
DOMAIN := env_var("DOMAIN")

# Detect if we are inside a container (devcontainer or service)
_in_container := `test -f /.dockerenv && echo 1 || echo 0`

# Canonical container user (matches your Dockerfile/devcontainer)
container_user := "app-user"

# Docker Compose + common service names
compose     := "docker compose -f docker-compose.yml -f docker-compose.dev.yml"
compose_prod := "docker compose -f docker-compose.yml"
api_svc     := "api"
web_svc     := "web"
db_svc      := "db"
cache_svc   := "cache"
mail_svc    := "mail"
fe_svc      := "frontend"
horizon_svc := "horizon"
caddy_svc   := "caddy"
prom_svc    := "prometheus"
graf_svc    := "grafana"
loki_svc    := "loki"
otel_svc    := "otel-collector"
prism_svc   := "prism"

# Backend working dir search:
# 1) stack-root devcontainer
# 2) older devcontainer defaults
# 3) running service path
backend_cd := 'for B in \
  /home/app-user/workspaces/stack-root/apps/backend \
  /workspaces/app/apps/backend \
  /workspace/apps/backend \
  /var/www/html \
; do \
  [ -d "$B" ] && cd "$B" && exit 0; \
done; echo "backend dir not found" >&2; exit 1'

# Frontend working dir inside the frontend container
fe_dir := "/usr/src/app"

# Guard: some recipes must run on the host
ensure_host := '''
if ! command -v docker >/dev/null 2>&1; then
  echo "Docker CLI not found on PATH."
  exit 1
fi
if [ -f /.dockerenv ]; then
  echo "You're inside a dev/service container. Run this on the host."
  exit 1
fi
'''

default:
    @echo "Common recipes:"
    @echo "  up, down, nuke, tiers-up, tiers-down, fe-install, install, test"
    @echo "Use: just <recipe>"

# ----------------------------------------------------------------------------
# Quick starts at the very top
# ----------------------------------------------------------------------------
# Spin up backend with SSL: web, api, db, cache, mail, caddy
quick-up-backend-ssl:
    @{{ensure_host}}
    {{compose}} --profile ssl up -d {{db_svc}} {{cache_svc}} {{mail_svc}} {{api_svc}} {{web_svc}} {{caddy_svc}}
    {{compose}} ps
    echo "Backend + SSL up. Try: curl -I https://{{DOMAIN}} --resolve {{DOMAIN}}:443:127.0.0.1 -k"

# Spin up frontend with SSL: frontend, caddy
quick-up-frontend-ssl:
    @{{ensure_host}}
    {{compose}} --profile ssl up -d {{fe_svc}} {{caddy_svc}}
    {{compose}} ps {{fe_svc}} {{caddy_svc}}
    echo "Frontend + SSL up. If Caddy proxies FE, hit your HTTPS domain. Otherwise: curl -i http://localhost:5173/"

# Bring everything down for this project
quick-down-all:
    @{{ensure_host}}
    {{compose}} -p {{PROJECT_SLUG}} down -v --remove-orphans || true
    docker rm -f $(docker ps -aq -f "label=com.docker.compose.project={{PROJECT_SLUG}}") 2>/dev/null || true
    docker network rm {{PROJECT_SLUG}}_default 2>/dev/null || true
    echo "All containers, volumes and the default network removed for this project"

# ----------------------------------------------------------------------------
# Utility
# ----------------------------------------------------------------------------
whereami:
    @if [ "{{_in_container}}" = "1" ]; then echo "inside-container"; else echo "host"; fi

sh:
    @if [ "{{_in_container}}" = "1" ]; then \
      exec bash; \
    else \
      {{compose}} exec -it -u {{container_user}} {{api_svc}} bash; \
    fi

sh-root:
    @if [ "{{_in_container}}" = "1" ]; then \
      exec -u root bash; \
    else \
      {{compose}} exec -it -u root {{api_svc}} bash; \
    fi

up:
    @{{ensure_host}}
    {{compose}} up -d {{db_svc}} {{cache_svc}} {{mail_svc}} {{api_svc}} {{web_svc}}
    {{compose}} ps

down:
    @{{ensure_host}}
    {{compose}} down --remove-orphans

nuke:
    @{{ensure_host}}
    {{compose}} down -v --remove-orphans

restart:
    @{{ensure_host}}
    {{compose}} up -d --force-recreate

logs svc='api':
    @{{ensure_host}}
    {{compose}} logs -f --tail=200 {{svc}}

# ----------------------------------------------------------------------------
# Backend (Laravel)
# ----------------------------------------------------------------------------
run-backend cmd:
    @if [ "{{_in_container}}" = "1" ]; then \
      {{backend_cd}} && {{cmd}}; \
    else \
      {{compose}} exec -T -u {{container_user}} {{api_svc}} bash -lc '{{backend_cd}} && {{cmd}}'; \
    fi

install:
    @just run-backend 'composer install --no-interaction'

test:
    @just run-backend 'composer test'

test-cov:
    @just run-backend '(composer run -q test:cov || php artisan test --coverage --min=0)'

watch-app-tests:
    @{{ensure_host}}
    watchexec -e php -w apps/backend -- \
      docker compose exec api vendor/bin/pest --colors=always

watch-package-tests:
    @{{ensure_host}}
    watchexec -e php -w packages/plenipotentiary-laravel -- \
      docker compose exec api bash -lc "cd /workspaces/stack-root/packages/plenipotentiary-laravel && vendor/bin/pest --colors=always"

# Watch BOTH in parallel with prefixed output
watch-all-tests:
	@{{ensure_host}}
	bash -ceu '\
	  prefix(){ stdbuf -oL sed -e "s/^/[$$1] /"; } ; \
	  ( watchexec -e php -w apps/backend -- \
	      docker compose exec api vendor/bin/pest --colors=always | prefix app ) & APP=$$! ; \
	  ( watchexec -e php -w packages/plenipotentiary-laravel -- \
	      docker compose exec api bash -lc "cd /workspaces/stack-root/packages/plenipotentiary-laravel && vendor/bin/pest --colors=always" | prefix pkg ) & PKG=$$! ; \
	  trap "kill $$APP $$PKG" INT TERM ; \
	  wait \
	'

lint:
    @just run-backend 'composer lint'

lint-fix:
    @just run-backend 'composer lint:fix'

stan:
    @just run-backend 'composer stan'

rector:
    @just run-backend 'composer rector'

migrate:
    @just run-backend 'php artisan migrate --force'

artisan +ARGS:
    @just run-backend 'php artisan {{ARGS}}'

# ----------------------------------------------------------------------------
# Frontend (pnpm/Vite) — always via the frontend service
# ----------------------------------------------------------------------------
fe-install:
    @{{ensure_host}}
    {{compose}} pull {{fe_svc}} >/dev/null || true
    {{compose}} run -T --rm --no-deps -w {{fe_dir}} -e CI=1 {{fe_svc}} sh -lc '\
      corepack enable && \
      corepack prepare pnpm@10.15.1 --activate && \
      rm -rf node_modules && \
      pnpm install --frozen-lockfile --reporter=silent --no-color \
    '

fe-install-fast:
    @{{ensure_host}}
    {{compose}} pull {{fe_svc}} >/dev/null || true
    {{compose}} run -T --rm --no-deps -w {{fe_dir}} -e CI=1 {{fe_svc}} sh -lc '\
      corepack enable && \
      corepack prepare pnpm@10.15.1 --activate && \
      pnpm install --frozen-lockfile --reporter=silent --no-color \
    '

fe-dev:
    @{{ensure_host}}
    {{compose}} up -d --force-recreate --no-deps {{fe_svc}}
    {{compose}} logs -f --since=10s {{fe_svc}}

fe-down:
    @{{ensure_host}}
    {{compose}} stop {{fe_svc}}

fe-shell:
    @{{ensure_host}}
    {{compose}} run --rm -it --no-deps -w {{fe_dir}} {{fe_svc}} sh -lc '\
      corepack enable && corepack prepare pnpm@10.15.1 --activate && exec sh'

fe-proof-cache:
    @{{ensure_host}}
    {{compose}} run -T --rm --no-deps -w {{fe_dir}} -e CI=1 {{fe_svc}} sh -lc '\
      set -e; corepack enable; corepack prepare pnpm@10.15.1 --activate; \
      echo cfg=$(pnpm config get store-dir); \
      echo path=$(pnpm store path); \
      du -sh /pnpm-store || true; \
      rm -rf node_modules; \
      pnpm install --frozen-lockfile --offline --reporter=silent --no-color; \
      echo OFFLINE_OK \
    '

fe-store-stat:
    @{{ensure_host}}
    {{compose}} run -T --rm --no-deps -w {{fe_dir}} {{fe_svc}} sh -lc '\
      corepack enable && corepack prepare pnpm@10.15.1 --activate && \
      echo path=$(pnpm store path) && du -sh /pnpm-store || true'

fe-store-prune:
    @{{ensure_host}}
    {{compose}} run -T --rm --no-deps -w {{fe_dir}} {{fe_svc}} sh -lc '\
      corepack enable && corepack prepare pnpm@10.15.1 --activate && \
      pnpm store prune'

fe-store-clear:
    @{{ensure_host}}
    {{compose}} run -T --rm --no-deps {{fe_svc}} sh -lc 'rm -rf /pnpm-store/* && echo "pnpm store cleared"'

# ----------------------------------------------------------------------------
# CI simulators (mirror .github/workflows/ci.yml)
# ----------------------------------------------------------------------------

ci:
    just ci-pint
    just ci-phpstan
    just ci-backend-sqlite
    just ci-package
    just ci-frontend

ci-all:
    just ci-pint
    just ci-phpstan
    just ci-backend-sqlite
    just ci-backend-mysql
    just ci-package
    just ci-frontend

# Style checks (Laravel Pint)
ci-pint:
    @{{compose}} exec -T -u {{container_user}} {{api_svc}} bash -lc '\
    cd /workspaces/stack-root/apps/backend; \
    [ -f vendor/autoload.php ] || composer install --no-interaction --no-progress --prefer-dist; \
    vendor/bin/pint --test . ../../packages/plenipotentiary-laravel \
    '

ci-phpstan:
    @{{compose}} exec -T -u {{container_user}} {{api_svc}} bash -lc '\
    set -eu; \
    export XDEBUG_MODE=off; \
    cd /workspaces/stack-root/apps/backend; \
    [ -f vendor/autoload.php ] || composer install --no-interaction --no-progress --prefer-dist; \
    mkdir -p /tmp/phpstan-app; \
    php -d memory_limit=2G vendor/bin/phpstan analyse -c phpstan.neon.dist --no-progress \
    '
ci-phpstan-split:
    @{{compose}} exec -T -u {{container_user}} {{api_svc}} bash -lc '\
    set -eu; \
    export XDEBUG_MODE=off; \
    cd /workspaces/stack-root/apps/backend; \
    [ -f vendor/autoload.php ] || composer install --no-interaction --no-progress --prefer-dist; \
    mkdir -p /tmp/phpstan-app; \
    php -d memory_limit=1G vendor/bin/phpstan analyse app database -c phpstan.neon.dist --no-progress; \
    php -d memory_limit=1G vendor/bin/phpstan analyse ../../packages/plenipotentiary-laravel/src -c phpstan.neon.dist --no-progress \
    '

# Strict mode to catch new issues (baseline still applied if included in config)
ci-phpstan-max:
    @{{compose}} exec -T -u {{container_user}} {{api_svc}} bash -lc '\
    set -eu; \
    export XDEBUG_MODE=off; \
    cd /workspaces/stack-root/apps/backend; \
    [ -f vendor/autoload.php ] || composer install --no-interaction --no-progress --prefer-dist; \
    mkdir -p /tmp/phpstan-app; \
    php -d memory_limit=2G vendor/bin/phpstan analyse -c phpstan.neon.dist --level=max --no-progress \
    '

# Regenerate the baseline at max (use when you’ve fixed stuff)
ci-phpstan-baseline:
    @{{compose}} exec -T -u {{container_user}} {{api_svc}} bash -lc '\
    set -eu; \
    export XDEBUG_MODE=off; \
    cd /workspaces/stack-root/apps/backend; \
    [ -f vendor/autoload.php ] || composer install --no-interaction --no-progress --prefer-dist; \
    mkdir -p /tmp/phpstan-app; \
    php -d memory_limit=2G vendor/bin/phpstan analyse -c phpstan.neon.dist --level=max --generate-baseline=phpstan-baseline.neon --no-progress \
    '

# Package-only scan (lower peak RAM than scanning everything)
ci-phpstan-package:
    @{{compose}} exec -T -u {{container_user}} {{api_svc}} bash -lc '\
    set -eu; \
    export XDEBUG_MODE=off; \
    cd /workspaces/stack-root/apps/backend; \
    [ -f vendor/autoload.php ] || composer install --no-interaction --no-progress --prefer-dist; \
    mkdir -p /tmp/phpstan-app; \
    php -d memory_limit=2G vendor/bin/phpstan analyse -c phpstan.neon.dist ../../packages/plenipotentiary-laravel/src --no-progress \
    '

# Debug (single worker-ish output to find crashes)
ci-phpstan-debug:
    @{{compose}} exec -T -u {{container_user}} {{api_svc}} bash -lc '\
    set -eu; \
    export XDEBUG_MODE=off; \
    cd /workspaces/stack-root/apps/backend; \
    [ -f vendor/autoload.php ] || composer install --no-interaction --no-progress --prefer-dist; \
    mkdir -p /tmp/phpstan-app; \
    php -d memory_limit=2G vendor/bin/phpstan analyse -c phpstan.neon.dist --debug --no-progress \
    '
    
# Backend tests with sqlite (default, in-memory DB + array cache)
ci-backend-sqlite:
    @{{compose}} exec -T -u {{container_user}} {{api_svc}} bash -lc '\
    set -eu; \
    cd /workspaces/stack-root/apps/backend; \
    [ -f vendor/autoload.php ] || composer install --no-interaction --no-progress --prefer-dist; \
    [ -f .env ] || cp .env.example .env; \
    php artisan key:generate --force; \
    export DB_CONNECTION=sqlite DB_DATABASE=":memory:" CACHE_DRIVER=array QUEUE_CONNECTION=sync SESSION_DRIVER=array; \
    php artisan config:clear && php artisan route:clear; \
    vendor/bin/pest --colors=always --coverage-clover=coverage.xml \
    '

# Backend tests with mysql (requires db container)
ci-backend-mysql:
    @{{ensure_host}}
    {{compose}} up -d {{db_svc}} >/dev/null
    {{compose}} exec -T -u {{container_user}} {{api_svc}} bash -lc '\
    set -eu; \
    cd /workspaces/stack-root/apps/backend; \
    [ -f vendor/autoload.php ] || composer install --no-interaction --no-progress --prefer-dist; \
    [ -f .env ] || cp .env.example .env; \
    php artisan key:generate --force; \
    export DB_CONNECTION=mysql DB_HOST=db DB_PORT=3306 DB_DATABASE=laravel DB_USERNAME=laravel DB_PASSWORD=secret; \
    php artisan config:clear && php artisan route:clear; \
    vendor/bin/pest --colors=always --coverage-clover=coverage.xml \
    '

# Package tests (plenipotentiary-laravel)
ci-package:
    @{{compose}} exec -T -u {{container_user}} {{api_svc}} bash -lc '\
    cd /workspaces/stack-root/packages/plenipotentiary-laravel; \
    [ -f vendor/autoload.php ] || composer install --no-interaction --no-progress --prefer-dist; \
    vendor/bin/pest --colors=always \
    '

# Frontend (lint, typecheck, test, build)
ci-frontend:
    @{{ensure_host}}
    {{compose}} up -d {{fe_svc}} >/dev/null
    {{compose}} exec -T {{fe_svc}} sh -lc '\
      set -eu; \
      corepack enable; corepack prepare pnpm@10.15.1 --activate; \
      export PNPM_STORE_DIR=/pnpm-store; \
      [ -f package.json ] || { echo "[skip] no frontend"; exit 0; }; \
      if [ -f pnpm-lock.yaml ]; then pnpm install --frozen-lockfile --reporter=append-only; else pnpm install --no-frozen-lockfile --reporter=append-only; fi; \
      echo "[lint]"; pnpm lint; \
      echo "[typecheck]"; pnpm typecheck; \
      echo "[test]"; pnpm test:unit; \
      echo "[build]"; pnpm build \
    '

# ----------------------------------------------------------------------------
# Tiered bring-up and minimal smokes
# ----------------------------------------------------------------------------
tier0-up:
    @{{ensure_host}}
    {{compose}} up -d {{web_svc}} {{api_svc}} {{db_svc}} {{cache_svc}} {{mail_svc}}
    {{compose}} ps

tier0-smoke:
    @{{ensure_host}}
    set -e
    echo "[web]";   curl -i "http://localhost:${WEB_PORT:-8080}/" | head -n 1
    echo "[redis]"; {{compose}} exec -T {{cache_svc}} redis-cli PING
    echo "[mail]";  curl -s -o /dev/null -w "%{http_code}\n" http://localhost:8025/

tier0-down:
    @{{ensure_host}}
    {{compose}} stop {{web_svc}} {{api_svc}} {{db_svc}} {{cache_svc}} {{mail_svc}}

ui-up:
    @{{ensure_host}}
    {{compose}} up -d {{fe_svc}}
    {{compose}} ps {{fe_svc}}

ui-smoke:
    @{{ensure_host}}
    curl -i http://localhost:5173/ | head -n 1

ui-down:
    @{{ensure_host}}
    {{compose}} stop {{fe_svc}}

delivery-up:
    @{{ensure_host}}
    {{compose}} up -d {{horizon_svc}}
    {{compose}} ps {{horizon_svc}}

delivery-smoke:
    @{{ensure_host}}
    {{compose}} exec -T {{horizon_svc}} sh -lc 'echo ok'
    curl -sI "http://localhost:${WEB_PORT:-8080}/horizon" | head -n 1 || true

delivery-down:
    @{{ensure_host}}
    {{compose}} stop {{horizon_svc}}

monitoring-up:
    @{{ensure_host}}
    {{compose}} --profile monitoring up -d {{prom_svc}} {{graf_svc}}
    {{compose}} ps {{prom_svc}} {{graf_svc}}

monitoring-smoke:
    @{{ensure_host}}
    set -e
    echo "[prometheus]"; curl -sf http://localhost:9090/-/healthy && echo "OK"
    echo "[grafana]";    curl -sI http://localhost:3000/login | head -n 1

monitoring-down:
    @{{ensure_host}}
    {{compose}} stop {{prom_svc}} {{graf_svc}}

logging-up:
    @{{ensure_host}}
    {{compose}} --profile logging up -d {{loki_svc}}
    {{compose}} ps {{loki_svc}}

logging-smoke:
    @{{ensure_host}}
    curl -i http://localhost:3100/ready | head -n 1

logging-down:
    @{{ensure_host}}
    {{compose}} stop {{loki_svc}}

tracing-up:
    @{{ensure_host}}
    {{compose}} --profile tracing up -d {{otel_svc}}
    {{compose}} ps {{otel_svc}}

tracing-smoke:
    @{{ensure_host}}
    set -e
    echo "[health]"; curl -sI http://localhost:13133/ | head -n 1
    echo "[otlp]  "; curl -sI http://localhost:4318/  | head -n 1

tracing-down:
    @{{ensure_host}}
    {{compose}} stop {{otel_svc}}

mock-up:
    @{{ensure_host}}
    {{compose}} --profile mock up -d {{prism_svc}}
    {{compose}} ps {{prism_svc}}

mock-smoke:
    @{{ensure_host}}
    curl -i http://localhost:4010/health | head -n 1

mock-down:
    @{{ensure_host}}
    {{compose}} stop {{prism_svc}}

ssl-up:
    @{{ensure_host}}
    {{compose}} --profile ssl up -d {{caddy_svc}}
    {{compose}} ps {{caddy_svc}}

ssl-smoke:
    @{{ensure_host}}
    curl -I https://{{DOMAIN}} --resolve {{DOMAIN}}:443:127.0.0.1 -k | head -n 1

ssl-down:
    @{{ensure_host}}
    {{compose}} stop {{caddy_svc}}

# Tier 3 scale placeholders
scale-up:
    @{{ensure_host}}
    echo "TODO: bring up octane, proxysql, k6 once configured"

scale-smoke:
    @{{ensure_host}}
    echo "TODO: add octane check, mysql proxy ping, k6 dry-run"

scale-down:
    @{{ensure_host}}
    echo "TODO: stop octane, proxysql, k6"

# Bundles
tiers-up:
    @{{ensure_host}}
    just tier0-up
    just ui-up
    just delivery-up
    just monitoring-up
    just logging-up
    just tracing-up
    just mock-up
    just ssl-up

tiers-smoke:
    @{{ensure_host}}
    just tier0-smoke
    just ui-smoke
    just delivery-smoke
    just monitoring-smoke
    just logging-smoke
    just tracing-smoke
    just mock-smoke
    just ssl-smoke

tiers-down:
    @{{ensure_host}}
    just ssl-down
    just mock-down
    just tracing-down
    just logging-down
    just monitoring-down
    just delivery-down
    just ui-down
    just down

# Fully nuke everything for this project
nuke-all:
    @{{ensure_host}}
    {{compose}} -p {{PROJECT_SLUG}} down -v --remove-orphans || true
    docker rm -f $(docker ps -aq -f "label=com.docker.compose.project={{PROJECT_SLUG}}") 2>/dev/null || true
    docker network rm {{PROJECT_SLUG}}_default 2>/dev/null || true

# Open backend VS Code window
vs-code-be:
    if command -v devcontainer >/dev/null 2>&1; then \
      devcontainer up --workspace-folder apps/backend >/dev/null || true; \
    fi
    code -n apps/backend

# Open frontend VS Code window
vs-code-fe:
    if command -v devcontainer >/dev/null 2>&1; then \
      devcontainer up --workspace-folder apps/frontend >/dev/null || true; \
    fi
    code -n apps/frontend

# Open both
vs-code-both:
    just vs-code-be
    just vs-code-fe

