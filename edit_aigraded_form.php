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
 * Editing form for the aigraded question type.
 *
 * @package    qtype_aigraded
 * @copyright  2026 onwards
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/question/type/essay/edit_essay_form.php');

/**
 * Adds an AI rubric to the Essay editing form.
 *
 * @package    qtype_aigraded
 * @copyright  2026 onwards
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_aigraded_edit_form extends qtype_essay_edit_form {
    #[\Override]
    protected function definition_inner($mform) {
        parent::definition_inner($mform);

        $mform->addElement('header', 'aigradedheader', get_string('airubricheader', 'qtype_aigraded'));
        $mform->addElement(
            'textarea',
            'rubric',
            get_string('airubric', 'qtype_aigraded'),
            ['rows' => 8, 'cols' => 70]
        );
        $mform->setType('rubric', PARAM_RAW);
        $mform->addElement('static', 'rubrichelp', '', get_string('airubric_help', 'qtype_aigraded'));
    }

    #[\Override]
    protected function data_preprocessing($question) {
        $question = parent::data_preprocessing($question);
        if (!empty($question->options->rubric)) {
            $question->rubric = $question->options->rubric;
        }
        return $question;
    }

    #[\Override]
    public function qtype() {
        return 'aigraded';
    }
}
