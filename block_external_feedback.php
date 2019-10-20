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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.

/**
 * Form for editing external feedback block instances.
 *
 * @package block_external_feedback
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_external_feedback extends block_base
{
    /**
     * Initializes the block.
     */
    function init() {
        $this->title = get_string('pluginname', 'block_external_feedback');
    }

    /**
     * Returns configurability.
     */
    function has_config() {
        return true;
    }

    /**
     * Defines, where this block is applicable.
     */
    function applicable_formats() {
        return array(
            'site' => true,
            'course' => true,
            'my' => true
        );
    }

    function specialization() {
        $this->title = format_string(get_string('blocktitle', 'block_external_feedback'));
    }

    /**
     * get_content() builds the block, generates its content and handles all of the logic.
     * @return stdClass|stdObject
     * @throws coding_exception
     * @throws dml_exception
     * @throws moodle_exception
     */
    function get_content() {
        global $USER, $CFG;
        
        if ($this->content !== NULL) {
            return $this->content;
        }

        // Get config for block
        $config = get_config('block_external_feedback');
        
        // Get all of users' courses
        $courses = enrol_get_all_users_courses($USER->id, false, 'id, shortname', 'fullname ASC');
        
        // Remove frontpage course from courses list.
        $site = get_site();
        if (array_key_exists($site->id, $courses)) {
            unset($courses[$site->id]);
        }

        // Extract IDs of roles which can evaluate ("evaluator") and which are evaluated ("evaluatee")
        $evaluator_roleids = explode(',', $config->roleshortname_evaluator);
        $evaluatee_roleids = explode(',', $config->roleshortname_evaluatee);
        
        // Remove all courses from $courses, where current user does not have specified role from settings
        // Courses, where the user can neither evaluate nor be evaluated are not relevant
        foreach ($courses as $courseKey => &$course) {
            $roles = self::get_roles_per_course($course->id, $USER->id);
            $is_evaluator = ! empty(array_intersect($roles, $evaluator_roleids));
            $is_evaluatee = ! empty(array_intersect($roles, $evaluatee_roleids));

            // courses are removed if they have no cached evaluation or the user has no relevant role assigned
            if (! self::is_cached($course->id) || ! ($is_evaluator || $is_evaluatee)) {
                    unset($courses[$courseKey]);
            }
        }
        
        $this->content = new stdClass();
        $this->content->text = ''; // will be filled with $url and other content, e.g. greetings
        $this->content->footer = ''; // we do not use footer in this block
        
        // both flags are used to determine, whether a user is shown the greeting for an evaluator or an evalutee
        // they are set in the foreach loop below.
        $flag_is_evaluator = false;
        $flag_is_evaluatee = false;

        // set timezone for the generated links to zensus as the timestamp is provided as a parameter in the url
        // if the timestamp differs at least 2h from servertime at zensus, the user will not be allowed to access the
        // link.
        date_default_timezone_set($CFG->timezone);

        // transform $courses to block-content. $courses by now only contains courses, which fulfill the criteria:
        //     a) the user is permitted to evaluate the course or is evaluated in the course.
        //     b) an evaluation is available in the cache table
        foreach ($courses as &$c) {
            $map = array( // an array characterizing the course
                'course_id' => $c->id,
                'course_idnumber' => $c->idnumber,
                'course_fullname' => $c->fullname,
                'course_shortname' => $c->shortname,
                'user_id' => $USER->id,
                'user_username' => $USER->username,
                'user_name' => "$USER->firstname $USER->lastname",
                'roles' => self::get_roles_per_course($c->id, $USER->id),
                'user_email' => $USER->email,
                'time' => date($config->time),
                'gmtime' => gmdate($config->time),
                'encrypted_user_id' => self::encrypt_user($USER->id)
            );
            
            $is_evaluator = ! empty(array_intersect($map['roles'], explode(',', $config->roleshortname_evaluator)));
            $is_evaluatee = ! empty(array_intersect($map['roles'], explode(',', $config->roleshortname_evaluatee)));
            $response = self::get_rpc_response($map['course_id']);
            $has_completed = self::has_completed($map['user_id'], $map['course_id']);

            // Set flags for greetings of block, if a flag is set, the greeting will be displayed.
            if($is_evaluator) {
                $flag_is_evaluator = true;
            }
            if($is_evaluatee) {
                $flag_is_evaluatee = true;
            }

            switch($response->status) {
                case "preview":
                    // If user is evaluator, we show the preview, i.e. target = preview
                    if ($is_evaluator) {
                        $target = 'preview';
                        $this->content->text .= self::build_html($map, $target);
                    }
                    break; // end of case "preview"

                // Evaluation is in "run" state.
                // Evaluators should see an URL to evaluate the evaluatees, i.e. link target = questionnaire.
                // Evaluatees should just see the preview, i.e. link target = preview
                case "run":
                    // In case a user is both evaluator and evalutee for any reason, we generate no link at all as the
                    // user would be able to evaluate him or herself, hence we terminate the switch here.
                    if ($is_evaluator && $is_evaluatee) {
                        // User is both evaluator and evaluatee - wtf?
                        // We quit here.
                        break;
                    }

                    // User is evaluator and has not completed an evaluation -> show target questionnaire.
                    if ($is_evaluator && !$has_completed) {
                        $target = 'questionnaire';
                        $this->content->text .= self::build_html($map, $target);
                    }

                    // User is evaluator and has completed an evaluation -> show entry without link.
                    if ($is_evaluator && $has_completed) {
                        $this->content->text .= get_string('label', 'block_external_feedback') .
                            ' ' . get_string('prefix_eval_done', 'block_external_feedback') .
                            ' ' . $c->fullname .
                            ' [' . $c->shortname . ']' . self::get_student_quota($map['course_id']) . '<br />';
                    }

                    // User is evaluatee -> show link target = preview.
                    if ($is_evaluatee) {
                        $target = 'preview';
                        $this->content->text .= self::build_html($map, $target);
                    }
                    break; // end of case "run"

                // Evaluation is in "finished" state.
                // In general everybody should see the result if they are present and no `noresultsreason` is given.
                case "finished":
                    // TODO
                    // Currently we only support the retrieval of personalized results for events with only one lecturer
                    // A way needs to be implemented that this plugin remembers which lecturer was evaluated so that
                    // later on only the results for him or her are retrieved
                    // Then we can replace index[0] with the corresponding id.
                    $lecturerid = $response->lecturers[0]->lecturer_id;

                    // Is evaluator with completed evaluation and `course`-results exist -> show link to target `pdfdetail`.
                    // Or is evaluatee -> show link to target `pdfdetail`.
                    if ((($is_evaluator && $has_completed) || $is_evaluatee)
                        && empty($response->noresultsreason)
                        && $response->pdfresults->type === "course") {
                        $target = 'pdfdetail';
                        $this->content->text .= self::build_html($map, $target);
                    }

                    // Is evaluator with completed evaluation and `personalized`-results exist -> show link to target `pdfdetail`.
                    if ($is_evaluator
                        && $has_completed
                        && empty($noresultsreason)
                        && $response->resultstype === "personalized"
                        && !empty($lecturerid)) {
                        $target = 'pdfdetail';
                        // calc hash:
                        $hash = hash_hmac($config->hash_algo,
                            $map['course_idnumber'] .
                            $map['course_idnumber'] .
                            $target .
                            $map['time'] .
                            $lecturerid, // <-- !!
                            $config->hash_secret);
                        // build url, get teacher id due to personalized results:
                        $url = $config->url_out .
                            '&sp=' . $map['time'] .
                            '&sp=' . $hash .
                            '&sp=' . urlencode($lecturerid) . // <-- !!
                            '&sp=' . $target .
                            '&sp=' . $map['course_idnumber'] .
                            '&sp=' . $map['course_idnumber'];
                        $this->content->text .= self::build_html_with_custom_url($map, $target, $url);
                    }

                    // User could have evaluated but did not. Show punishing error message. Harhar.
                    if ($is_evaluator && !$has_completed) {
                        $this->content->text .= get_string('label', 'block_external_feedback') .
                            ' ' . get_string('prefix_pdfdetail', 'block_external_feedback') .
                            ' ' . $c->fullname . ' [' . $c->shortname . ']' .
                            self::get_student_quota($map['course_id']) . ' - '
                            . get_string('noresultsreason_notevaluated',
                                'block_external_feedback') . '<br />';
                    }

                    // User is evaluator but for some reason no results exist. Show this reason from zensus w/o url.
                    if (($is_evaluator || $is_evaluatee) && !empty($response->noresultsreason)) {
                        $this->content->text .= get_string('label', 'block_external_feedback') .
                            ' ' . get_string('prefix_pdfdetail',
                                'block_external_feedback') .
                            ' ' . $c->fullname . ' [' . $c->shortname . ']' .
                            self::get_student_quota($map['course_id']) . ' - ';
                        switch ($response->noresultsreason) {
                            case "wrong phase":
                                $this->content->text .= get_string('noresultsreason_wrongphase',
                                    'block_external_feedback');
                                break;
                            case "too few answers":
                                $this->content->text .= get_string('noresultsreason_toofewanswers',
                                    'block_external_feedback');
                                break;
                            case "not public":
                                $this->content->text .= get_string('noresultsreason_notpublic',
                                    'block_external_feedback');
                                break;
                            default:
                                $this->content->text .= get_string('noresultsreason_unknown',
                                    'block_external_feedback');
                        }
                        $this->content->text .= '<br />';
                    }

                    // Is evaluator, has completed, results are present but are personalized and no lecturerid is given.
                    // This case is simply not supported.
                    if ($is_evaluator
                        && $has_completed
                        && empty($noresultsreason)
                        && $response->resultstype === "personalized"
                        && empty($lecturerid)) {
                        $this->content->text .= get_string('label', 'block_external_feedback') .
                            ' ' . get_string('prefix_pdfdetail', 'block_external_feedback') .
                            ' ' . $c->fullname . ' [' . $c->shortname . ']' .
                            self::get_student_quota($map['course_id']) . ' - ' .
                            get_string('noresultsreason_notsupported',
                                'block_external_feedback') . '<br />';
                    }
                    break; // end of case "finished"

                // Evaluation is in "analyze" state.
                // Generally, this is used as some archive for older evaluations
                // They are ignored by this plugin
                case "analyze":
                    // do nothing
                    break;

                // unhandled cases result in a call to `debugging()`.
                default:
                    debugging("Error in block_external_feedback.php, an 
                    evaluation has an unknown status: " . $response->status);
                    break;
            } // END OF SWITCH STATEMENT
        } // END OF FOREACH LOOP

        // if at least one URL was added we show the greeting.
        // Otherwise this block is empty and will not be shown by moodle. This prevents a greeting without urls.
        if (! empty($this->content->text)) {
            $greeting = ''; // temporary var to hold user's greeting
            if($flag_is_evaluatee && !empty($config->greeting_evaluatee)) { // Show evaluatee_greeting
                $greeting .= '<div id="external_feedback_evaluatee_greeting">' . $config->greeting_evaluatee . '</div>';
                
                if($flag_is_evaluator && !empty($config->greeting_evaluator)) {
                    // append <hr> to evaluatee_greeting, since a second greeting will be shown (when not empty)
                    $greeting .= '<hr>';
                }
            }
            
            if($flag_is_evaluator){ // show greeting for evaluators (i.e. students)
                $greeting .= '<div id="external_feedback_evaluator_greeting">' . $config->greeting_evaluator . '</div>';
            }
            
            $this->content->text = '<div id="block_external_feedback"><p>' . $greeting . '</p>' . $this->content->text . '</div>';
        }
        
        return $this->content;
    }

    /**
     * Calculates the participation in the evaluation vote per course, given by the course id.
     * A string is returned, which is a concatenation of a token (language-file: `returns`) with the actual percentage.
     * e.g. "<returns> 42 %"
     * If no percentage can be calculated, if for example, nobody has voted, an empty string is returned.
     * @param $courseid A moodle course id
     * @return string Concatenation of the `returns`-string with the percentage.
     * @throws coding_exception
     * @throws dml_exception
     */
    function get_student_quota($courseid) {
        // Get global DB, config and context
        global $DB;
        $config = get_config('block_external_feedback');
        $coursecontext = context_course::instance($courseid);

        // Count all eligible evaluators, entitled to participate in the vote per course and per roleshortname.
        $evaluators_count_sql = $DB->get_records_sql('SELECT COUNT(DISTINCT userid) 
                                                          FROM mdl_role_assignments 
                                                          WHERE contextid = $1 AND roleid IN ($2)',
                                                          array($coursecontext->id, $config->roleshortname_evaluator));
        $evaluators_count_int = intval(array_values($evaluators_count_sql)[0]->count);

        // Count all evaluators, who actually participated in the vote.
        $voted_count_sql = $DB->get_records_sql('SELECT COUNT(DISTINCT userid) 
                                                     FROM mdl_block_external_feedback 
                                                     WHERE mdl_courseid = $1', array($courseid));
        $voted_count_int = intval(array_values($voted_count_sql)[0]->count);

        // If someone has voted, calculate the participation in percent
        if ($voted_count_int === 0) {
            // do not return any participation
            return '';
        } else {
            // calculate the participation and return it as a percentage without any decimals
            return ' - ' . get_string('returns', 'block_external_feedback')
                . number_format($voted_count_int / $evaluators_count_int * 100, 0) . ' %';
        }
    }

    /**
     * Generate a random string, using a cryptographically secure
     * pseudorandom number generator (random_int)
     *
     * This function uses type hints now (PHP 7+ only), but it was originally
     * written for PHP 5 as well.
     *
     * For PHP 7, random_int is a PHP core function
     * For PHP 5.x, depends on https://github.com/paragonie/random_compat
     *
     * via https://stackoverflow.com/a/31107425 - thank you, Scott!
     *
     * @param int $length How many characters do we want?
     * @param string $keyspace A string of all possible characters
     *                         to select from
     * @return string
     * @throws Exception
     */
    function random_str(
        int $length = 64,
        string $keyspace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'
        ) : string {
            if ($length < 1) {
                throw new \RangeException("Length must be a positive integer");
            }
            $pieces = [];
            $max = mb_strlen($keyspace, '8bit') - 1;
            for ($i = 0; $i < $length; ++$i) {
                $pieces []= $keyspace[random_int(0, $max)];
            }
            return implode('', $pieces);
    }

    /**
     * Encrypts a user-id in order to be sent as part of the generated link to zensus.
     * The user-id is encrypted so that the external server does not know the user's moodle id.
     * Also generates a random initialization vector for openssl if it does not exist.
     * @param $userid mdl_user userid
     * @return string encrypted user-id
     * @throws dml_exception
     */
    function encrypt_user($userid) {
        $openssl_length = 16;
        $config = get_config('external_feedback');

        // generate a random openssl initialization vector and writes it to config
        // the vector must have exactly 16 bytes or openssl will print a warning
        if(! array_key_exists("openssl-iv", $config)) {
            set_config("openssl-iv", self::random_str($openssl_length), "external_feedback");
        }

        // generates a random encryption key and writes it to config
        if(! array_key_exists("openssl-key", $config)) {
            set_config("openssl-key", self::random_str($openssl_length), "external_feedback");
        }

        // returns an encrypted userid
        return openssl_encrypt($userid,
            "aes-256-cbc",
            get_config("external_feedback", "openssl-key"),
            0,
            get_config("external_feedback", "openssl-iv"));
    }

    /**
     * Decrypts an encrypted userid returned by zensus.
     * @param $encrypted_userid , which was sent by the evaluation system.
     * @return $userid a mdl_user userid.
     * @throws dml_exception
     */
    function decrypt_user($encrypted_userid) {
        return urldecode(openssl_decrypt($encrypted_userid,
            "aes-256-cbc",
            get_config("external_feedback", "openssl-key"),
            0,
            get_config("external_feedback", "openssl-iv")));
    }

    /**
     * Calculates a hmac hash by the given map for a target.
     * @param $map
     * @param $target
     * @return string
     * @throws dml_exception
     */
    function build_hmac($map, $target) {
        $config = get_config('block_external_feedback');

        return hash_hmac($config->hash_algo,
            $map['course_idnumber'] . $map['course_idnumber'] . $target . $map['time'] . $map['encrypted_user_id'],
            $config->hash_secret);
    }

    /**
     * Builds a signed url from the map containing all information on a course.
     * @param $map
     * @param $target
     * @return string
     * @throws dml_exception
     */
    function build_url($map, $target) {
        $config = get_config('block_external_feedback');

        return $config->url_out .
            '&sp=' . $map['time'] .
            '&sp=' . self::build_hmac($map, $target) .
            '&sp=' . urlencode($map['encrypted_user_id']) . // encode userid. zensus will decode this (see manual).
            '&sp=' . $target .
            '&sp=' . $map['course_idnumber'] .
            '&sp=' . $map['course_idnumber'];
    }

    /**
     * Builds specific html from the map containing all information on a course for a given target.
     * @param $map
     * @param $target
     * @return string
     * @throws coding_exception
     * @throws dml_exception
     */
    function build_html($map, $target) {
        return '<a href="' . self::build_url($map, $target) . '" target="_blank">' .
        get_string('label', 'block_external_feedback') . ' ' . 
        get_string('prefix_' . $target, 'block_external_feedback') . ' ' . 
        $map['course_fullname'] .
        ' [' . $map['course_shortname'] . ']' .
        self::get_student_quota($map['course_id']) . '</a>' . '<br />';
    }

    /**
     * Builds specific htlm form the map containing all information on a course for a given target for a given url.
     * @param $map
     * @param $target
     * @param $url
     * @return string
     * @throws coding_exception
     * @throws dml_exception
     */
    function build_html_with_custom_url($map, $target, $url) {
        return '<a href="' . $url . '" target="_blank">' .
            get_string('label', 'block_external_feedback') . ' ' .
            get_string('prefix_' . $target, 'block_external_feedback') . ' ' .
            $map['course_fullname'] .
            ' [' . $map['course_shortname'] . ']' .
            self::get_student_quota($map['course_id']) . '</a>' . '<br />';
    }

    /**
     * The function parse_input($data) is called from `callback.php` and is passed the GET-request from the blubbsoft
     * server, when a user has evaluated. It checks the hash for validity.
     * @param $data $_GET[]-request from blubbsoft server.
     * @return array
     * @throws dml_exception
     */
    function parse_input($data) {
        global $DB;
        
        $config = get_config('block_external_feedback');
        
        $query = self::parse_string($config->url_in, $data);
        $input = array();
        parse_str($query, $input);
        
        if (empty($input['user_id']) && isset($input['user_email'])) {
            $user = $DB->get_record('user', array(
                'email' => $input['user_email']
            ));
            if (! empty($user))
                $input['user_id'] = $user->id;
        }
        
        if (empty($input['course_id']) && isset($input['course_idnumber'])) {
            $course = $DB->get_record('course', array(
                'idnumber' => $input['course_idnumber']
            ));
            if (! empty($course))
                $input['course_id'] = $course->id;
        }
        $input['hash'] = $data['check'];
        
        $hash = hash_hmac($config->hash_algo, self::parse_string($config->hash_in, $input), $config->hash_secret);
        $input['_validhash'] = $hash;
        
        $input['_verified'] = empty($hash) || empty($input['hash']) || $hash !== $input['hash'] ? false : true;
        
        return $input;
    }

    /**
     * Checks whether a user has completed an evaluation of a certain course. Returns True, if user evaluated the course
     * already and moodle has received a callback subsequently. Otherwise it returns false.
     * @param $userid
     * @param $courseid
     * @return bool
     * @throws dml_exception
     */
    function has_completed($userid, $courseid) {
        global $DB;
        $completed = $DB->get_record('block_external_feedback', array(
            'userid' => $userid,
            'mdl_courseid' => $courseid
        ));
        return ! empty($completed);
    }

    /**
     * Returns true, if the course is present in the cache table (mdl_block_external_feedback_rpc).
     * @param $courseid
     * @return bool
     * @throws dml_exception
     */
    function is_cached($courseid) {
        global $DB;
        $cached = $DB->get_record('block_external_feedback_rpc', array('mdl_courseid' => $courseid));
        return ! empty($cached);
    }

    /**
     * Returns and decodes the json from the cache table (mdl_block_external_feedback_rpc) that contains the response
     * of Zensus.
     * @param $courseid
     * @return mixed
     * @throws dml_exception
     */
    function get_rpc_response($courseid) {
        global $DB;
        return json_decode($DB->get_field('block_external_feedback_rpc', 'ext_response', array('mdl_courseid' => $courseid)));
    }
    
    /**
     * Is called by `callback.php` if the HTTP-GET-request from Blubbsoft Zensus was successfull.
     * @param $data array
     * @return bool
     * @throws dml_exception
     */
    function receive($data) {
        global $DB;
        
        if ($data['_verified'] !== true)
            return false;
        
        if (empty($data['user_id']) || empty($data['course_id']))
            return false;
        
        if (self::has_completed(self::decrypt_user($data['user_id']), $data['course_id']))
            return true;
        
        $completed = new stdClass();
        $completed->userid = self::decrypt_user($data['user_id']);
        $completed->cn_courseid = $data['course_idnumber'];
        $completed->mdl_courseid = $data['course_id'];
        $completed->timecompleted = time();
        $DB->insert_record('block_external_feedback', $completed);     
        
        return true;
    }

    /**
     * Performs some magic. Simsallabim!
     * @param $subject
     * @param $map
     * @param bool $encode
     * @return mixed
     */
    function parse_string($subject, $map, $encode = false) {
        $return = $subject;
        foreach ($map as $k => $v) {
            $return = $encode ? str_replace('{' . $k . '}', rawurlencode($v), $return) :
                str_replace('{' . $k . '}', $v, $return);
        }
        return $return;https://evaluation.hs-ruhrwest.de/zensus/app?service=pex/CampusNetLogin
    }

    /**
     * Returns all the roles of a user per course as an array.
     * @param $courseid
     * @param $userid
     * @return array
     */
    function get_roles_per_course($courseid, $userid) {
        $roles = array_values(get_user_roles(context_course::instance($courseid), $userid));
        $roles_arr = array();
        foreach($roles as $role){
            array_push($roles_arr, $role->roleid);
        }
        return $roles_arr;
    }
}
