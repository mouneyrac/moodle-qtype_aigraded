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
 * Security invariant for the AI-graded question type.
 *
 * The whole privacy guarantee for the quiz surface rests on one structural fact: the student-facing
 * rendering is identical to a core Essay question, so the assurance review is never shown to students.
 * This test fails loudly if anyone adds a renderer override that could leak the review.
 *
 * @package    qtype_aigraded
 * @group      security
 * @coversNothing
 * @copyright  2026 onwards
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class security_test extends \basic_testcase {
    public function test_renderer_only_adds_a_grader_gated_trigger_over_essay(): void {
        global $CFG;
        require_once($CFG->dirroot . '/question/type/aigraded/renderer.php');

        $this->assertTrue(class_exists('qtype_aigraded_renderer'));
        $this->assertSame(
            'qtype_essay_renderer',
            get_parent_class('qtype_aigraded_renderer'),
            'SECURITY: the question type must render exactly like a core Essay question for students'
        );

        // The only override may be feedback() — and it must be gated to the grading context.
        $ref = new \ReflectionClass('qtype_aigraded_renderer');
        $own = [];
        foreach ($ref->getMethods() as $method) {
            if ($method->getDeclaringClass()->getName() === 'qtype_aigraded_renderer') {
                $own[] = $method->getName();
            }
        }
        $this->assertSame(
            ['feedback'],
            $own,
            'SECURITY: the only renderer override may be the grader-gated feedback()'
        );

        // The on-demand trigger must be gated on the editable manual comment — i.e. shown only while a
        // teacher is grading, never to a student attempting or reviewing.
        $source = file_get_contents((new \ReflectionMethod('qtype_aigraded_renderer', 'feedback'))->getFileName());
        $this->assertStringContainsString(
            'question_display_options::EDITABLE',
            $source,
            'SECURITY: the trigger must gate on the editable manual comment so students never see it'
        );
    }
}
