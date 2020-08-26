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
 * Code to be executed after the plugin's database scheme has been installed is defined here.
 *
 * @package     local_auto_visibility
 * @category    upgrade
 * @copyright   2020 Szabolcs Várhegyi <sz.varhegyi@gmail.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Custom code to be run on installing the plugin.
 */
function xmldb_local_auto_visibility_install() {

    $handler = \core_customfield\handler::get_handler('core_course', 'course');
    $hasCategory = false;
    $category = null;
    $c = $handler->get_categories_with_fields();
    foreach($c as $a) {
        if ($a->get('name') == get_string('course_group_name', 'local_auto_visibility')) {
            $hasCategory = true;
            $category = \core_customfield\category_controller::create($a->get('id'));
            break;
        }
    }

    if($hasCategory == false) {
        $categoryid = $handler->create_category(get_string('course_group_name', 'local_auto_visibility'));
        $category = \core_customfield\category_controller::create($categoryid);
    }


    $record = new \stdClass();
    $record->type = "checkbox";
    $record->shortname = "auto_visibility";
    $record->name = get_string('auto_visibility', 'local_auto_visibility');
    $record->description = get_string('auto_visibility_description', 'local_auto_visibility');
    $record->descriptionformat = 1;
    $record->configdata = '{"required":"0","uniquevalues":"0","checkbydefault":"0","locked":"0","visibility":"0"}';

    $field = \customfield_checkbox\field_controller::create(0, $record, $category);

    $field->save();

    return true;
}
