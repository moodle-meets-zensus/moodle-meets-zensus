<?php

defined('MOODLE_INTERNAL') || die();


$tasks = array(
    array(
        'classname' => 'block_external_feedback\task\fetchrpc',
        'blocking' => 0,
        'minute' => '*',
        'hour' => '*',
        'day' => '*',
        'dayofweek' => '*',
        'month' => '*'
    )
);