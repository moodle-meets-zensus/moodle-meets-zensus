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
 * Strings for component 'block_external_feedback', language 'en', branch 'MOODLE_20_STABLE'
 *
 * @package   block_external_feedback
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['external_feedback:addinstance']    = 'Füge eine neue Instanz vom "External Feedback" block hinzu';
$string['external_feedback:myaddinstance']  = 'Füge eine neue Instanz vom "External Feedback" block zum Dashboard hinzu';

$string['label']                            = '&bull;';
$string['blocktitle']                       = 'Evaluation';
$string['pluginname']                       = 'External Feedback';

$string['url_out']                          = 'External Feedback URL Vorlage';
$string['url_out_help']                     = '';
$string['hash_out']                         = 'External Feedback Hash Vorlage';
$string['hash_out_help']                    = '';

$string['greeting_evaluator']               = 'Begrüßung für Evaluatoren';
$string['greeting_evaluator_help']          = 'Begrüßt die Evaluatoren (z.B. Teilnehmer/innen) mit einer Nachricht';

$string['greeting_evaluatee']               = 'Begrüßung für Evaluierte';
$string['greeting_evaluatee_help']          = 'Begrüßt die Evaluierten (z.B. Trainer/innen) mit einer Nachricht';

$string['url_in']                           = 'Interne URL Vorlage';
$string['url_in_help']                      = '';
$string['hash_in']                          = 'Interne Hsah Vorlage';
$string['hash_in_help']                     = '';

$string['zensus_remote']                    = 'Zensus Remote URL';
$string['zensus_remote_help']               = '';

$string['zensus_service']                   = 'Zensus Service';
$string['zensus_service_help']              = '';

$string['zensus_origintype']                = 'Zensus Origin Type';
$string['zensus_origintype_help']           = '';

$string['external_feedback:viewadmin']      = 'Administrative Einstellungen des Blocks verändern';

$string['returns']                          = 'Rücklauf: ';

$string['prefix_questionnaire']             = 'Jetzt evaluieren:';
$string['prefix_results']                   = 'Ergebnisse ansehen:';
$string['prefix_pdfdetail']                 = 'Ergebnisse ansehen (PDF):';
$string['prefix_preview']                   = 'Vorschau für Lehrende:';
$string['prefix_eval_done']                 = 'Bereits evaluiert:';


$string['roleshortname_evaluator']          = 'Evaluatoren';
$string['roleshortname_evaluator_help']     = 'Rollen, welche in einem Kurs evaluieren dürfen.';

$string['roleshortname_evaluatee']          = 'Evaluiertes';
$string['roleshortname_evaluatee_help']     = 'Rollen, die in einem Kurs evaluiert werden.';

$string['hash_algo']                        = 'Hash algorithmus';
$string['hash_algo_help']                   = 'benutzter <a href="http://php.net/manual/en/function.hash-hmac.php" target="_blank">HMAC Algorithmus</a>';
$string['hash_secret']                      = 'Hash Geheimnis';
$string['hash_secret_help']                 = 'Geheimnis, das mit dem Evaluationssystem geteilt wird.';
$string['time']                             = 'Zeitstempel Vorlage';
$string['time_help']                        = '<a href="http://de.php.net/manual/de/function.date.php" target="_blank">date() format</a> used for constructing {time} and {gmtime} placeholders';

$string['noresultsreason_wrongphase']       = '<i>Grund für Nichtverfügbarkeit: Falsche Phase.</i>';
$string['noresultsreason_toofewanswers']    = '<i>Grund für Nichtverfügbarkeit: Zu wenig abgegebene Evaluationen.</i>';
$string['noresultsreason_notpublic']        = '<i>Grund für Nichtverfügbarkeit: Nicht öffentlich.</i>';
$string['noresultsreason_unknown']          = '<i>Grund für Nichtverfügbarkeit: Unbekannt. Bitte melden Sie dies dem IT-Service.</i>';
$string['noresultsreason_notevaluated']     = '<i>Grund für Nichtverfügbarkeit: Sie haben leider nicht evaluiert.</i>';
$string['noresultsreason_notsupported']     = '<i>Grund für Nichtverfügbarkeit: Der automatische Abruf unterstützt diese Veranstaltung leider nicht.</i>';

$string['scheduled_task_verbose']           = 'Detaillierte Ausgabe der geplanten Ausführung';
$string['scheduled_task_verbose_desc']      = 'Druckt die Rückgaben der RPC-Aufrufe durch die geplante Ausführung als JSON-Objekt in das Log.';