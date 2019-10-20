<?php

defined('MOODLE_INTERNAL') || die;

$hash_algos = array_combine(hash_algos(), hash_algos());

$capabilities = [
    'block/external_feedback:viewadmin',
];
if ($hassiteconfig || has_any_capability($capabilities, context_system::instance())){

    $settings->add(new admin_setting_configtext('block_external_feedback/url_out', get_string('url_out', 'block_external_feedback'),
        get_string('url_out_help', 'block_external_feedback'), 'https://example.org/zensus/app?service=pex/CampusNetLogin', PARAM_TEXT));

    $settings->add(new admin_setting_configtext('block_external_feedback/hash_out', get_string('hash_out', 'block_external_feedback'),
        get_string('hash_out_help', 'block_external_feedback'), '{course_idnumber}{user_id}{gmtime}', PARAM_TEXT));

    $settings->add(new admin_setting_configtext('block_external_feedback/url_in', get_string('url_in', 'block_external_feedback'),
        get_string('url_in_help', 'block_external_feedback'), 'course_idnumber={course}&user_id={user}&hash={hash}', PARAM_TEXT));

    $settings->add(new admin_setting_configtext('block_external_feedback/hash_in', get_string('hash_in', 'block_external_feedback'),
        get_string('hash_in_help', 'block_external_feedback'), '{course_idnumber}{user_id}', PARAM_TEXT));

    $settings->add(new admin_setting_configselect('block_external_feedback/hash_algo', get_string('hash_algo', 'block_external_feedback'),
        get_string('hash_algo_help', 'block_external_feedback'), 'md5', $hash_algos));

    $settings->add(new admin_setting_configpasswordunmask('block_external_feedback/hash_secret', get_string('hash_secret', 'block_external_feedback'),
        get_string('hash_secret_help', 'block_external_feedback'), '', PARAM_TEXT));

    $settings->add(new admin_setting_configtext('block_external_feedback/zensus_remote', get_string('zensus_remote', 'block_external_feedback'),
        get_string('zensus_remote_help', 'block_external_feedback'), 'http://example.org.de/zensus/remote', PARAM_TEXT));

    $settings->add(new admin_setting_configtext('block_external_feedback/zensus_service', get_string('zensus_service', 'block_external_feedback'),
        get_string('zensus_service_help', 'block_external_feedback'), 'info.course_status_v3', PARAM_TEXT));

    $settings->add(new admin_setting_configtext('block_external_feedback/zensus_origintype', get_string('zensus_origintype', 'block_external_feedback'),
        get_string('zensus_origintype_help', 'block_external_feedback'), 'CampusNet', PARAM_TEXT));

    $settings->add(new admin_setting_configtext('block_external_feedback/time', get_string('time', 'block_external_feedback'),
        get_string('time_help', 'block_external_feedback'), 'Y-m-d-H-i', PARAM_TEXT));

    $settings->add(new admin_setting_confightmleditor('block_external_feedback/greeting_evaluatee', get_string('greeting_evaluatee', 'block_external_feedback'),
        get_string('greeting_evaluatee_help', 'block_external_feedback'), 'Hello World Evaluatees!'));

    $settings->add(new admin_setting_confightmleditor('block_external_feedback/greeting_evaluator', get_string('greeting_evaluator', 'block_external_feedback'),
        get_string('greeting_evaluator_help', 'block_external_feedback'), 'Hello World Evaluators!'));

    $settings->add(new admin_setting_pickroles('block_external_feedback/roleshortname_evaluator', get_string('roleshortname_evaluator', 'block_external_feedback'),
        get_string('roleshortname_evaluator_help', 'block_external_feedback'), array()));

    $settings->add(new admin_setting_pickroles('block_external_feedback/roleshortname_evaluatee', get_string('roleshortname_evaluatee', 'block_external_feedback'),
        get_string('roleshortname_evaluatee_help', 'block_external_feedback'), array()));

    $settings->add(new admin_setting_configcheckbox('block_external_feedback/scheduled_task_verbose', get_string('scheduled_task_verbose', 'block_external_feedback'),
        get_string('scheduled_task_verbose_desc', 'block_external_feedback'), false));
}
