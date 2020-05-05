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
 * Restore step script.
 * @package    mod_kalvidres
 * @copyright  (C) 2010 onwards Eloy Lafuente (stronk7) {@link http://stronk7.com}
 * @copyright  (C) 2016-2017 Yamaguchi University <info-cc@ml.cc.yamaguchi-u.ac.jp>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Structure step to restore one kalvidres activity.
 *
 * @package    mod_kalvidres
 * @copyright  (C) 2010 onwards Eloy Lafuente (stronk7) {@link http://stronk7.com}
 * @copyright  (C) 2016-2017 Yamaguchi University <info-cc@ml.cc.yamaguchi-u.ac.jp>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class restore_kalvidres_activity_structure_step extends restore_activity_structure_step {

    /**
     * Define (add) particular settings this resource can have.
     * @return object - define structure.
     */
    protected function define_structure() {

        $paths = array();
        $userinfo = $this->get_setting_value('userinfo');

        $paths[] = new restore_path_element('kalvidres', '/activity/kalvidres');
		
		/*
        if ($userinfo) {
            $paths[] = new restore_path_element('kalvidres_log', '/activity/kalvidres/logs/log');
        }
		*/
        // Return the paths wrapped into standard activity structure.
        return $this->prepare_activity_structure($paths);
    }

    /**
     * Define (add) particular settings this resource can have.
     * @param object $data - array of data.
     */
    protected function process_kalvidres($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->course = $this->get_courseid();

        $data->timemodified = $this->apply_date_offset($data->timemodified);

        // Insert the kalvidres record.
        $newitemid = $DB->insert_record('kalvidres', $data);
        // Immediately after inserting "activity" record, call this.
        $this->apply_activity_instance($newitemid);
    }

    /**
     * Restore kalvidres_log.
     * @param array $data - structure defines.
     */
    protected function process_kalvidres_log($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        $data->instanceid = $this->get_new_parentid('kalvidres');
        $data->userid = $this->get_mappingid('user', $data->userid);

        $newitemid = $DB->insert_record('kalvidres_log', $data);
        $this->set_mapping('kalvidres_log', $oldid, $newitemid);
    }

    /**
     * Restore related files.
     */
    protected function after_execute() {
        // Add kalvidres related files, no need to match by itemname (just internally handled context).
        $this->add_related_files('mod_kalvidres', 'intro', null);
    }
}
