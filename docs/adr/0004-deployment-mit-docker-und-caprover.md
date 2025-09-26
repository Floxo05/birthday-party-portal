## ADR 0004: Deployment mit Docker (Multi-Stage) und CapRover

- Status: Angenommen
- Datum: 2025-08-12

### Kontext
Wir möchten reproduzierbare Builds, einfache Portabilität und ein unkompliziertes Hosting. CapRover bietet PaaS-ähnliche Features für Docker-Workloads mit geringer Komplexität.

### Entscheidung
Erstellung eines Multi-Stage Docker Images (Assets builden, anschließend PHP 8.3-Apache Stage). CapRover nutzt `captain-definition`, um den Build/Deploy-Prozess auszulösen. Start erfolgt über `start.sh` (Migrationen, Cache, Asset Map).

### Konsequenzen
- + Reproduzierbare Deployments, klare Trennung von Build/Run
- + Einfache Verwaltung auf CapRover-Instanzen
- − CapRover-spezifische Konfigurationen
- − Alternative Plattformen benötigen ggf. Anpassungen

### Alternativen
- Kubernetes: Sehr flexibel, aber für dieses Projekt überdimensioniert.
- Docker Compose: Für Prod-Setup möglich, aber weniger Self-Service/Automatisierung.
- Platform.sh/Heroku: Managed, aber Kosten/Lock-in.


