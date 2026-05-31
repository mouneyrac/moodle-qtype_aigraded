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
 * Renderer for the aigraded question type.
 *
 * @package    qtype_aigraded
 * @copyright  2026 onwards
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/question/type/essay/renderer.php');

/**
 * Identical to an Essay question for the student. The only addition is a grader-only on-demand check
 * trigger, shown strictly when the manual comment is editable (i.e. a teacher is grading) — never to a
 * student. This is the standard Moodle mechanism for grader-only question output.
 *
 * @package    qtype_aigraded
 * @copyright  2026 onwards
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_aigraded_renderer extends qtype_essay_renderer {
    #[\Override]
    public function feedback(question_attempt $qa, question_display_options $options) {
        $feedback = parent::feedback($qa, $options);
        // SECURITY: only when the manual comment is editable — a teacher grading. A student attempting or
        // reviewing never has an editable comment, so never sees this. In 'auto' mode the review runs on
        // submission, so no manual trigger is offered.
        if ($options->manualcomment != question_display_options::EDITABLE) {
            return $feedback;
        }
        if (get_config('aiplacement_gradeconfidence', 'mode') === 'auto') {
            return $feedback;
        }
        $url = new moodle_url('/question/type/aigraded/request.php', [
            'usageid' => $qa->get_usage_id(),
            'sesskey' => sesskey(),
        ]);
        return $feedback . html_writer::div(
            html_writer::link(
                $url,
                get_string('requestcheck', 'qtype_aigraded'),
                ['class' => 'btn btn-secondary btn-sm']
            ),
            'qtype_aigraded-request'
        );
    }
}
