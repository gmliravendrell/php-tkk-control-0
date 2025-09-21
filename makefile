# Variables
LARAVEL = laravel

# 1. Prettify del código (JS/TS con Prettier + PHP con Pint)
prettify:
	@echo "🔧 Formateando código..."
	npx prettier --write . \
		--ignore-path .prettierignore || true
	$(LARAVEL)/vendor/bin/pint

# 2. Análisis estático de calidad (SAST con PHPStan + ESLint)
sast:
	@echo "🔍 Análisis estático (SAST)..."
	$(LARAVEL)/vendor/bin/phpstan analyse --memory-limit=1G $(LARAVEL)/app $(LARAVEL)/routes
	npx eslint . --ext .js,.jsx,.ts,.tsx --ignore-path .eslintignore

# 3. Informe de vulnerabilidades (Composer audit + npm audit + OWASP ZAP)
vuln-report:
	@echo "🛡️ Informe de vulnerabilidades..."
	# Backend (Composer)
	cd $(LARAVEL) && composer audit || true
	# Frontend (npm/yarn)
	npm audit --json > npm-audit.json || true
	@echo "📊 Resultado guardado en npm-audit.json"
	# Extra: OWASP ZAP (requiere zaproxy instalado en local o via docker)
	@echo "⚡ Ejecutando OWASP ZAP baseline..."
	docker run --rm -v $$(pwd):/zap/wrk/:rw -t ghcr.io/zaproxy/zaproxy:stable zap-baseline.py -t http://localhost:8000 -r zap-report.html || true
	@echo "📑 Informe OWASP generado en zap-report.html"
