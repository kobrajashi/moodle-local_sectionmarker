<?php
// This file is part of Moodle - http://moodle.org/
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
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Deutsche Sprachstrings für local_sectionmarker.
 *
 * @package    local_sectionmarker
 * @copyright  2025 Your Name <you@example.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// Kernstrings des Plugins.
$string['pluginname'] = 'Abschnittsmarkierung';

// Einträge im Aktivitäts-Aktionsmenü.
$string['mark_info']    = 'Markiere: Info';
$string['mark_achtung'] = 'Markiere: Hinweis';
$string['mark_ok']      = 'Markiere: Erfolgreich';
$string['remove_mark']  = 'Markierung entfernen';

// Rückmeldungen der Webservices.
$string['marker_set']     = 'Abschnittsmarkierung wurde gesetzt.';
$string['marker_removed'] = 'Abschnittsmarkierung wurde entfernt.';

// Capability-String.
$string['sectionmarker:manage'] = 'Abschnittsmarkierungen verwalten';

// Datenschutz-API-Strings.
$string['privacy:metadata']                             = 'Das Plugin Abschnittsmarkierung speichert, welche Abschnitte markiert wurden und wer die Markierungen zuletzt geändert hat.';
$string['privacy:metadata:local_sectionmarker']         = 'Abschnittsmarkierungen';
$string['privacy:metadata:local_sectionmarker:courseid']     = 'Die ID des Kurses, dem der Abschnitt gehört.';
$string['privacy:metadata:local_sectionmarker:cmid']         = 'Die ID der markierten Aktivität bzw. des Materials.';
$string['privacy:metadata:local_sectionmarker:marker']       = 'Der verwendete Markierungstyp (info, achtung, ok).';
$string['privacy:metadata:local_sectionmarker:timemodified'] = 'Der Zeitpunkt der letzten Änderung der Markierung.';
$string['privacy:metadata:local_sectionmarker:usermodified'] = 'Die ID des Nutzers, der die Markierung zuletzt geändert hat.';
