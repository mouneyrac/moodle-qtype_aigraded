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
 * Queues the background review when a quiz attempt is submitted.
 *
 * @package    qtype_aigraded
 * @copyright  2026 onwards
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class observer {
    /**
     * On attempt submission, queue an adhoc task to review any aigraded responses.
     *
     * @param \mod_quiz\event\attempt_submitted $event
     */
    public static function attempt_submitted(\mod_quiz\event\attempt_submitted $event): void {
        // Only auto-review on submission in 'auto' mode. The default is on-demand: the teacher triggers a
        // check when they want one, which keeps cost low enough to afford the best model.
        if (get_config('aiplacement_gradeconfidence', 'mode') !== 'auto') {
            return;
        }
        $task = new \qtype_aigraded\task\review_attempt();
        $task->set_custom_data(['attemptid' => $event->objectid]);
        \core\task\manager::queue_adhoc_task($task);
    }
}
