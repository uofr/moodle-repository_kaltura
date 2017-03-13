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
 * Observer class containing methods monitoring various events.
 *
 * @package    repository_kaltura
 * @copyright  
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace repository_kaltura;

defined('MOODLE_INTERNAL') || die();

/**
 * Observer class containing methods monitoring various events.
 *
 * @since      Moodle 3.1
 * @package    repository_kaltura
 * @copyright  
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class eventobservers {

    /** @var array $buffer buffer of events. */
    protected $buffer = array();

    /** @var int Number of entries in the buffer. */
    protected $count = 0;

    /** @var  eventobservers a reference to a self instance. */
    protected static $instance;

    /**
     * Course delete event observer.
     * This observer monitors course delete event, and when a course is deleted it deletes any rules and subscriptions associated
     * with it, so no orphan data is left behind.
     *
     * @param \core\event\course_deleted $event The course deleted event.
     */
    public static function course_deleted(\core\event\course_deleted $event) {
		
		
	    global $DB;

	    $kaltura = new kaltura_connection();
	    $connection = $kaltura->get_connection(true, KALTURA_SESSION_LENGTH);

	    if (!empty($connection)) {
	        $category = repository_kaltura_create_course_category($connection, $event->courseid);

	        if ($category) {
	            $param = array('courseid' => $event->courseid);

	            if ($DB->delete_records('repo_kaltura_videos', $param)) {

	                $connection->category->delete($category->id);
	                //add_to_log($event->courseid, 'repository_kaltura', 'Course category deleted', '', 'course id - ' . $event->courseid);
			        $params = array(
			            'context' => $event->get_context(),
			            'objectid' => $event->courseid
			        );
			        $event = \repository_kaltura\event\category_deleted::create($params);
			        $event->add_record_snapshot('course', $event->courseid);
			        $event->add_record_snapshot('category', $category->id);
			        $event->trigger();
	            }
	        }
	    } else {
	        //add_to_log($event->courseid, 'repository_kaltura', 'Course category not deleted', '', 'course id - ' . $event->courseid);
	        $params = array(
	            'context' => $event->get_context(),
	            'objectid' => $event->courseid
	        );
	        $event = \repository_kaltura\event\category_not_deleted::create($params);
	        $event->add_record_snapshot('course', $event->courseid);
	        $event->add_record_snapshot('category', $category->id);
	        $event->trigger();
		}
		
		
		
		/*
        // Delete rules defined inside this course and associated subscriptions.
        $rules = rule_manager::get_rules_by_courseid($event->courseid, 0, 0, false);
        foreach ($rules as $rule) {
            rule_manager::delete_rule($rule->id, $event->get_context());
        }
        // Delete remaining subscriptions inside this course (from site-wide rules).
        subscription_manager::remove_all_subscriptions_in_course($event->get_context());
    	*/
	}


}
