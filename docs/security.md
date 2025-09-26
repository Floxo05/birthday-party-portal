## Sicherheitsmodell

### Authentifizierung
- Custom Authenticator: `App\Security\AppAuthenticator`
- Remember-Me aktiviert mit 7 Tagen Gültigkeit

### Autorisierung
- Rollenhierarchie:
  - `ROLE_ADMIN` → `ROLE_ORGANIZER` → `ROLE_USER`
- Zuweisbare Rollen: siehe `src/Security/Role.php`
- Zugriffskontrolle (Routen):
  - Öffentlich: `/login`, `/register`, `/impressum`, `/datenschutz`, `/invite`
  - Organizer+: `/admin`
  - Authentifiziert: `/`

### Fein granulare Berechtigungen
- `MediaVoter` steuert `view` auf `Media`: nur Mitglieder der jeweiligen Party dürfen sehen.

### CSRF & Sessions
- CSRF global aktiv; Sessions mit sicheren/lax Cookies konfiguriert


