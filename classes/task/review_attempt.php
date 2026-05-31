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

namespace qtype_aigraded\task;

use qtype_aigraded\local\review;

/**
 * Background review of the aigraded essay responses in one quiz attempt. Runs after submission so the slow,
 * billable AI call never blocks the student; the teacher sees results in the consistency dashboard / trace.
 *
 * @package    qtype_aigraded
 * @copyright  2026 onwards
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class review_attempt extends \core\task\adhoc_task {
    #[\Override]
    public function execute() {
        $data = $this->get_custom_data();
        $attemptid = (int) ($data->attemptid ?? 0);
        if (!$attemptid) {
            return;
        }

        try {
            $attemptobj = \mod_quiz\quiz_attempt::create($attemptid);
        } catch (\moodle_exception $e) {
            return; // The attempt was deleted before the task ran.
        }
        $contextid = (int) $attemptobj->get_quizobj()->get_context()->id;
        $userid = (int) $attemptobj->get_userid();

        foreach ($attemptobj->get_slots() as $slot) {
            if ($attemptobj->get_question_type_name($slot) !== 'aigraded') {
                continue;
            }
            $qa = $attemptobj->get_question_attempt($slot);
            $rubric = \qtype_aigraded::rubric_for((int) $qa->get_question_id());
            if (trim($rubric) === '') {
                continue;
            }
            try {
                review::run(
                    $contextid,
                    (int) $qa->get_database_id(),
                    $userid,
                    $rubric,
                    (string) $qa->get_response_summary()
                );
            } catch (\Throwable $e) {
                // One failed response must not stop the others.
                debugging('qtype_aigraded review failed: ' . $e->getMessage(), DEBUG_DEVELOPER);
            }
        }
    }
}
