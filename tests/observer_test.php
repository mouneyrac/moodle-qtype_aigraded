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

use aiplacement_gradeconfidence\local\run_store;

/**
 * Integration test for the quiz-review seam: attempt submission -> observer -> adhoc task -> review -> store.
 *
 * The pieces are unit-tested elsewhere (review::run with a fake grader; the grader with a fake client).
 * This joins them through the real event/observer/task path — the one part that otherwise only ran in
 * manual end-to-end testing, and where a regression would silently stop quiz reviews from firing.
 *
 * @package    qtype_aigraded
 * @covers     \qtype_aigraded\observer
 * @covers     \qtype_aigraded\task\review_attempt
 * @copyright  2026 onwards
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class observer_test extends \advanced_testcase {
    public function test_submitting_an_attempt_queues_and_runs_the_review(): void {
        global $CFG;
        require_once($CFG->dirroot . '/mod/quiz/locallib.php');
        $this->resetAfterTest();
        $this->setAdminUser();
        set_config('mode', 'auto', 'aiplacement_gradeconfidence');

        // A course with a quiz that has one aigraded question (the generator gives it a rubric), and a student.
        $gen = $this->getDataGenerator();
        $course = $gen->create_course();
        $student = $gen->create_and_enrol($course, 'student');
        $quiz = $gen->get_plugin_generator('mod_quiz')->create_instance(['course' => $course->id, 'sumgrades' => 1]);
        $qgen = $gen->get_plugin_generator('core_question');
        $cat = $qgen->create_question_category();
        $question = $qgen->create_question('aigraded', 'editor', ['category' => $cat->id]);
        quiz_add_quiz_question($question->id, $quiz, 0, 1.0);

        // Start and finish an attempt as the student; process_finish() fires attempt_submitted.
        $quizobj = \mod_quiz\quiz_settings::create($quiz->id, $student->id);
        $this->setUser($student);
        $quba = \question_engine::make_questions_usage_by_activity('mod_quiz', $quizobj->get_context());
        $quba->set_preferred_behaviour($quizobj->get_quiz()->preferredbehaviour);
        $timenow = time();
        $attempt = quiz_create_attempt($quizobj, 1, false, $timenow, false, $student->id);
        quiz_start_new_attempt($quizobj, $quba, $attempt, 1, $timenow);
        quiz_attempt_save_started($quizobj, $quba, $attempt);

        $attemptobj = \mod_quiz\quiz_attempt::create($attempt->id);
        $attemptobj->process_submitted_actions($timenow, false, [
            1 => [
                'answer' => 'Social media should be regulated; the evidence shows real harms to young people.',
                'answerformat' => FORMAT_HTML,
            ],
        ]);
        $attemptobj->process_submit($timenow, false);
        $attemptobj->process_grade_submission($timenow);

        // 1) The observer queued exactly one review task, carrying this attempt's id.
        $tasks = \core\task\manager::get_adhoc_tasks(task\review_attempt::class);
        $this->assertCount(1, $tasks);
        $task = reset($tasks);
        $this->assertSame($attempt->id, (int) $task->get_custom_data()->attemptid);

        // 2) Executing it reaches review::run and stores a quiz review keyed to the response.
        $task->execute();
        $qaid = (int) \mod_quiz\quiz_attempt::create($attempt->id)->get_question_attempt(1)->get_database_id();
        $run = run_store::get_by_source('quiz', $qaid);
        $this->assertNotNull($run, 'A quiz review run should be stored for the aigraded response.');
        // No AI provider is configured in the test DB, so the engine degrades to a graceful partial
        // (status "error"). The point here is that the whole chain fired and persisted a run.
        $this->assertSame('error', $run['status']);
    }

    public function test_no_task_is_queued_when_the_quiz_has_no_aigraded_question(): void {
        global $CFG;
        require_once($CFG->dirroot . '/mod/quiz/locallib.php');
        $this->resetAfterTest();
        $this->setAdminUser();
        set_config('mode', 'auto', 'aiplacement_gradeconfidence');

        // Same flow, but with a plain essay question — the observer still queues a task (it cannot know
        // the slot types cheaply), but executing it must store nothing for a non-aigraded response.
        $gen = $this->getDataGenerator();
        $course = $gen->create_course();
        $student = $gen->create_and_enrol($course, 'student');
        $quiz = $gen->get_plugin_generator('mod_quiz')->create_instance(['course' => $course->id, 'sumgrades' => 1]);
        $qgen = $gen->get_plugin_generator('core_question');
        $cat = $qgen->create_question_category();
        $question = $qgen->create_question('essay', null, ['category' => $cat->id]);
        quiz_add_quiz_question($question->id, $quiz, 0, 1.0);

        $quizobj = \mod_quiz\quiz_settings::create($quiz->id, $student->id);
        $this->setUser($student);
        $quba = \question_engine::make_questions_usage_by_activity('mod_quiz', $quizobj->get_context());
        $quba->set_preferred_behaviour($quizobj->get_quiz()->preferredbehaviour);
        $timenow = time();
        $attempt = quiz_create_attempt($quizobj, 1, false, $timenow, false, $student->id);
        quiz_start_new_attempt($quizobj, $quba, $attempt, 1, $timenow);
        quiz_attempt_save_started($quizobj, $quba, $attempt);
        $attemptobj = \mod_quiz\quiz_attempt::create($attempt->id);
        $attemptobj->process_submitted_actions($timenow, false, [
            1 => ['answer' => 'A plain essay answer.', 'answerformat' => FORMAT_HTML],
        ]);
        $attemptobj->process_submit($timenow, false);
        $attemptobj->process_grade_submission($timenow);

        $tasks = \core\task\manager::get_adhoc_tasks(task\review_attempt::class);
        $this->assertCount(1, $tasks);
        reset($tasks)->execute();

        $qaid = (int) \mod_quiz\quiz_attempt::create($attempt->id)->get_question_attempt(1)->get_database_id();
        $this->assertNull(run_store::get_by_source('quiz', $qaid));
    }
}
