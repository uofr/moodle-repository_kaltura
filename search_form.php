<?php

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
 * Kaltura video assignment grade preferences form
 *
 * @package    Repository
 * @subpackage Kaltura
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


/**
 * This function prints the search form
 *
 * @param object - object 'id' = repo id, 'context'->'id' = context id
 *
 * @return HTML markup
 */
function print_search_form($data) {

    $html = '';

    // Hidden field repo instance id
    $attributes = array('type'=>'hidden',
                        'name' => 'repo_id',
                        'value' => $data->id);
    $html .= html_writer::empty_tag('input', $attributes);

    // hidden field context id
    $attributes['name'] = 'ctx_id';
    $attributes['value'] = $data->context->id;
    $html .= html_writer::empty_tag('input', $attributes);

    // hidden field session key
    $attributes['name'] = 'sesskey';
    $attributes['value'] = sesskey();
    $html .= html_writer::empty_tag('input', $attributes);

    // label search name
    $param = array('for' => 'label_search_name');
    $title = get_string('search_name', 'repository_kaltura');
    $html .= html_writer::tag('label', $title, $param);
    $html .= html_writer::empty_tag('br');

    // text field search name
    $attributes['type'] = 'text';
    $attributes['name'] = 's';
    $attributes['value'] = '';
    $attributes['title'] = $title;
    $html .= html_writer::empty_tag('input', $attributes);
    $html .= html_writer::empty_tag('br');
    $html .= html_writer::empty_tag('br');

    // label search tags
    $param = array('for' => 'label_search_tags');
    $title = get_string('search_tags', 'repository_kaltura');
    $html .= html_writer::tag('label', $title, $param);
    $html .= html_writer::empty_tag('br');

    // textfield search tags
    $attributes['type'] = 'text';
    $attributes['name'] = 't';
    $attributes['value'] = '';
    $attributes['title'] = $title;
    $html .= html_writer::empty_tag('input', $attributes);
    $html .= html_writer::empty_tag('br');
    $html .= html_writer::empty_tag('br');

    // label course name filter
    $param = array('for' => 'label_course_filter');
    $title = get_string('course_filter', 'repository_kaltura');
    $html .= html_writer::tag('label', $title, $param);
    $html .= html_writer::empty_tag('br');

    // select course name filter options
    $options = array('contains' => get_string('contains', 'repository_kaltura'),
                     'equals' => get_string('equals', 'repository_kaltura'),
                     'startswith' => get_string('startswith', 'repository_kaltura'),
                     'endswith' => get_string('endswith', 'repository_kaltura')
                    );
    $html .= html_writer::select($options, 'course_with', '', false, array('title' => get_string('course_filter_select_title', 'repository_kaltura')));

    $html .= '&nbsp';

    // text field course name filter
    $attributes['type'] = 'text';
    $attributes['name'] = 'c';
    $attributes['value'] = '';
    $attributes['id'] = 'course_name_filter';
    $attributes['title'] = $title;
    $html .= html_writer::empty_tag('input', $attributes);
    $html .= html_writer::empty_tag('br');
    $html .= html_writer::empty_tag('br');

    // label search own videos
//    $param = array('for' => 'label_own_video_filter');
//    $html .= html_writer::tag('label', get_string('own_videos_filter', 'repository_kaltura'), $param);
//
//    $html .= '&nbsp';

    // checkbox search own videos
//    $attributes['type'] = 'checkbox';
//    $attributes['name'] = 'own';
//    $attributes['value'] = 'search_own';
//    $html .= html_writer::empty_tag('input', $attributes);

    return $html;
}

/**
 * Prints required hidden element for users having the course video visibility
 * capability
 *
 * @param nothing
 * @return HTML markup
 */
function print_used_selection() {
    $html = '';

    $param = array('for' => 'label_shared_or_used');
    $title = get_string('search_shared_or_used', 'repository_kaltura');
    $html .= html_writer::tag('label', $title, $param);

    $html .= '&nbsp';

    // select type of search to perform
    $options = array('used'       => get_string('search_used', 'repository_kaltura'),
                     'own'          => get_string('search_own_upload', 'repository_kaltura')
                    );

    $javascript_event = 'var share_select = document.getElementById("menushared_used");
                         if (1 == share_select.selectedIndex) {
                             document.getElementById("course_name_filter").disabled = true;
                             document.getElementById("menucourse_with").disabled = true;
                         } else {
                             document.getElementById("course_name_filter").disabled = false;
                             document.getElementById("menucourse_with").disabled = false;
                         }
                        ';

    $html .= html_writer::select($options, 'shared_used', 'used', false, array('onclick' => $javascript_event, 'title' => $title));

    return $html;

    // hidden flag for shared or used search
//    $attributes = array('type'=>'hidden',
//                        'name' => 'shared_used',
//                        'value' => 'used');
//    $html .= html_writer::empty_tag('input', $attributes);
//
//    return $html;
}

/**
 * Prints a drop down selection for users having both the course video
 * visibility and shared video visibility capabilities
 *
 * @param nothing
 * @return HTML markup
 */
function print_shared_used_selection() {
    $html = '';

    // label type of search
    $param = array('for' => 'label_shared_or_used');
    $title = get_string('search_shared_or_used', 'repository_kaltura');
    $html .= html_writer::tag('label', $title, $param);

    $html .= '&nbsp';

    // select type of search to perform
    $options = array('shared'       => get_string('search_shared', 'repository_kaltura'),
                     'site_shared'  => get_string('search_site_shared', 'repository_kaltura'),
                     'used'         => get_string('search_used', 'repository_kaltura'),
                     'own'          => get_string('search_own_upload', 'repository_kaltura')
                    );
    $javascript_event = 'var share_select = document.getElementById("menushared_used");
                         if (1 == share_select.selectedIndex || 3 == share_select.selectedIndex) {
                             document.getElementById("course_name_filter").disabled = true;
                             document.getElementById("menucourse_with").disabled = true;
                         } else {
                             document.getElementById("course_name_filter").disabled = false;
                             document.getElementById("menucourse_with").disabled = false;
                         }
                        ';
    $html .= html_writer::select($options, 'shared_used', 'used', false, array('onclick' => $javascript_event, 'title' => $title));

    return $html;
}

/**
 * Prints a drop down selection for users having the shared video visibility
 * capability
 *
 * @param nothing
 *
 * @return HTML markup
 */
function print_shared_selection() {
    $html = '';

    $param = array('for' => 'label_shared_or_used');
    $title = get_string('search_shared_or_used', 'repository_kaltura');
    $html .= html_writer::tag('label', $title, $param);

    $html .= '&nbsp';

    // select type of search to perform
    $options = array('shared'       => get_string('search_shared', 'repository_kaltura'),
                     'site_shared'  => get_string('search_site_shared', 'repository_kaltura'),
                     'own'          => get_string('search_own_upload', 'repository_kaltura')
                    );

    $javascript_event = 'var share_select = document.getElementById("menushared_used");
                         if (1 == share_select.selectedIndex || 2 == share_select.selectedIndex) {
                             document.getElementById("course_name_filter").disabled = true;
                             document.getElementById("menucourse_with").disabled = true;
                         } else {
                             document.getElementById("course_name_filter").disabled = false;
                             document.getElementById("menucourse_with").disabled = false;
                         }
                        ';

    $html .= html_writer::select($options, 'shared_used', 'used', false, array('onclick' => $javascript_event, 'title' => $title));

    return $html;
}