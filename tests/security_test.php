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
    public function test_renderer_adds_no_student_facing_output_over_essay(): void {
        global $CFG;
        require_once($CFG->dirroot . '/question/type/aigraded/renderer.php');

        $this->assertTrue(class_exists('qtype_aigraded_renderer'));
        $this->assertSame(
            'qtype_essay_renderer',
            get_parent_class('qtype_aigraded_renderer'),
            'SECURITY: the question type must render exactly like a core Essay question for students'
        );

        // No methods declared directly on our renderer ⇒ no override that could surface the review.
        $ref = new \ReflectionClass('qtype_aigraded_renderer');
        $own = array_values(array_filter(
            array_map(static fn (\ReflectionMethod $m) => $m->getName(), $ref->getMethods()),
            static fn (string $name) => (new \ReflectionMethod('qtype_aigraded_renderer', $name))
                ->getDeclaringClass()->getName() === 'qtype_aigraded_renderer'
        ));
        $this->assertSame(
            [],
            $own,
            'SECURITY: the qtype renderer must not override Essay rendering — students must never see the review'
        );
    }
}
