# Section Marker (local_sectionmarker)

Local plugin that **colour-marks activities on the course page** – a quick visual hint for participants that
leaves the course content itself untouched.

Three markers are available:

| Marker | Meaning |
|---|---|
| **Info** | Note / additional information |
| **Note** | Pay attention, important |
| **Successful** | Done, passed |

## Requirements

- **Moodle 5.1 or later** (`$plugin->requires = 2025051900`). That value lies above every Moodle 5.0.x release,
  so the first release that satisfies it is Moodle 5.1.0.

## What the plugin does

In editing mode, every activity gets its own entry in the action menu to set and remove the marker. The marker
is saved through a web service; the course page does not need to be reloaded.

It is stored per course module (`local_sectionmarker`: `courseid`, `cmid`, `marker`, `timemodified`,
`usermodified`) – the activity itself is not changed.

## Capability

`local/sectionmarker:manage` – set and remove markers.

## Backup and restore

Markers are included in backups through the plugin's own backup/restore handler (`backup/moodle2/`) and mapped
to the new module ids on restore. Course copies and imports therefore keep the markers, and so does restoring a
single activity.

## Installation

From Moodle 5.1, plugins live in the `public` directory:

```bash
cp -r local_sectionmarker /path/to/moodle/public/local/sectionmarker
php admin/cli/upgrade.php
```

Alternatively, install as an administrator via *Site administration → Notifications*.

## Privacy

Privacy provider included: the plugin stores which activities have been marked and who last changed the marker.

## License

GNU GPL v3 or later – see [LICENSE](LICENSE).
