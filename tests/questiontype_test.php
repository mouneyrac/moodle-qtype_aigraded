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

/**
 * Tests that the per-question rubric round-trips through the question save/load path.
 *
 * @package    qtype_aigraded
 * @covers     \qtype_aigraded
 * @copyright  2026 onwards
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class questiontype_test extends \advanced_testcase {
    public function test_rubric_saves_and_loads_with_the_question(): void {
        $this->resetAfterTest();
        $this->setAdminUser();
        $generator = $this->getDataGenerator()->get_plugin_generator('core_question');
        $cat = $generator->create_question_category();
        $question = $generator->create_question('aigraded', 'editor', ['category' => $cat->id]);

        $loaded = \question_bank::load_question_data($question->id);
        $this->assertStringContainsString('Thesis', $loaded->options->rubric);
        $this->assertStringContainsString('Evidence', $loaded->options->rubric);

        // The static accessor used by the background task returns the same rubric.
        $this->assertStringContainsString('Thesis', \qtype_aigraded::rubric_for((int) $question->id));
    }

    public function test_deleting_the_question_removes_its_rubric(): void {
        global $DB;
        $this->resetAfterTest();
        $this->setAdminUser();
        $generator = $this->getDataGenerator()->get_plugin_generator('core_question');
        $cat = $generator->create_question_category();
        $question = $generator->create_question('aigraded', 'editor', ['category' => $cat->id]);

        question_delete_question($question->id);
        $this->assertSame(0, $DB->count_records('qtype_aigraded', ['questionid' => $question->id]));
    }
}
