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
 * Strings for qtype_aigraded.
 *
 * @package    qtype_aigraded
 * @copyright  2026 onwards
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

$string['airubric'] = 'AI review rubric';
$string['airubric_help'] = 'One criterion per line: the criterion name followed by its level labels, separated by "|". Example: Use of evidence | None | Asserts | Some support | Well-cited. After a student submits, Grade Confidence reviews the response against this rubric in the background; you see the result in the course\'s grading-consistency report. It never sets the grade — you still mark the essay yourself.';
$string['airubricheader'] = 'Grade Confidence review';
$string['creditsout'] = 'You have used your AI check allowance for this course.';
$string['pluginname'] = 'Essay (AI-reviewed)';
$string['pluginname_help'] = 'A standard Essay question whose response Grade Confidence reviews against a rubric for the teacher. The student answers exactly as for an Essay; the grade is still set by the teacher.';
$string['pluginnameadding'] = 'Adding an AI-reviewed Essay question';
$string['pluginnameediting'] = 'Editing an AI-reviewed Essay question';
$string['pluginnamesummary'] = 'An Essay question whose response is reviewed against a rubric by Grade Confidence (the teacher still grades it).';
$string['privacy:metadata'] = 'The AI-reviewed Essay question type stores only the per-question rubric (question configuration). Review outcomes and any submission text sent to AI are described by the Grade Confidence engine (aiplacement_gradeconfidence).';
$string['requestcheck'] = 'Ask Grade Confidence to check this attempt';
$string['requestmore'] = 'Request more checks';
$string['reviewdone'] = 'Grade Confidence checked the AI-graded responses in this attempt.';
$string['reviewnone'] = 'Nothing to check here (no AI-graded responses with a rubric and an answer).';
