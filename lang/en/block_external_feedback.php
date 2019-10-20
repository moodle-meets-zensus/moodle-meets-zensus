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

$string['external_feedback:addinstance']    = 'Add a new External Feedback block';
$string['external_feedback:myaddinstance']  = 'Add a new External Feedback block to My home';

$string['label']                            = '&bull;';
$string['blocktitle']                       = 'Evaluation';
$string['pluginname']                       = 'External Feedback';

$string['url_out']                          = 'External URL template';
$string['url_out_help']                     = '';
$string['hash_out']                         = 'External hash template';
$string['hash_out_help']                    = '';

$string['greeting_evaluator']               = 'Greeting for Evaluators';
$string['greeting_evaluator_help']          = 'Greet the evaluators (i.e. students) with a custom message.';

$string['greeting_evaluatee']               = 'Greeting for Evaluatees';
$string['greeting_evaluatee_help']          = 'Greet the evaluatees (i.e. teachers) with a custom message.';

$string['url_in']                           = 'Internal URL template';
$string['url_in_help']                      = '';
$string['hash_in']                          = 'Internal hash template';
$string['hash_in_help']                     = '';

$string['zensus_remote']                    = 'Zensus Remote URL';
$string['zensus_remote_help']               = '';

$string['zensus_service']                   = 'Zensus Service';
$string['zensus_service_help']              = '';

$string['zensus_origintype']                = 'Zensus Origin Type';
$string['zensus_origintype_help']           = '';

$string['external_feedback:viewadmin']      = 'Change administrative settings';

$string['returns']                          = 'Response rate: ';

$string['prefix_questionnaire']             = 'Evaluate now:';
$string['prefix_results']                   = 'View results:';
$string['prefix_pdfdetail']                 = 'View results (PDF):';
$string['prefix_preview']                   = 'Preview for lecturers:';
$string['prefix_eval_done']                 = 'Already evaluated:';

$string['roleshortname_evaluator']          = 'Evaluators';
$string['roleshortname_evaluator_help']     = 'Roles which are allowed to evaluate a course.';

$string['roleshortname_evaluatee']          = 'Evaluatees';
$string['roleshortname_evaluatee_help']     = 'Roles which are allowed to see the preparation of an evaluation.';

$string['hash_algo']                        = 'Hashing algorithm';
$string['hash_algo_help']                   = '<a href="http://php.net/manual/en/function.hash-hmac.php" target="_blank">HMAC algorithm</a> to be used';
$string['hash_secret']                      = 'Hashing secret';
$string['hash_secret_help']                 = 'Secret shared with external evaluation system';
$string['time']                             = 'Timestamp template';
$string['time_help']                        = '<a href="http://de.php.net/manual/de/function.date.php" target="_blank">date() format</a> used for constructing {time} and {gmtime} placeholders';

$string['noresultsreason_wrongphase']       = '<i>Reason for unavailability: Wrong phase.</i>';
$string['noresultsreason_toofewanswers']    = '<i>Reason for unavailability: Too few answers.</i>';
$string['noresultsreason_notpublic']        = '<i>Reason for unavailability: Not public.</i>';
$string['noresultsreason_unknown']          = '<i>Reason for unavailability: Unknown.</i>';
$string['noresultsreason_notevaluated']     = '<i>Reason for unavailability: You did not evaluate.</i>';
$string['noresultsreason_notsupported']     = '<i>Reason for unavailability: No automatic retrieval possiblef.</i>';

$string['scheduled_task_verbose']           = 'Scheduled Task Verbose Output';
$string['scheduled_task_verbose_desc']      = 'Enables a verbose output for the scheduled task, as it will print the received data objects in the JSON format to the logfile.';