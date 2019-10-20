# moodle-meets-zensus

This block is an extension of Blubbsoft's "external_feedback"-block that is licensed under GPLv3.
It provides an interface to Blubbsoft Zensus, so that students can easily evaluate their courses.
For this reason, this block performs a distinction between "evaluators" and "evaluatees". 
Which role is able to evaluate and who ca view the results at which point in time, is freel configurable.

This block is intended to be added to the dashboard of moodle.

## Features

* Calls Zensus' interface and checks for all courses in moodle, whether an evaluation is available (via scheduled task).
* Stores the response as a JSON to Moodle's databsae for later processing.
* Enables certain roles (evaluators) to evaluate certain other roles (evaluatees)
* An evaluator, that evaluated a course once, cannot evaluate it again.
* To sync the evaluations with Zensus, the "idnumber"-field is utilized in each course.
* One can set a greeting with more information for evaluators and evaluatees.

## Prerequisites

* Moodle v3 or later.
* PHP 7 or later.
* Required PHP-Extensions: php7-openssl, php7-json

## Deployment

1. Install the block in Moodle.
2. Zensus requires an extension for Moodle where the URL of Moodle needs to be set (to the base directory)
3. "Geheimnis 1" needs to be shared between Zensus and Moodle.

For a technical documentation, please take a look at the technical documentation of Blubbsoft, "Zugang zum Fragebogen mit Single-Sign-On aus anderen Systemen. Leitfaden: so geht's!" (file: `Leitfaden_IntegrationInLms.pdf`).

## Known-issues

Currently it is only possible to evaluate at a course-level.
It is not possible to evaluate certain lecturers specifically.
If this is intended, it needs to be realized with the built-in capabilities of Zensus.
Ideally, this plugin would be extended for this last missing major feature in the future.
The retrieval of results is also only possible for `type == course` and for courses with `type == personalized` which have exactly one trainer only. (See p. 10 of `Leitfaden_IntegrationInLms.pdf`)

Furthermore, Zensus does not support a "null-state" for evaluations. We use "analyze" for this reason. The state "preview" enables the preview, the state "run" the questionnaire and the state "finished" the retrieval of results. 
If an evaluation should not be available at all, it needs to be set to "analyze".

## Contributors

* Please add *your* name here! :)
* Jonathan Liebers

## License

GPLv3 or later.