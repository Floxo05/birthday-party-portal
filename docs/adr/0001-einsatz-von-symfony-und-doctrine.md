## ADR 0001: Einsatz von Symfony 7.3, Doctrine ORM 3, Twig und EasyAdmin

- Status: Angenommen
- Datum: 2025-08-12

### Kontext
Wir benötigen ein stabiles, gut wartbares PHP-Framework mit breitem Ökosystem, klaren Konventionen und solider Security- und Tooling-Unterstützung. Die Anwendung umfasst klassische Web-Features (Auth, Formulare, Templating), Admin-Oberflächen und persistente Datenmodelle.

### Entscheidung
Wir setzen Symfony 7.3 mit Doctrine ORM 3, Twig als Template-Engine und EasyAdmin für das Admin-Backend ein.

### Konsequenzen
- + Hohe Wartbarkeit und klare Struktur (Bundles, Config, DI)
- + Breites Ökosystem und Dokumentation
- + Doctrine: mächtige Query-Fähigkeiten, Migrations, Unit of Work
- − Lernkurve für Einsteiger höher als bei Microframeworks
- − Regelmäßige Updates (Major/Minor) erforderlich

### Alternativen
- Laravel: Sehr produktiv, aber andere Konventionen/Ökosystem; Symfony bietet uns mehr Granularität in Komponentenwahl.
- Slim/Laminas: Leichter, aber mehr Eigenbau für Security/ORM/Admin nötig.
- Eigene Implementierung: Hoher Aufwand, Sicherheits- und Wartungsrisiken.


