# local_coursewizard – Kursassistent

Moodle-Plugin von cometas (Moodle 4.5+, vorbereitet für 5.x). Ersetzt das Moodle-Formular «Neuer Kurs» durch einen geführten Dialog: Der Assistent fragt die Angaben Schritt für Schritt ab und legt am Schluss einen ganz normalen Moodle-Kurs an.

Aufruf: `local/coursewizard/index.php?category=<id>`. Das Moodle-Formular «Neuer Kurs» wird noch nicht umgeleitet.

## Ablauf

Der Dialog begrüsst die Person mit Vornamen und fragt nacheinander: Kursname, Kategorie, Start und Ende, Beschreibung, Kursbild, Sichtbarkeit. Nicht ausgefüllte Angaben behalten die Standardwerte der Plattform. Verlangt die Plattform weitere Pflichtfelder (z. B. Kursfelder), folgt ein zusätzlicher Schritt. Am Schluss legt «Neuen Kurs anlegen» den Kurs an.

Der Kurzname wird nie abgefragt: Er entsteht aus dem Kursnamen, ist er vergeben, wird « 2», « 3» … angehängt.

Technisch läuft unter dem Dialog Moodles eigenes Kursformular (`classes/form/course_form.php`), der Dialog zeigt jeweils nur die Felder eines Schritts. Prüfung und Speichern sind dieselben wie bei `course/edit.php`.

## Rechte

Wer den Assistenten nutzt, braucht wie beim Moodle-Formular das Recht `moodle/course:create` in der Kurskategorie.

## Grundsätze

- Baut nur auf Moodle auf (Kursformular und `create_course()`), keine Abhängigkeit zu anderen cometas-Plugins.
- Keine eigenen Datenbanktabellen, das Plugin legt nur normale Kurse an.
- Später möglich: Übernahme in `local_courseadmin`, KI-Vorschläge für einzelne Antworten.

## JavaScript bauen

`amd/src` → `amd/build` mit Moodles Grunt, lokal über `moodle-dev/js-build.sh coursewizard`. `amd/build` wird mitcommittet.
