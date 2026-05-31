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
 * The aigraded question type — an Essay question whose response is reviewed against a rubric by the AI.
 *
 * @package    qtype_aigraded
 * @copyright  2026 onwards
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/question/type/essay/questiontype.php');

/**
 * Extends the Essay question type with a per-question AI rubric. The student experience is unchanged
 * (a manually-graded essay); a background review assesses the response against the rubric for the teacher.
 *
 * @package    qtype_aigraded
 * @copyright  2026 onwards
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_aigraded extends qtype_essay {
    /** @var string The per-question rubric table. */
    private const TABLE = 'qtype_aigraded';

    #[\Override]
    public function get_question_options($question) {
        global $DB;
        parent::get_question_options($question);
        $question->options->rubric = (string) $DB->get_field(self::TABLE, 'rubric', ['questionid' => $question->id]);
        return true;
    }

    #[\Override]
    public function save_question_options($formdata) {
        global $DB;
        parent::save_question_options($formdata);
        $rubric = (string) ($formdata->rubric ?? '');
        $existing = $DB->get_record(self::TABLE, ['questionid' => $formdata->id]);
        if ($existing) {
            $existing->rubric = $rubric;
            $DB->update_record(self::TABLE, $existing);
        } else {
            $DB->insert_record(self::TABLE, (object) ['questionid' => $formdata->id, 'rubric' => $rubric]);
        }
    }

    #[\Override]
    public function delete_question($questionid, $contextid) {
        global $DB;
        $DB->delete_records(self::TABLE, ['questionid' => $questionid]);
        parent::delete_question($questionid, $contextid);
    }

    /**
     * The rubric configured on a question (raw text, '' if none).
     *
     * @param int $questionid
     * @return string
     */
    public static function rubric_for(int $questionid): string {
        global $DB;
        return (string) $DB->get_field(self::TABLE, 'rubric', ['questionid' => $questionid]);
    }
}
