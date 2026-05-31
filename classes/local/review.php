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

namespace qtype_aigraded\local;

use aiplacement_gradeconfidence\grader;
use aiplacement_gradeconfidence\local\rubric_text;
use aiplacement_gradeconfidence\local\run_store;

/**
 * Reviews one quiz essay response against its question rubric through the Grade Confidence engine and
 * stores the outcome (sourcetype 'quiz'). There is no teacher filling to diff against, so the review is an
 * AI assessment; it never writes a grade. Surfaced via the engine's trace + consistency dashboard.
 *
 * @package    qtype_aigraded
 * @copyright  2026 onwards
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class review {
    /**
     * Review every aigraded essay response in one quiz attempt (shared by the auto task and the on-demand
     * trigger). Returns how many responses were reviewed.
     *
     * @param int $attemptid The quiz attempt id.
     * @return int Responses reviewed.
     */
    public static function for_attempt(int $attemptid): int {
        try {
            $attemptobj = \mod_quiz\quiz_attempt::create($attemptid);
        } catch (\moodle_exception $e) {
            return 0; // The attempt was deleted.
        }
        $contextid = (int) $attemptobj->get_quizobj()->get_context()->id;
        $userid = (int) $attemptobj->get_userid();
        $count = 0;
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
                if (self::run($contextid, (int) $qa->get_database_id(), $userid, $rubric,
                        (string) $qa->get_response_summary())) {
                    $count++;
                }
            } catch (\Throwable $e) {
                // One failed response must not stop the others.
                debugging('qtype_aigraded review failed: ' . $e->getMessage(), DEBUG_DEVELOPER);
            }
        }
        return $count;
    }

    /**
     * Run the review for one response.
     *
     * @param int $contextid The quiz module context id.
     * @param int $qaid The question_attempt id (the stored review's source id).
     * @param int $userid The student whose response is reviewed.
     * @param string $rubrictext The question's rubric (one criterion per line).
     * @param string $response The student's response text.
     * @param grader|null $grader Injected for tests; defaults to the engine grader.
     * @return bool True if a review was run and stored.
     */
    public static function run(
        int $contextid,
        int $qaid,
        int $userid,
        string $rubrictext,
        string $response,
        ?grader $grader = null
    ): bool {
        $criteria = rubric_text::parse($rubrictext);
        if (!rubric_text::is_valid($criteria) || trim($response) === '') {
            return false;
        }
        $grader = $grader ?? new grader();
        $result = $grader->review(
            $contextid,
            $userid,
            rubric_text::to_review_rubric($criteria),
            $response,
            [],
            self::prompt_options()
        );
        run_store::save($contextid, 'quiz', $qaid, $userid, $result);
        return true;
    }

    /**
     * Site-level prompt directives shared with the assign surface (no per-question model answer in v1).
     *
     * @return array
     */
    private static function prompt_options(): array {
        return [
            'language' => (string) get_config('aiplacement_gradeconfidence', 'feedbacklanguage'),
            'fairness' => (bool) get_config('aiplacement_gradeconfidence', 'fairmode'),
            'detect' => (bool) get_config('aiplacement_gradeconfidence', 'aidetection'),
        ];
    }
}
