# Changelog
Alle nennenswerten Änderungen an diesem Projekt werden in dieser Datei dokumentiert.

Das Format basiert auf [Keep a Changelog](https://keepachangelog.com/de/1.1.0/),
und dieses Projekt hält sich an [Semantic Versioning](https://semver.org/lang/de/spec/v2.0.0.html).

## [Unreleased]

### Geplant
- Spielesystem mit Punktesammlung
- Leaderboards
- Shop mit virtuellen und echten Belohnungen
- Vollständige Party-Detail-Ansicht mit allen Funktionen

## [0.5.2] - 2025-09-26

### Geändert
- CRSF-Token in Zusage-Form hinzugefügt für richtige Formvalidierung

## [0.5.1] - 2025-09-26

### Hinzugefügt
- Emoji-Anzeige beim aktuellen Responsestatus: „🙂“ für Zusage, „🙁“ sonst

### Geändert
- Zusage-Seite: Zahleneingabe entfernt, ein einzelner Button „Zusage + 1“
- Controller: Bei „Zusage + 1“ wird automatisch `plusGuests = 1` gesetzt
- Template `party/action_response.html.twig`: Bereinigt – nur noch drei Buttons, kein Zahleneingabefeld

## [0.5.0] - 2025-09-12

### Hinzugefügt
- Seite „Zu-/Absage verwalten“ mit Optionen: Zusage, Absage, Zusage + X
- Inline-Eingabefeld für „+X“ mit einblendbarem Info-Hinweis
- Anzeige der aktuellen Entscheidung zur Partyteilnahme

### Geändert
- Validierung für „+X“: Es sind nur die Werte 1 oder 2 erlaubt
- Auswahl nur bis zur Rückmeldefrist (`rsvpDeadline`) möglich
- Einheitlicher, neutraler (gefüllter) Button-Stil für alle Aktionen

## [0.4.0] - 2025-08-14

### Neu
- Foreshadowing für Partys: Eine Vorschau-Seite, auf die Party-Seiten umleiten, solange der Modus aktiv ist.
- In der Administration pro Party ein- und ausschaltbar (standardmäßig aktiv).

## [0.3.0] - 2025-08-12

### Hinzugefügt
- Versionsanzeige in der Anwendung
- Changelog-Dokumentation
- Verbesserte Projektqualität und Dokumentation
- Umfassende Projektdokumentation
  - Neue `docs/` Verzeichnisstruktur mit praktischen Anleitungen
  - Architektur-Übersicht und technische Details
  - Setup-Anleitungen für Docker und lokale Entwicklung
  - Umgebungsvariablen und Konfigurationsdokumentation
  - Testing & QA-Prozesse mit Skripten
  - Sicherheitsmodell und Rollenhierarchie
  - Deployment-Prozesse (Docker, CapRover)
  - Betrieb und Troubleshooting
- Architecture Decision Records (ADRs)
  - Template für zukünftige Entscheidungen
  - Dokumentation der Symfony/Doctrine-Entscheidung
  - S3/minIO Storage-Strategie
  - Rollen- und Zugriffskontrollmodell
  - Docker/CapRover Deployment-Ansatz

### Geändert
- Aktualisierte Projektstruktur und -dokumentation
- README.md erweitert um Verweise auf die neue Dokumentationsstruktur
- Dokumentation durchgängig auf Deutsch übersetzt für bessere Konsistenz

## [0.2.0] - 2025-08-10

### Hinzugefügt
- Link für "Passwort vergessen" Funktionalität
- Verbesserte Benutzerfreundlichkeit bei der Passwort-Wiederherstellung

### Geändert
- Aktualisiertes Impressum
- Kleinere UI-Verbesserungen

## [0.1.0] - 2024-08-02

### Hinzugefügt
- Backend-System mit Symfony Framework
- Benutzerverwaltung (Registrierung, Login, Rollen)
- Einfaches Benutzer-Dashboard
- Party-Verwaltung mit grundlegenden Funktionen
- Einladungssystem mit Token-basierter Authentifizierung
- Admin-Backend mit EasyAdmin Bundle
- Medien-Upload und -verwaltung
- Party-Nachrichten und -News
- Grundlegende Sicherheitsfunktionen
- Docker-Containerisierung
- Datenbank-Migrationen
- Umfangreiche Test-Suite

### Bekannt
- Party-Detail-Ansicht zeigt nur ein Bild (Entwicklung läuft)
- Weitere Funktionen sind in Planung

---

## Hinweise zur Versionsverwaltung

- **MAJOR.MINOR.PATCH**: Wir folgen dem Semantic Versioning
- **Unreleased**: Enthält alle Änderungen, die noch nicht veröffentlicht wurden
- **Geplant**: Features, die für zukünftige Versionen vorgesehen sind
- **Bekannt**: Bekannte Einschränkungen oder Work-in-Progress Features
