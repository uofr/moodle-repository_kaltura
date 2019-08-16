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
 * Kaltura video resource
 *
 * @package    mod
 * @subpackage kalvidres
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once(dirname(dirname(dirname(__FILE__))) . '/local/kaltura/locallib.php');

defined('MOODLE_INTERNAL') || die();

header('Access-Control-Allow-Origin: *');
header('Cache-Control: no-cache');

$id = required_param('id', PARAM_INT);
$doAutoPlay = optional_param('autoPlay', 0, PARAM_INT);

$cm = get_coursemodule_from_id('kalvidres', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
$kalvidres = $DB->get_record('kalvidres', array("id"=>$cm->instance), '*', MUST_EXIST);

require_login($course, true, $cm);

$PAGE->set_url('/mod/kalvidres/view.php', array('id' => $id, 'autoPlay' => $doAutoPlay));
$PAGE->set_title(format_string($kalvidres->name));
$url = $CFG->wwwroot . '/mod/kalvidres/trigger.php';
$PAGE->requires->js_call_amd('mod_kalvidres/playtrigger', 'init', array($url, $id));

// Try connection.
$kaltura = new kaltura_connection();
$connection = $kaltura->get_connection(true, KALTURA_SESSION_LENGTH);

if ($connection) {
    if (local_kaltura_has_mobile_flavor_enabled() && local_kaltura_get_enable_html5()) {
        $uiconf_id = local_kaltura_get_player_uiconf('player_resource');
        $url = new moodle_url(local_kaltura_htm5_javascript_url($uiconf_id));
        $PAGE->requires->js($url, true);
        $url = new moodle_url('/local/kaltura/js/frameapi.js');
        $PAGE->requires->js($url, true);
    }
}

$event = \mod_kalvidres\event\media_resource_viewed::create(array(
    'objectid' => $kalvidres->id,
    'context' => context_module::instance($cm->id)
));
$event->trigger();

$completion = new completion_info($course);
$completion->set_module_viewed($cm);

echo $OUTPUT->header();
echo $OUTPUT->heading($kalvidres->name);

$renderer = $PAGE->get_renderer('mod_kalvidres');

$clientipaddress = local_kaltura_get_client_ipaddress(true);
if ($kalvidres->internal == 1 and !local_kaltura_check_internal($clientipaddress)) {
    print_error('invalid_ipaddress', 'mod_kalvidres');
}
if (!$connection) {
    print_error('conn_failed_alt', 'local_kaltura');
}

local_kaltura_validate_entry_id($kalvidres);

try {
    $media = $connection->media->get($kalvidres->entry_id);
    if ($media !== null) {
        $enabled = local_kaltura_kaltura_repository_enabled();
        if ($enabled) {
            require_once($CFG->dirroot.'/repository/kaltura/locallib.php');
            $category = repository_kaltura_create_course_category($connection, $course->id);
        }
        if (!empty($category) && $enabled) {
            repository_kaltura_add_video_course_reference($connection, $course->id, array($kalvidres->entry_id));
        }
        echo $renderer->embed_media($kalvidres);
    }
}
catch (Exception $ex) {
    echo '<div class="alert alert-warning"><p>';
    echo 'Entry Id <b>' . $kalvidres->entry_id. '</b> could not be found.';
    echo '</p></div>';
}

$admin = is_siteadmin();
$teacher = false;
$coursecontext = context_course::instance($COURSE->id);
$roles = get_user_roles($coursecontext, $USER->id);
foreach ($roles as $role) {
    if ($role->shortname == 'teacher' || $role->shortname == 'editingteacher') {
        $teacher = true;
    }
}

if ($teacher == true || $admin == true) {
    echo $renderer->create_access_link_markup($cm->id);
}

echo $OUTPUT->footer();
