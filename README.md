# Section Marker (local_sectionmarker)

Lokales Plugin, das **Aktivitäten auf der Kursseite farbig markiert** – als schneller
visueller Hinweis für Teilnehmer/innen, ohne den Kursinhalt selbst zu verändern.

Drei Markierungen stehen zur Verfügung:

| Markierung | Bedeutung |
|---|---|
| **Info** | Hinweis / zusätzliche Information |
| **Achtung** | Beachten, wichtig |
| **Erfolgreich** | erledigt, bestanden |

## Was das Plugin macht

Im Bearbeitungsmodus erhält jede Aktivität im Aktionsmenü einen eigenen Eintrag zum Setzen
und Entfernen der Markierung. Die Markierung wird per Web Service gespeichert, die
Kursseite muss nicht neu geladen werden.

Gespeichert wird pro Kursmodul (`local_sectionmarker`: `courseid`, `cmid`, `marker`,
`timemodified`, `usermodified`) – die Aktivität selbst bleibt unangetastet.

## Berechtigung

`local/sectionmarker:manage` – Markierungen setzen und entfernen.

## Backup und Wiederherstellung

Markierungen werden über einen eigenen Backup-/Restore-Subplugin-Handler
(`backup/moodle2/`) mitgesichert und bei der Wiederherstellung den neuen Modul-IDs
zugeordnet. Kurskopien und Kurs-Reset behalten die Markierungen damit.

## Installation

```bash
cp -r local_sectionmarker /pfad/zu/moodle/public/local/sectionmarker
php admin/cli/upgrade.php
```

Ab Moodle 5.1 liegen Plugins im Verzeichnis `public`. Alternativ als Administrator/in
über *Website-Administration → Mitteilungen*.

## Datenschutz

Privacy-Provider vorhanden: gespeichert wird, welche Aktivitäten markiert wurden und
wer die Markierung zuletzt geändert hat.

## Lizenz

GNU GPL v3 oder später – siehe [LICENSE](LICENSE).
