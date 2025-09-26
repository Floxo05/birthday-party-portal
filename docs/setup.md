## Setup

### Voraussetzungen
- Docker und Docker Compose (empfohlen) oder PHP 8.3, Composer, Node 20, MySQL
- Für S3-Storage: Zugang zu AWS S3 oder minIO

### Schnellstart (Docker)
1. Umgebung bereitstellen: `.env.local` anlegen oder Env Vars setzen; siehe [Umgebung](./environment.md)
2. Build und Start:
   - `docker build -t birthday-portal .`
   - `docker run --env-file .env.local -p 8080:80 birthday-portal`
3. App ist erreichbar unter `http://localhost:8080`

Beim ersten Start führt `start.sh` Migrationen aus, leert/wärmt den Cache und kompiliert die Asset Map.

### Lokale Entwicklung (ohne Docker)
1. PHP-Abhängigkeiten: `composer install`
2. Node-Abhängigkeiten: `npm ci` und `npm run dev` (oder `npm run build`)
3. Datenbank konfigurieren (`DATABASE_URL`) und ausführen:
   - `php bin/console doctrine:database:create`
   - `php bin/console doctrine:migrations:migrate --no-interaction`
4. Server starten: `symfony serve -d` oder `php -S localhost:8000 -t public`

### Assets
- Encore erzeugt nach `public/build/`
- Asset Mapper mapped `assets/` gemäß `asset_mapper.yaml`


