# Snake Assets

Lege hier optionale Bilder ab, um Schlange und Früchte zu skinnen. Wenn keine Bilder vorhanden sind, wird wie bisher vektor‑basiert gezeichnet.

Unterstützte Dateinamen (alle optional):
- `snake-head-1.png` – Kopf der Schlange (Standardausrichtung NACH LINKS). Wird je nach Richtung gespiegelt/rotiert. Standard‑Skin bis 9 Punkte.
- `snake-head-2.png` – Alternativ‑Kopf (wird ab Score ≥ 10 automatisch verwendet). Gleiche Ausrichtung/Rotation wie oben.
- `snake-body.png` – Körper‑Segment der Schlange, nicht rotiert (quadratisch entwerfen)
- `fruit.png` – Frucht/Snack

Format‑Empfehlungen:
- Transparenter PNG oder WebP (PNG ist am einfachsten)
- Quadratische Grafiken, damit die Skalierung ins Raster passt
- Empfohlene Pixelgröße: 64×64 px
  - Alternativ 32×32 px (klassisches Pixel‑Art)
  - 128×128 px funktioniert ebenfalls; wird sauber herunterskaliert
- Hintergrund transparent lassen, damit das Spielfeld sichtbar bleibt

Wie groß sind die Kacheln im Spiel?
- Das Spiel ist responsiv. Eine Zelle (`CELL`) liegt je nach Bildschirm und Pixel‑Dichte ungefähr zwischen 18 und 56 Gerätepixeln.
- Deshalb funktionieren 32–64 px gut. 64 px ist ein sehr robuster Sweet‑Spot für scharfe Darstellung auf HiDPI‑Displays.

Ablage:
- Dieser Ordner ist relativ zu `Snake.html` und `snake.js`.
- Öffne `Snake.html` einfach im Browser. Wenn die Dateien vorhanden sind, werden sie automatisch verwendet.

Fallback & Verhalten:
- Fehlt eine Datei, wird automatisch die eingebaute Vektor‑Grafik genutzt (keine Fehlermeldung, normales Spiel).
- `snake-head.png` Basis ist NACH LINKS ausgerichtet. Ausrichtung im Spiel:
  - rechts: horizontal gespiegelt (keine Rotation)
  - oben: +90° Rotation
  - unten: −90° Rotation
- `snake-body.png` wird pro Segment ohne Rotation gezeichnet.
- `fruit.png` wird pro Zelle mittig skaliert.

Tipp für Pixel‑Art:
- Das Spiel zeichnet Bilder ohne Kantenglättung (`imageSmoothingEnabled = false`).
- Erzeuge die Assets in der Zielauflösung (z. B. 32×32 oder 64×64), um crispes Pixel‑Art zu erhalten.


## Mobile-optimiertes Raster & Steuerung der Zellanzahl

Das Grid ist nun dynamisch und passt sich der verfügbaren Fläche an. Auf Smartphones werden automatisch weniger, dafür größere Zellen verwendet, damit das Spielfeld auf jedem Viewport gut und vollständig sichtbar ist.

- Ziel-Zellgröße: ca. 44–56 px (Phone) bzw. ~44 px (Desktop)
- Dynamischer Bereich der Gridgröße: 8×8 bis 16×16
- Das Canvas wird auf die nächstpassende Zellgröße zugeschnitten, damit das Raster „crisp“ ohne Restpixel bleibt.

Optional kannst du die Gridgröße erzwingen (z. B. zum Testen):

- `?cells=12` → erzwingt ein 12×12-Raster (wird auf 8–24 geklammert, in der UI aktuell auf 8–16 genutzt)

Hinweis: Die HTML `<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover"/>` und CSS `svh`/Safe‑Area Inset sorgen dafür, dass das Board auf Geräten mit Notch/Home‑Indicator optimal platziert wird.

## Standardeinstellungen

- Standard-Größe des Grids ist jetzt 12×12. Über den Query-Parameter `?cells=NUM` kannst du dies weiterhin überschreiben (z. B. `?cells=10`).


## Spielregeln-Update

- Ab Highscore/Score ≥ 10 werden gleichzeitig 2 Früchte gespawnt. Solange du ≥ 10 Punkte hast, bleibt die Anzahl der Früchte auf 2; darunter ist es 1 Frucht.
