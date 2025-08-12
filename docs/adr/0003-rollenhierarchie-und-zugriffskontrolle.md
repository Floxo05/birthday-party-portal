## ADR 0003: Rollenhierarchie und Zugriffskontrolle

- Status: Angenommen
- Datum: 2025-08-12

### Kontext
Wir benötigen ein einfaches, nachvollziehbares Berechtigungssystem für Benutzer, Organisatoren und Administratoren. Zusätzlich sollen Medien nur von Mitgliedern der jeweiligen Party gesehen werden können.

### Entscheidung
Etablierung einer Rollenhierarchie `ROLE_ADMIN` → `ROLE_ORGANIZER` → `ROLE_USER` in `config/packages/security.yaml`. Zuweisbare Rollen sind in `src/Security/Role.php` definiert. Für feinere Objektberechtigungen kommt ein `MediaVoter` zum Einsatz, der Sichtbarkeit auf Party-Mitgliedschaft prüft.

### Konsequenzen
- + Klare, wartbare Rechtevergabe über Rollen
- + Objektbasierte Kontrolle für Medien-Zugriff
- − Feingranulare Sonderfälle erfordern zusätzliche Voter/Regeln

### Alternativen
- Attributbasierte Zugriffskontrolle (ABAC): Flexibler, aber komplexer in Modellierung/Transparenz.
- Rechte pro Entität/Benutzer: Sehr granular, aber hoher Verwaltungsaufwand.


