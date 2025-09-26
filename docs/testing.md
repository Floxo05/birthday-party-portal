## Tests & QA

### Test-Suites
- Unit-Tests: schnell, isoliert (`tests/Unit`)
- Integrations-Tests: Services/DB (`tests/Integration`)
- Feature-Tests: übergreifende Flows (`tests/Feature`)

### Kommandos
- Alle QA-Schritte:
  - `bash qa/check.sh`
- Einzelne Schritte:
  - Composer Audit: `bash qa/steps/audit.sh`
  - PHPStan: `bash qa/steps/phpstan.sh`
  - Unit Coverage (HTML unter `qa/reports/code-coverage`): `bash qa/steps/coverage-unit.sh`
  - Integration: `bash qa/steps/coverage-integration.sh`
  - Feature: `bash qa/steps/coverage-feature.sh`

Feature-Tests setzen die Datenbank automatisch zurück; die Coverage-Schwelle ist im Skript konfigurierbar.


