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
 * The repository_kaltura category deleted event.
 *
 * @package    repository_kaltura
 * @copyright  
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace repository_kaltura\event;
defined('MOODLE_INTERNAL') || die();

/**
 * The repository_kaltura message sent event class.
 *
 * @package    repository_kaltura
 * @since      Moodle 3.1
 * @copyright  
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class category_deleted extends \core\event\base {
    /**
     * Init method.
     *
     * @return void
     */
    protected function init() {
        $this->data['crud'] = 'd';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
        $this->data['objecttable'] = 'mail_messages';
    }

    /**
     * Return localised event name.
     *
     * @return string
     */
    public static function get_name() {
        return get_string('categorydeleted', 'repository_kaltura');
    }

    /**
     * Returns description of what happened.
     *
     * @return string
     */
    public function get_description() {
        return "The user with id '$this->userid' deleted the course with id '$this->objectid' for the course with " .
            "context id '$this->contextinstanceid'.";
    }

    /**
     * Return the legacy event log data.
     *
     * @return array
     */
    
    protected function get_legacy_logdata() {
        return array($this->courseid, 'repository_kaltura', 'Course category deleted', '',
            $this->objectid, $this->contextinstanceid);
		//add_to_log($event->courseid, 'repository_kaltura', 'Course category deleted', '', 'course id - ' . $event->courseid);
    }

    /**
     * Get URL related to the action.
     *
     * @return \moodle_url
     */
    
    public function get_url() {
        return new \moodle_url('/mod/mail/mail_read.php', array('id' => $this->contextinstanceid, 'mid' => $this->objectid));
    }
    
}
