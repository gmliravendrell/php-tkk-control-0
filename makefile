# Variables
PHP = docker compose exec app
NPM = docker compose exec node
LARAVEL = laravel

# 1. Prettify del código (JS/TS + PHP con pint + Prettier)
prettify:
	@echo "🔧 Formateando código..."
	$(NPM) npx prettier --write .
	$(PHP) ./$(LARAVEL)/vendor/bin/pint

# 2. Análisis estático de calidad (SAST con Larastan y ESLint)
sast:
	@echo "🔍 Análisis estático (SAST)..."
	$(PHP) ./$(LARAVEL)/vendor/bin/phpstan analyse --memory-limit=1G $(LARAVEL)/app $(LARAVEL)/routes
	$(NPM) npx eslint . --ext .js,.jsx,.ts,.tsx

# 3. Informe de vulnerabilidades (OWASP/ZAP + npm audit + composer audit)
vuln-report:
	@echo "🛡️ Informe de vulnerabilidades..."
	# Backend (Composer)
	$(PHP) composer --working-dir=$(LARAVEL) audit || true
	# Frontend (npm/yarn)
	$(NPM) npm audit --json > npm-audit.json || true
	@echo "📊 Resultado guardado en npm-audit.json"
	# Extra: OWASP ZAP (requiere tener zaproxy instalado)
	@echo "⚡ Ejecutando OWASP ZAP en modo baseline..."
	docker run --rm -v $$(pwd):/zap/wrk/:rw -t ghcr.io/zaproxy/zaproxy:stable zap-baseline.py -t http://app:8000 -r zap-report.html || true
	@echo "📑 Informe OWASP generado en zap-report.html"
