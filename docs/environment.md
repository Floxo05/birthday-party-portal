## Umgebung & Konfiguration

Konfiguration via `.env.local`, Container-Umgebungsvariablen oder Secret-Manager.

### Erforderlich
- `APP_SECRET`: Symfony-Secret
- `DATABASE_URL`: MySQL-Verbindungsstring
- `MAILER_DSN`: SMTP-DSN
- `MEDIA_BUCKET`: Name des S3-Buckets

### S3 / minIO Client
AWS SDK via Standard-Umgebungsvariablen für `Aws\S3\S3Client` konfigurieren:
- `AWS_ACCESS_KEY_ID`
- `AWS_SECRET_ACCESS_KEY`
- `AWS_DEFAULT_REGION` (z. B. `eu-central-1`)
- `AWS_ENDPOINT` (für minIO: z. B. `http://minio:9000`)
- `AWS_S3_FORCE_PATH_STYLE=true` für minIO

`config/packages/flysystem.yaml` konfiguriert den `aws`-Adapter und den Bucket.

### Sicherheit
- Rollenhierarchie in `config/packages/security.yaml`
- Zuweisbare Rollen in `src/Security/Role.php`

### Caching und Sessions
- Sessions aktiviert, Cookies sicher/lax; CSRF-Schutz aktiv


