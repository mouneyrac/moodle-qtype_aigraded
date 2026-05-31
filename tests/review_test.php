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

namespace qtype_aigraded;

use qtype_aigraded\local\review;
use aiplacement_gradeconfidence\grader;
use aiplacement_gradeconfidence\local\run_store;
use aiplacement_gradeconfidence\local\text_client;

/**
 * Tests for the quiz-response review core.
 *
 * @package    qtype_aigraded
 * @covers     \qtype_aigraded\local\review
 * @copyright  2026 onwards
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class review_test extends \advanced_testcase {
    /**
     * A grader whose client returns a canned one-criterion assessment.
     *
     * @return grader
     */
    private function fake_grader(): grader {
        $client = new class implements text_client {
            /**
             * Return a canned response scoring criterion c0 at level 1.
             *
             * @param int $contextid
             * @param int $userid
             * @param string $prompt
             * @return string|null
             */
            public function generate(int $contextid, int $userid, string $prompt): ?string {
                return '{"criteria":[{"id":"c0","chosen_level_index":1,"evidence":[],"reasoning":"r"}]}';
            }
        };
        return new grader($client, 1);
    }

    public function test_run_stores_a_quiz_review(): void {
        $this->resetAfterTest();
        $ok = review::run(123, 50, 500, 'Idea | Weak | OK | Strong', 'An essay about the idea.', $this->fake_grader());

        $this->assertTrue($ok);
        $stored = run_store::get_by_source('quiz', 50);
        $this->assertNotNull($stored);
        $this->assertSame('ok', $stored['status']);
        // No teacher filling in a quiz → an AI assessment (the flagged criterion has no teacher level).
        $this->assertNull($stored['flags'][0]['teacherlevel']);
    }

    public function test_run_skips_without_rubric(): void {
        $this->resetAfterTest();
        $this->assertFalse(review::run(1, 1, 1, '', 'essay', $this->fake_grader()));
        $this->assertNull(run_store::get_by_source('quiz', 1));
    }

    public function test_run_skips_empty_response(): void {
        $this->resetAfterTest();
        $this->assertFalse(review::run(1, 2, 1, 'Idea | A | B', '   ', $this->fake_grader()));
        $this->assertNull(run_store::get_by_source('quiz', 2));
    }
}
