<?php

// This file will be executed by the moodle-cron and contains a scheduled task to keep the cache of available
// evaluations up to date.

namespace block_external_feedback\task;

class fetchrpc extends \core\task\scheduled_task {

    /**
     * @return string name shown in administrator's menu
     */
    public function get_name() {
        return 'External Feedback RPC Fetcher';
    }

    /**
     * Executes a scheduled task, which queries the zensus server with an xml-rpc request, containing all
     * CampusNet-IDs for all Moodle-courses..
     * The zensus server answers, whether it has an evaluation ready for
     * each CampusNet-course_id. The result is then stored in the tmp-table, which functions as a cache.
     *
     * {@inheritdoc}
     * @see \core\task\task_base::execute()
     */
    public function execute() {
        global $DB;
        // get config of this plugin
        $config = get_config('block_external_feedback');
        $table = 'block_external_feedback_rpc';

        echo "Starting scheduled task to update external feedback via RPC ..." . PHP_EOL;

        if($config->scheduled_task_verbose) {
            echo "RPC for course:\tcourseid\tidnumber\tshortname\tfullname\t->\tresponse" . PHP_EOL;
        }

        $courses = get_courses();
        foreach ($courses as &$c) {
            if ($c->id > 1) { // ignore frontpage-course
                $response = self::perform_request($c->idnumber);

                if($config->scheduled_task_verbose) {
                    echo "RPC for course:\t" . $c->id . "\t" . $c->idnumber . "\t" . $c->shortname . "\t" .
                        $c->fullname . "\t->\t" . json_encode($response) . PHP_EOL;
                }

                // if record exists, update it else create a new one
                if($DB->record_exists($table, array('mdl_courseid' => $c->id))) {
                    $DB->set_field($table, 'ext_response', json_encode($response), array('mdl_courseid' => $c->id));
                } else {
                    $obj = (object) ['mdl_courseid' => $c->id, 'ext_response' => json_encode($response)];
                    $DB->insert_record($table, $obj);
                }
            }
        }
        
        echo '... finished.' . PHP_EOL;
    }


    /**
     * Connects to the external evaluation service and fetches a response.
     * @param $external_id
     * @return mixed
     * @throws \dml_exception
     */
    private function perform_request($external_id) {
        global $CFG;

        // get config
        $config = get_config('block_external_feedback');
        
        // process parameters
        date_default_timezone_set($CFG->timezone);
        $tstamp = date($config->time); // gmdate('Y-m-d-h-m');
        $hash = hash_hmac($config->hash_algo, $external_id . $tstamp, $config->hash_secret);
        
        // encode parameters and form request
        $params = array(
                (string) $external_id,
                (string) $config->zensus_origintype,
                (string) $tstamp,
                (string) $hash
            );
        
        $request = xmlrpc_encode_request($config->zensus_service, $params);
        
        // create the stream context for the request
        $context = stream_context_create(array(
            'http' => array(
                'method' => "POST",
                'header' => "Content-Type: text/xml\r\nUser-Agent: PHPRPC/1.0\r\n",
                'content' => $request
            )
        ));
        
        // URL of the XMLRPC Server
        $file = file_get_contents($config->zensus_remote, false, $context);
        
        if (! $file) { // server could not be reached
            throw new Exception('External feedback server did not return any data (might be connection error).
                                 Aborting scheduled task.');
        }
        
        // decode the XMLRPC response
        return xmlrpc_decode($file);
    }
}


/* EXAMPLE DUMP OF DECODED RESPONSE OBJECT FROM ZENSUS SERVER. IS STORED AS JSON IN THE DATABASE OF MOODLE.
 * Table: mdl_block_external_feedback_rpc
 * 
 * array(15) {
 * ["noquestionnairereason"]=>
 * string(10) "no user_id"
 * ["pdfdetailfreetexts"]=>
 * array(2) {
 *   ["content"]=>
 *   array(1) {
 *     [0]=>
 *     array(3) {
 *       ["notavailablereason"]=>
 *       string(15) "too few answers"
 *       ["lecturer_id"]=>
 *       string(15) "358915339695216"
 *       ["available"]=>
 *       bool(false)
 *     }
 *   }
 *   ["type"]=>
 *   string(6) "course"
 * }
 * ["status"]=>
 * string(3) "run"
 * ["pdfresults"]=>
 * array(2) {
 *   ["content"]=>
 *   array(1) {
 *     [0]=>
 *     array(3) {
 *       ["notavailablereason"]=>
 *       string(15) "too few answers"
 *       ["lecturer_id"]=>
 *       string(15) "358915339695216"
 *       ["available"]=>
 *       bool(false)
 *     }
 *   }
 *   ["type"]=>
 *   string(6) "course"
 * }
 * ["nopreviewreason"]=>
 * string(11) "wrong phase"
 * ["noresultsreason"]=>
 * string(15) "too few answers"
 * ["pdfdetail"]=>
 * array(2) {
 *   ["content"]=>
 *   array(1) {
 *     [0]=>
 *     array(3) {
 *       ["notavailablereason"]=>
 *       string(15) "too few answers"
 *       ["lecturer_id"]=>
 *       string(15) "358915339695216"
 *       ["available"]=>
 *       bool(false)
 *     }
 *   }
 *   ["type"]=>
 *   string(6) "course"
 * }
 * ["numvotes_details"]=>
 * array(3) {
 *   ["paper_complete"]=>
 *   int(0)
 *   ["online_incomplete"]=>
 *   int(0)
 *   ["online_complete"]=>
 *   int(0)
 * }
 * ["questionnaire"]=>
 * bool(false)
 * ["results"]=>
 * bool(false)
 * ["preview"]=>
 * bool(false)
 * ["lecturers"]=>
 * array(1) {
 *   [0]=>
 *   array(6) {
 *     ["lecturer_id"]=>
 *     string(15) "358915339695216"
 *     ["numvotes_details"]=>
 *     array(3) {
 *       ["paper_complete"]=>
 *       int(0)
 *       ["online_incomplete"]=>
 *       int(0)
 *       ["online_complete"]=>
 *       int(0)
 *     }
 *     ["title"]=>
 *     string(0) ""
 *     ["lastname"]=>
 *     string(9) "<LASTNAME>"
 *     ["firstname"]=>
 *     string(5) "<FIRSTNAME>"
 *     ["numvotes"]=>
 *     int(0)
 *   }
 * }
 * ["pdfquestionnaire"]=>
 * bool(false)
 * ["nopdfquestionnairereason"]=>
 * string(10) "not public"
 * ["numvotes"]=>
 * int(0)
 * }
 */
