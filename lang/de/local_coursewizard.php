<?php
// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * German language strings.
 *
 * @package     local_coursewizard
 * @copyright   2026 cometas
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['askdates'] = 'Kursbeginn und Kursende abfragen';
$string['askdates_desc'] = 'Der Assistent fragt nach Kursbeginn und Kursende. Sinnvoll für Kurse mit festem Semester oder Zeitraum (z. B. Hochschulen). Ist die Option aus, gelten die Standardwerte der Plattform (Website-Administration → Kurse → Standardeinstellungen).';
$string['category'] = 'Kategorie';
$string['coursename'] = 'Kursname';
$string['createnow'] = 'Kurs erstellen';
$string['decision'] = 'Danke, {$a->name}! Du hast alles Nötige angegeben und kannst deinen Kurs jetzt erstellen. Möchtest du noch weitere Angaben machen, zum Beispiel {$a->details}?';
$string['emptycourse'] = 'Leerer Kurs';
$string['eventcapacity'] = 'Plätze';
$string['eventcapacitymin'] = 'Es braucht mindestens einen Platz.';
$string['eventcreated'] = 'Dein Event «{$a}» ist erstellt. Teilnehmende können sich jetzt anmelden.';
$string['eventend'] = 'Ende';
$string['eventendbeforestart'] = 'Das Ende muss nach dem Beginn liegen.';
$string['eventstart'] = 'Beginn';
$string['explain'] = 'Wofür ist das?';
$string['explain_capacity'] = 'So viele Personen können sich für den Termin anmelden. Ist er voll, zeigt der Kurskatalog «Ausgebucht». Weitere Termine, eine Warteliste oder Kosten richtest du später direkt im Event ein.';
$string['explain_category'] = 'Kategorien ordnen die Kurse auf der Plattform, ähnlich wie Ordner. In der Kursübersicht findet man deinen Kurs unter dieser Kategorie. Oft legt sie auch fest, wer den Kurs verwalten darf. Bist du unsicher, lass den Vorschlag stehen. Du kannst den Kurs später jederzeit verschieben.';
$string['explain_dates'] = 'Der Kursbeginn legt fest, ab wann der Kurs als laufend gilt, zum Beispiel in Zeitleisten und Berichten. Nach dem Kursende zeigt Moodle ihn den Teilnehmenden unter «Vergangen». Gesperrt wird der Kurs dadurch nicht: Eingeschriebene können ihn weiterhin öffnen.';
$string['explain_eventdate'] = 'Das ist der erste Termin deines Events. Er erscheint im Kurskatalog unter «Events», und Teilnehmende können sich direkt dafür anmelden. Weitere Termine kannst du später im Event hinzufügen.';
$string['explain_fullname'] = 'Der Kursname erscheint überall, wo dein Kurs auftaucht: oben auf der Kursseite, im Dashboard und unter «Meine Kurse», in der Kursübersicht, in der Navigation und in Benachrichtigungen an die Teilnehmenden. Wähle einen kurzen, klaren Namen, unter dem man den Kurs auch in einer Liste sofort erkennt. Du kannst ihn später jederzeit ändern.';
$string['explain_image'] = 'Das Bild erscheint als Kachel im Dashboard, unter «Meine Kurse» und in der Kursübersicht. Am besten eignet sich ein Querformat im Seitenverhältnis 16:9, zum Beispiel 1200 × 675 Pixel, als JPG oder PNG. Platziere Wichtiges in der Mitte, denn je nach Ansicht werden die Ränder abgeschnitten. Text im Bild ist auf kleinen Kacheln kaum lesbar. Ohne Bild zeigt Moodle ein farbiges Muster. Erstellst du den Kurs aus einer Vorlage und wählst kein eigenes Bild, bekommt er das Bild der Vorlage.';
$string['explain_kind'] = 'Ein Kurs enthält Lerninhalte, die Teilnehmende selbst bearbeiten. Ein Event ist ein Anlass mit festem Termin, zum Beispiel ein Workshop oder ein Netzwerkanlass. Man meldet sich dafür an, statt sich in einen Kurs einzuschreiben. Events erscheinen im Kurskatalog unter «Events» und nicht unter «Meine Kurse».';
$string['explain_more'] = 'Diese Angaben hat eure Plattform als Pflicht festgelegt, damit alle Kurse einheitlich geordnet sind. Bei Fragen dazu hilft dir die Administration eurer Plattform.';
$string['explain_summary'] = 'Die Beschreibung sehen Interessierte in der Kursübersicht, bevor sie den Kurs öffnen, und bei der Selbsteinschreibung. Zwei, drei Sätze reichen: worum es geht, für wen der Kurs gedacht ist und was man danach kann.';
$string['explain_visible'] = '«Sichtbar machen» heisst: Eingeschriebene Teilnehmende sehen den Kurs und können ihn öffnen. «Verbergen» heisst: Nur du und andere Trainer/innen sehen ihn. Das ist praktisch, solange du den Kurs noch aufbaust. Du kannst die Sichtbarkeit später jederzeit ändern.';
$string['greeting'] = 'Hallo {$a}! Ich helfe dir, Schritt für Schritt einen neuen Kurs anzulegen.';
$string['imagefromtemplate'] = '(aus Vorlage)';
$string['kind'] = 'Art';
$string['kindcourse'] = 'Kurs';
$string['kindevent'] = 'Event';
$string['later'] = 'Später';
$string['moredetails'] = 'Weitere Angaben machen';
$string['optionalhint'] = 'Das Wichtigste ist erledigt. Du kannst deinen Kurs jetzt erstellen oder noch ein paar freiwillige Angaben machen.';
$string['pluginname'] = 'Kursassistent';
$string['privacy:metadata'] = 'Das Plugin Kursassistent speichert keine personenbezogenen Daten.';
$string['searchcategory'] = 'Andere Kategorie wählen …';
$string['step_capacity'] = 'Wie viele Plätze gibt es?';
$string['step_category'] = 'In welcher Kategorie soll dein Kurs erscheinen? Eine ist schon vorausgewählt. Passt sie nicht, klicke ins Feld und wähle eine andere.';
$string['step_dates'] = 'Wann beginnt dein Kurs, und wann endet er?';
$string['step_done'] = 'Danke, {$a}! Alles ist bereit. Prüfe die Angaben oben und erstelle deinen Kurs.';
$string['step_eventdate'] = 'Wann findet dein Event statt?';
$string['step_fullname'] = 'Wie soll dein neuer Kurs heissen?';
$string['step_image'] = 'Hast du ein Bild für deinen Kurs? Auch das ist freiwillig.';
$string['step_kind'] = 'Was möchtest du anlegen?';
$string['step_more'] = 'Auf dieser Plattform braucht der Kurs noch diese Angaben:';
$string['step_summary'] = 'Möchtest du deinen Kurs kurz beschreiben? Das ist freiwillig.';
$string['step_visible'] = 'Sollen Teilnehmende deinen Kurs schon sehen können?';
$string['template'] = 'Vorlage';
$string['templateapplied'] = 'Dein Kurs wurde aus der Vorlage «{$a}» erstellt.';
$string['templateimage'] = 'Das ist das Bild der Vorlage. Du kannst ein eigenes hochladen oder es übernehmen.';
$string['usetemplate'] = 'Aus einer Vorlage erstellen';
$string['usetemplateimage'] = 'Übernehmen';
$string['viewtemplate'] = 'Ansehen';
