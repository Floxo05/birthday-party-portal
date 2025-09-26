## ADR 0002: Medien-Speicherung über Flysystem mit S3-kompatiblem Backend (AWS S3/minIO)

- Status: Angenommen
- Datum: 2025-08-12

### Kontext
Benutzer laden Medien (Bilder/Videos) hoch. Lokaler Storage skaliert begrenzt und erschwert Backups/Versionierung. Wir benötigen ein standardisiertes, skalierbares Objekt-Storage.

### Entscheidung
Einsatz von `league/flysystem` mit dem `aws-s3` Adapter. In der Entwicklung/On-Prem nutzen wir minIO; in der Cloud kann AWS S3 verwendet werden. Konfiguration über `config/packages/flysystem.yaml` und Standard-AWS-Umgebungsvariablen.

### Konsequenzen
- + Skalierbarkeit und Ausfallsicherheit (abhängig vom Backend)
- + Austauschbarkeit (S3-kompatible Backends)
- − Eventuelle Latenzen/Kosten bei Cloud-Storage
- − Eventual Consistency beachten; signierte URLs/ACLs ggf. erforderlich

### Alternativen
- Lokales Dateisystem: Einfach, aber schwierig bei Skalierung/Backups.
- NFS/Shared Volumes: Betrieblich aufwändiger, Locking/Performance-Themen.
- Direkter AWS SDK Einsatz: Weniger abstrahiert, Bindung an einen Anbieter.


