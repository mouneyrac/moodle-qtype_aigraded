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
 * Test helper for the aigraded question type.
 *
 * @package    qtype_aigraded
 * @copyright  2026 onwards
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Provides aigraded test questions for the data generator.
 *
 * @package    qtype_aigraded
 * @copyright  2026 onwards
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_aigraded_test_helper extends question_test_helper {
    #[\Override]
    public function get_test_questions() {
        return ['editor'];
    }

    /**
     * Make an aigraded question definition (HTML editor input).
     *
     * @return qtype_aigraded_question
     */
    public function make_aigraded_question_editor() {
        question_bank::load_question_definition_classes('aigraded');
        $q = new qtype_aigraded_question();
        test_question_maker::initialise_a_question($q);
        $q->name = 'AI-reviewed essay';
        $q->questiontext = 'Write a short argument.';
        $q->generalfeedback = '';
        $q->responseformat = 'editor';
        $q->responserequired = 1;
        $q->responsefieldlines = 10;
        $q->minwordlimit = null;
        $q->maxwordlimit = null;
        $q->attachments = 0;
        $q->attachmentsrequired = 0;
        $q->maxbytes = 0;
        $q->filetypeslist = null;
        $q->graderinfo = '';
        $q->graderinfoformat = FORMAT_HTML;
        $q->qtype = question_bank::get_qtype('aigraded');
        return $q;
    }

    /**
     * Form data for an aigraded question (HTML editor input) with a rubric.
     *
     * @return stdClass
     */
    public function get_aigraded_question_form_data_editor() {
        $fromform = new stdClass();
        $fromform->name = 'AI-reviewed essay';
        $fromform->questiontext = ['text' => 'Write a short argument.', 'format' => FORMAT_HTML];
        $fromform->defaultmark = 1.0;
        $fromform->generalfeedback = ['text' => '', 'format' => FORMAT_HTML];
        $fromform->responseformat = 'editor';
        $fromform->responserequired = 1;
        $fromform->responsefieldlines = 10;
        $fromform->attachments = 0;
        $fromform->attachmentsrequired = 0;
        $fromform->maxbytes = 0;
        $fromform->filetypeslist = '';
        $fromform->graderinfo = ['text' => '', 'format' => FORMAT_HTML];
        $fromform->responsetemplate = ['text' => '', 'format' => FORMAT_HTML];
        $fromform->rubric = "Thesis | Absent | Clear | Nuanced\nEvidence | None | Some | Cited";
        $fromform->status = \core_question\local\bank\question_version_status::QUESTION_STATUS_READY;
        return $fromform;
    }
}
