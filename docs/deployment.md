## Deployment

### Docker Image
- Multi-Stage Build (`Dockerfile`):
  - Stage 1: Node 20 baut Assets mit Encore
  - Stage 2: PHP 8.3 Apache; installiert Composer-Dependencies, übernimmt gebaute Assets
- Start-Skript `start.sh` führt Migrationen aus, leert/wärmt Cache, kompiliert Asset Map

Build & Run:
```bash
docker build -t birthday-portal .
docker run --env-file .env.production -p 80:80 birthday-portal
```

### CapRover
- `captain-definition` verweist auf den `Dockerfile`
- Env Vars und Volumes in den CapRover-App-Einstellungen konfigurieren

### Datenbank-Migrationen
Beim Start via `start.sh` oder manuell:
```bash
php bin/console doctrine:migrations:migrate --no-interaction
```

### Assets
- Während des Image-Builds erzeugt; zur Laufzeit wird zusätzlich die Asset Map kompiliert (Asset Mapper)

### Storage
- S3/minIO-Bucket über `MEDIA_BUCKET`
- Korrekte AWS-Credentials/Endpoint in der Umgebung sicherstellen


