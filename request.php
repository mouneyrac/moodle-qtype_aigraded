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
 * On-demand trigger: a grader reviews the AI-graded essays in one quiz attempt. Side-effecting (calls the
 * AI + writes rows) so it is capability- and sesskey-gated; it consumes one of the teacher's check credits.
 *
 * @package    qtype_aigraded
 * @copyright  2026 onwards
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../../config.php');
require_once($CFG->dirroot . '/mod/quiz/locallib.php');

$usageid = required_param('usageid', PARAM_INT);
require_login();
require_sesskey();

$attemptrow = $DB->get_record('quiz_attempts', ['uniqueid' => $usageid], '*', MUST_EXIST);
$attemptobj = \mod_quiz\quiz_attempt::create($attemptrow->id);
$context = $attemptobj->get_quizobj()->get_context();
require_capability('mod/quiz:grade', $context);

$backurl = new moodle_url('/mod/quiz/report.php', [
    'id' => $attemptobj->get_cmid(),
    'mode' => 'grading',
]);

// Per-teacher credit gate: stop before spending a check once the allowance is used up.
$guard = new \aiplacement_gradeconfidence\local\credit_guard();
$status = $guard->status((int) $context->id, (int) $USER->id);
if ($status['enabled'] && !$status['can']) {
    redirect($backurl, get_string('creditsout', 'qtype_aigraded'));
}

$reviewed = \qtype_aigraded\local\review::for_attempt((int) $attemptrow->id);
if ($reviewed > 0) {
    $guard->consume((int) $context->id, (int) $USER->id);
    redirect($backurl, get_string('reviewdone', 'qtype_aigraded'));
}
redirect($backurl, get_string('reviewnone', 'qtype_aigraded'));
