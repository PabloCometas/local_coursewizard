# local_coursewizard – Kursassistent

Moodle-Plugin von cometas (Moodle 4.5+, vorbereitet für 5.x). Ersetzt das Moodle-Formular «Neuer Kurs» durch einen geführten Dialog: Der Assistent fragt die Angaben Schritt für Schritt ab und legt am Schluss einen ganz normalen Moodle-Kurs an.

Stand: Grundgerüst (Version 0.1.0). Die Seite `local/coursewizard/index.php?category=<id>` zeigt erst einen Platzhalter, das Moodle-Formular wird noch nicht umgeleitet.

## Rechte

Wer den Assistenten nutzt, braucht wie beim Moodle-Formular das Recht `moodle/course:create` in der Kurskategorie.

## Grundsätze

- Baut nur auf Moodle auf (Kursformular und `create_course()`), keine Abhängigkeit zu anderen cometas-Plugins.
- Keine eigenen Datenbanktabellen, das Plugin legt nur normale Kurse an.
- Später möglich: Übernahme in `local_courseadmin`, KI-Vorschläge für einzelne Antworten.

## JavaScript bauen

`amd/src` → `amd/build` mit Moodles Grunt, lokal über `moodle-dev/js-build.sh coursewizard`. `amd/build` wird mitcommittet.
