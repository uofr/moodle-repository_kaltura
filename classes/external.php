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

namespace mod_kalvidres;
defined('MOODLE_INTERNAL') || die;

require_once(dirname(dirname(dirname(dirname(__FILE__)))).'/config.php');
require_once($CFG->dirroot  . '/local/kaltura/API/KalturaClient.php');
require_once($CFG->dirroot . '/local/kaltura/kaltura_entries.class.php');
require_once($CFG->dirroot . '\local\kaltura\locallib.php');
require_once($CFG->dirroot  . '/mod/book/lib.php');
require_once($CFG->dirroot  . '/mod/book/locallib.php');
require_once($CFG->libdir.'/completionlib.php');

/**
 * Connection Class
 */
class kaltura_connection {

    private static $connection  = null;
    private static $timeout     = 0;
    private static $timestarted = 0;

    /**
     * Constructor for Kaltura connection class.
     *
     * @param int $timeout Length of timeout for Kaltura session in minutes
     */
    public function __construct($timeout = KALTURA_SESSION_LENGTH) {
        self::$connection = local_kaltura_login(true, '', $timeout);
        if (!empty(self::$connection)) {
            self::$timestarted = time();
            self::$timeout = $timeout;
        }
    }

    /**
     * Get the connection object.  Pass true to renew the connection
     *
     * @param bool $renew true to renew the session if it has expired.  Otherwise
     * false. (OBSOLETE the connection is always renewed.  TODO: remove this parameter
     * from the function and areas where this method is referenced in all the plug-ins)
     * @param int $timeout seconds to keep the session alive, if zero is passed the
     * last time out value will be used
     * @return object A Kaltura KalturaClient object
     */
    public function get_connection($renew = true, $timeout = 0) {
        self::$connection = local_kaltura_login(true, '', $timeout);
        return self::$connection;
    }

    /**
     * Return the number of seconds the session is alive for
     * @param - none
     * @return int - number of seconds the session is set to live
     */
    public function get_timeout() {

        return self::$timeout;
    }

    /**
     * Return the time the session started
     * @param - none
     * @return int - unix timestamp
     */
    public function get_timestarted() {
        return self::$timestarted;
    }

    public function __destruct() {
        global $SESSION;

        $SESSION->kaltura_con             = serialize(self::$connection);
        $SESSION->kaltura_con_timeout     = self::$timeout;
        $SESSION->kaltura_con_timestarted = self::$timestarted;
    }
}


/**
 * Mobile Kaltura external functions
 *
 * @package    mod_kaltura
 */
class external extends \external_api {
    /**
     * Describes the parameters for get_kaltura_by_courses.
     *
     * @return \external_function_parameters
     */
    public static function get_kaltura_by_courses_parameters() {
        return new \external_function_parameters (
            ['courseids' => new \external_multiple_structure(
                new \external_value(PARAM_INT, 'course id'), 'Array of course ids', VALUE_DEFAULT, []),
            ]
        );
    }
    /**
     * Returns a list of kaltura options in a provided list of courses,
     * if no list is provided all kaltura options that the user can view will be returned.
     *
     * @param array $courseids the course ids
     * @return array the kaltura details
     */
    public static function get_kaltura_by_courses($courseids = array()) {
        
        global $CFG;
        $returnedinstance = [];
        $warnings = [];
        
        $params = self::validate_parameters(self::get_kaltura_by_courses_parameters(), ['courseids' => $courseids]);
        if (empty($params['courseids'])) {
            $params['courseids'] = array_keys(enrol_get_my_courses());
        }
        
        // Ensure there are courseids to loop through.
        if (!empty($params['courseids'])) {
            list($courses, $warnings) = \external_util::validate_courses($params['courseids']);
            // Get the kaltura option in this course, this function checks users visibility permissions.
            // We can avoid then additional validate_context calls.

            $instances = get_all_instances_in_courses("kalvidres", $courses);

            foreach ($instance as $instance) {
                $context = context_module::instance($instance->coursemodule);
                // Entry to return.
                $module = [];
                // First, we return information that any user can see in (or can deduce from) the web interface.
                $module['id'] = $instance->id;
                $module['coursemodule'] = $instance->coursemodule;
                $module['course'] = $instance->course;
                $module['name']  = external_format_string($instance->name, $context->id);
                $viewablefields = [];
                if (has_capability('mod/kalvidres:view', $context)) {
                    list($module['intro'], $module['introformat']) =
                        external_format_text($instance->intro, $instance->introformat, $context->id,'mod_kalvidres', 'intro', $instance->id);
                }
                $returnedinstances[] = $module;
            }
        }
        
        $result = [];
        $result['instances'] = $returnedinstance;
        $result['warnings'] = $warnings; 
        return $result;
    }
    /**
     * Describes the get_kaltura_by_courses return value.
     *
     * @return \external_single_structure
     */
    public static function get_kaltura_by_courses_returns() {
        return new \external_single_structure(
            ['instances' => new \external_multiple_structure(
                new \external_single_structure(
                    ['id' => new \external_value(PARAM_INT, ' id'),
                     'coursemodule' => new \external_value(PARAM_INT, 'Course module id'),
                     'course' => new \external_value(PARAM_INT, 'Course id'),
                     'name' => new \external_value(PARAM_RAW, 'kaltura name'),
                     'intro' => new \external_value(PARAM_RAW, 'The kaltura intro', VALUE_OPTIONAL),
                     'introformat' => new \external_format_value('intro', VALUE_OPTIONAL),
                    ]
                )),
             'warnings' => new \external_warnings(),
            ]
        );
    }
    
    /**
     * Describes the parameters for get_media_id.
     *
     * @return \external_function_parameters
     */
    public static function get_media_parameters() {
        return new \external_function_parameters (
            ['moduleid' => new \external_value(PARAM_INT, 'course instance id')]
        );
    }
    /**
     * Returns the kaltura video entity for processing and viewing the video
     *
     * @param array $moduleid of the current course
     * @return array the kaltura video details
     */
    public static function get_media($moduleid) {
        
        global $CFG, $DB, $SESSION;
        $returnedinstance = [];
        $warnings = [];
        
        $params = self::validate_parameters(self::get_media_parameters(), ['moduleid' => $moduleid]);
        if (empty($params['courseids'])) {
            $params['courseids'] = array_keys(enrol_get_my_courses());
        }
        
        if (! $cm = get_coursemodule_from_id('kalvidres', $moduleid)) {
            print_error('invalidcoursemodule');
        }

        if (! $course = $DB->get_record('course', array('id' => $cm->course))) {
            print_error('coursemisconf');
        }
        if (! $kalvidres = $DB->get_record('kalvidres', array("id"=>$cm->instance))) {
            print_error('invalidid', 'kalvidres');
        }
        
        //These might not be needed -> test without
        $kaltura = new kaltura_connection();
        $connection = $kaltura->get_connection(true, KALTURA_SESSION_LENGTH);
        $permission= $connection->permission;
        $session = $connection->session;
        
     
        //This is needed
        $partnerid = local_kaltura_get_partner_id();
        
        $result = [];
        $result['responses'] = $kalvidres;
        $result['pid'] = $partnerid;
        $result['warnings'] = $warnings; 
        return $result;
    }
    /**
     * Describes the get_media return value.
     *
     * @return \external_single_structure
     */
    public static function get_media_returns() {
        return new \external_single_structure(
            ['responses' =>  new \external_single_structure(
                    [
                     'id' => new \external_value(PARAM_RAW, ' id', VALUE_OPTIONAL),
                     'course' => new \external_value(PARAM_RAW, ' id', VALUE_OPTIONAL),
                     'name' => new \external_value(PARAM_RAW, 'Course module id', VALUE_OPTIONAL),
                     'intro' => new \external_value(PARAM_RAW, 'Course id', VALUE_OPTIONAL),
                     'introformat' => new \external_value(PARAM_RAW, 'Course id', VALUE_OPTIONAL),
                     'entry_id' => new \external_value(PARAM_RAW, 'kaltura name', VALUE_OPTIONAL),
                     'video_title' => new \external_value(PARAM_RAW, 'kaltura name', VALUE_OPTIONAL),
                     'uiconf_id' => new \external_value(PARAM_RAW, 'kaltura name', VALUE_OPTIONAL),
                     'widescreen' => new \external_value(PARAM_RAW, 'Course id', VALUE_OPTIONAL),
                     'height' => new \external_value(PARAM_RAW, 'Course id', VALUE_OPTIONAL),
                     'width' => new \external_value(PARAM_RAW, 'Course id', VALUE_OPTIONAL),
                     'timemodified' => new \external_value(PARAM_RAW, 'Course id', VALUE_OPTIONAL),
                     'timecreated' => new \external_value(PARAM_RAW, 'Course id', VALUE_OPTIONAL),
                    ]
                ),
             'pid' => new \external_value(PARAM_RAW, ' id', VALUE_OPTIONAL),
             'warnings' => new \external_warnings(),
            ]
        );
    }
    
      /**
     * Describes the parameters for get_video_id.
     *
     * @return \external_function_parameters
     */
    public static function get_page_content_parameters() {
        return new \external_function_parameters (
            ['moduleid' => new \external_value(PARAM_INT, 'course instance id')]
        );
    }
    /**
     * Returns the kaltura video entity for processing and viewing the video
     *
     * @param array $courseid of the current course
     * @return page content html
     */
    public static function get_page_content($moduleid) {
        
        global $CFG, $DB, $SESSION;
        $returnedinstance = [];
        $warnings = [];
        
        $params = self::validate_parameters(self::get_page_content_parameters(), ['moduleid' => $moduleid]);
        if (empty($params['courseids'])) {
            $params['courseids'] = array_keys(enrol_get_my_courses());
        }
        
        if (!$cm = get_coursemodule_from_id('page', $moduleid)) {
            print_error('invalidcoursemodule');
        }
        $page = $DB->get_record('page', array('id'=>$cm->instance), '*', MUST_EXIST);
        
        $result = [];
        $result['responses'] = $page->content;
        $result['warnings'] = $warnings; 
        return $result;
    }
    /**
     * Describes the get_page_content return value.
     *
     * @return \external_single_structure
     */
    public static function get_page_content_returns() {
        return new \external_single_structure(
            ['responses' => new \external_value(PARAM_RAW, ' html text of page', VALUE_OPTIONAL),
             'warnings' => new \external_warnings(),
            ]
        );
    }
    
    /**
     * Describes the parameters for get_uiconfid
     *
     * @return \external_function_parameters
     */
    public static function get_uiconfid_parameters() {
        return new \external_function_parameters (
            ['moduleid' => new \external_value(PARAM_INT, 'course instance id')]
        );
    }
    /**
     * Returns the kaltura video player id for processing and viewing the video
     *
     * @param array $module of the current course
     * @return uiconfid int
     */
    public static function get_uiconfid($moduleid) {
        
        global $CFG, $DB, $SESSION;
        $returnedinstance = [];
        $warnings = [];
        
        $params = self::validate_parameters(self::get_uiconfid_parameters(), ['moduleid' => $moduleid]);
        if (empty($params['courseids'])) {
            $params['courseids'] = array_keys(enrol_get_my_courses());
        }
        
        $uiconf_id = local_kaltura_get_player_uiconf('player_filter');
        
        $result = [];
        $result['responses'] = $uiconf_id;
        $result['warnings'] = $warnings; 
        return $result;
    }
    /**
     * Describes the get_page_content return value.
     *
     * @return \external_single_structure
     */
    public static function get_uiconfid_returns() {
        return new \external_single_structure(
            ['responses' => new \external_value(PARAM_INT, ' id of kaltura player'),
             'warnings' => new \external_warnings(),
            ]
        );
    }
   
          /**
     * Describes the parameters for get_book_chapters
     *
     * @return \external_function_parameters
     */
    public static function get_book_chapters_parameters() {
        return new \external_function_parameters (
            ['moduleid' => new \external_value(PARAM_INT, 'course instance id'),
             'chapterid' => new \external_value(PARAM_INT, 'current chapter id')]
        );
    }
    /**
     * Returns the current chapter html for app processing
     *
     * @param array $moduleid of the current course
     * @param array $chapterid of the current chapter
     * @return html of chapter content
     */
    public static function get_book_chapters($moduleid, $chapterid) {
        
        global $CFG, $DB, $SESSION;
        $returnedinstance = [];
        $warnings = [];
        $chapter=[];
        
        $params = self::validate_parameters(self::get_book_chapters_parameters(), ['moduleid' => $moduleid,'chapterid' => $chapterid]);
        if (empty($params['courseids'])) {
            $params['courseids'] = array_keys(enrol_get_my_courses());
        }
        
        $cm = get_coursemodule_from_id('book', $moduleid, 0, false, MUST_EXIST);
        $course = $DB->get_record('course', array('id'=>$cm->course), '*', MUST_EXIST);
        $book = $DB->get_record('book', array('id'=>$cm->instance), '*', MUST_EXIST);
        $context = get_context_instance(CONTEXT_MODULE, $cm->id);
        
        
        // Chapter doesnt exist or it is hidden for students
        if ((!$chapter = $DB->get_record('book_chapters', array('id' => $chapterid, 'bookid' => $book->id))) or ($chapter->hidden and !$viewhidden)) {
            print_error('errorchapter', 'mod_book', $courseurl);
        }
        
        $result = [];
        $result['responses'] = $chapter->content;
        $result['warnings'] = $warnings; 
        return $result;
    }
    /**   
     * Describes the get_book_chapters return value.
     *
     * @return \external_single_structure
     */
    public static function get_book_chapters_returns() {
        return new \external_single_structure(
            ['responses' => new \external_value(PARAM_RAW, ' html text of page', VALUE_OPTIONAL),
             'warnings' => new \external_warnings(),
            ]
        );
    }
}