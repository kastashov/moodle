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
 * A scheduled task for lesson cron.
 *
 * @package    mod_lesson
 * @copyright  2017 Kirill Astashov <kirill.astashov@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_lesson\task;

class cron_task extends \core\task\scheduled_task {

    /**
     * Get a descriptive name for this task (shown to admins).
     *
     * @return string
     */
    public function get_name() {
        return get_string('crontask', 'mod_lesson');
    }

    /**
     * Run lesson cron.
     */
    public function execute() {
        global $DB;

        // Replay the upgrade step 2015032700.
        // Delete any orphaned lesson_branch record.
        if ($DB->get_dbfamily() === 'mysql') {
            $sql = "DELETE {lesson_branch}
                      FROM {lesson_branch}
                 LEFT JOIN {lesson_pages}
                        ON {lesson_branch}.pageid = {lesson_pages}.id
                     WHERE {lesson_pages}.id IS NULL";
        } else {
            $sql = "DELETE FROM {lesson_branch}
               WHERE NOT EXISTS (
                         SELECT 'x' FROM {lesson_pages}
                          WHERE {lesson_branch}.pageid = {lesson_pages}.id)";
        }

        $DB->execute($sql);
    }
}
