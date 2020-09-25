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

$id = required_param('id', PARAM_INT);

$cm = get_coursemodule_from_id('kalvidres', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
$kalvidres = $DB->get_record('kalvidres', array("id"=>$cm->instance), '*', MUST_EXIST);

require_login($course, true, $cm);

$PAGE->set_url('/mod/kalvidres/view.php', array('id' => $id));
$PAGE->set_title(format_string($kalvidres->name));
$url = $CFG->wwwroot . '/mod/kalvidres/trigger.php';
$PAGE->requires->js_call_amd('mod_kalvidres/playtrigger', 'init', array($url, $id));

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

local_kaltura_validate_entry_id($kalvidres);

echo $renderer->embed_media($kalvidres);

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
