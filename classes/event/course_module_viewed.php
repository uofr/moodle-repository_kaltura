<?php

// This file is part of the Certificate module for Moodle - http://moodle.org/
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
 * The mod_kalvidres course module viewed event.
 *
 * @package    mod_kalvidres
 * @copyright  2016 Trevor Cunningham <trevor.cunningham@uregina.ca>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_kalvidres\event;

defined('MOODLE_INTERNAL') || die();

/**
 * The mod_mail course module viewed event class.
 *
 * @package    mod_kalvidres
 * @since      Moodle 3.0
 * @copyright  2016 Trevor Cunningham <trevor.cunningham@uregina.ca>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class course_module_viewed extends \core\event\course_module_viewed {
    
    /**
     * Init method.
     *
     * @return void
     */
    protected function init() {
        $this->data['crud'] = 'r';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
        $this->data['objecttable'] = 'kalvidres';
    }
    /**
     * Return localised event name.
     *
     * @return string
     */
    public static function get_name() {
        return get_string('media_viewed', 'mod_kalvidres');
    }
    /**
     * Get URL related to the action
     *
     * @return \moodle_url
     */
    public function get_url() {
        return new \moodle_url('/mod/kalvidres/view.php', array('id' => $this->contextinstanceid));
    }
    
    /**
     * Returns description of what happened.
     *
     * @return string
     */
    public function get_description() {
        return "The user with id '$this->userid' viewed the media resource with id '$this->contextinstanceid'.";
    }
    
    /**
     * Return the legacy event log data.
     *
     * @return array|null
     */
    protected function get_legacy_logdata() {
		//add_to_log($course->id, 'kalvidres', 'view video resource', 'view.php?id='.$cm->id, $kalvidres->id, $cm->id);
        return array($this->courseid, 'kalvidres', 'view video resource', 'view.php?id=' . $this->contextinstanceid,
            $this->objectid, $this->contextinstanceid);
    }
}