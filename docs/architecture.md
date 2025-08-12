## Architektur

### Überblick (High-level)
- Symfony 7.3 (PHP 8.3), Doctrine ORM 3, Twig, EasyAdmin für das Admin-UI
- Frontend-Assets via Webpack Encore (Node 20) und Asset Mapper
- Storage: Medien via Flysystem nach S3-kompatiblem Storage (minIO/AWS S3)
- Authentifizierung: Custom Authenticator, Rollenhierarchie (`USER`, `ORGANIZER`, `ADMIN`)
- Tests: PHPUnit (Unit, Integration, Feature) mit Hilfsskripten unter `qa/`

### Hauptmodule
- Einladungen
  - `InvitationManager` erzeugt Token und validiert Ablaufzeit und Nutzungen
  - `InvitationHandler` orchestriert Validierung und Party-Mitgliedschaft
  - Link-Generierung: `InvitationLinkGenerator`
- Medien
  - `MediaUploader` bildet Storage-Pfad, speichert via `MediaStorage`, persistiert `Media`
  - Zugriff ist durch `MediaVoter` geschützt (nur Party-Mitglieder)
- Party & Mitgliedschaften
  - Factory/Services zum Erstellen von Parties und Verwalten der Rollen
- Admin-Oberfläche
  - EasyAdmin-Controller unter `src/Controller/Admin` mit eigenem Layout

### Datenmodell (Überblick)
- `User` ⇄ `PartyMember` ⇄ `Party`
- `Invitation` (token, expiresAt, uses, maxUses, role) ist einer `Party` zugeordnet
- `Media` ist `Party` und Besitzer-`User` zugeordnet
- `PartyNews` für News pro Party

Ein Klassendiagramm liegt unter `planning/UML/Classdiagram/overview.plantuml`.

### Request-Flows (Beispiele)
- Einladung öffnen → `InvitationController` → `InvitationHandler::handleInvitation()`
- Medien hochladen → `MediaController` → `MediaUploader::upload()` → Flysystem S3

### Relevante Konfigurationen
- Doctrine: `config/packages/doctrine.yaml`
- Sicherheit: `config/packages/security.yaml`, Rollen in `src/Security/Role.php`
- Flysystem/S3: `config/packages/flysystem.yaml`
- Mailer: `config/packages/mailer.yaml`
- Asset Mapper + Encore: `config/packages/asset_mapper.yaml`, `webpack.config.js`


