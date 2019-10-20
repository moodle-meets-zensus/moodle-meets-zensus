<?php

require_once(dirname(__FILE__) . '/../../config.php');

block_load_class('external_feedback');

header('Content-type: text/plain');

$data = block_external_feedback::parse_input($_GET);
// data contains:
// $data['user_email'], $data['user_id'], $['course_id'], $data['user_id'], $data['course_idnumber']


if (! block_external_feedback::receive($data)) {
    http_response_code(400);
    echo "Verification failed (Error 400 - Bad Request)!\n";
    exit;
}

header('Location: ' . $CFG->wwwroot . '/course/view.php?id=' . $data['course_id']);
