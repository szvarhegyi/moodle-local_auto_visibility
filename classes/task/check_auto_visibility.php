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
 * Configurable notifications - Course closed notification
 *
 * @package     local_auto_visibility
 * @copyright   Várhegyi Szabolcs <sz.varhegyi@gmail.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_auto_visibility\task;


class check_auto_visibility extends \core\task\scheduled_task
{

    public function get_name()
    {
        return get_string('taskname', 'local_auto_visibility');
    }

    public function execute()
    {
        global $DB;

        $end = time();

        $field = $DB->get_record('customfield_field', ['shortname' => 'auto_visibility']);

        if($field) {

            $field->name = get_string('auto_visibility', 'local_auto_visibility');
            $field->description = get_string('auto_visibility_description', 'local_auto_visibility');
            $DB->update_record('customfield_field', $field);

            $field_category = $DB->get_record('customfield_category', ['id' => $field->categoryid]);
            $field_category->name = get_string('course_group_name', 'local_auto_visibility');
            $DB->update_record('customfield_category', $field_category);

            mtrace("Check which courser started beetwen: " . date('Y-m-d H:i:s', $start)
                . ' and ' . date('Y-m-d H:i:s', $end));

            //$DB->set_debug(true);
            $entries = $DB->get_records_sql('SELECT cd.value, c.* FROM {customfield_data} cd LEFT JOIN {course} c ON c.id = cd.instanceid WHERE
                                        cd.fieldid = ? AND cd.value = ? AND c.visible = ? AND c.startdate < ?', [
                $field->id,
                "1",
                "0",
                $end
            ]);

            foreach($entries as $course) {
                $course->visible = 1;
                $DB->update_record('course', $course);
                mtrace('Set course visibility to show. Course: ' . $course->fullname);
            }

            mtrace("Check which courser ended beetwen: " . date('Y-m-d H:i:s', $start)
                . ' and ' . date('Y-m-d H:i:s', $end));

            $entries = $DB->get_records_sql('SELECT cd.value, c.* FROM {customfield_data} cd LEFT JOIN {course} c ON c.id = cd.instanceid WHERE
                                        cd.fieldid = ? AND cd.value = ? AND c.visible = ? AND c.enddate < ?', [
                $field->id,
                "1",
                "1",
                "0",
                $end
            ]);

            foreach($entries as $course) {
                $course->visible = 0;
                $DB->update_record('course', $course);
                mtrace('Set course visibility to hide. Course: ' . $course->fullname);
            }
        }

    }

}