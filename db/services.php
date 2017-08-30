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
defined('MOODLE_INTERNAL') || die;

$functions = array(
    'mod_kalvidres_get_kaltura_by_courses' => array(
        'classname'     => 'mod_kalvidres\external',
        'methodname'    => 'get_kaltura_by_courses',
        'description'   => 'Returns a list of kaltura instances in a provided set of courses, if no courses are provided then all the kaltura instances the user has access to will be returned.',
        'type'          => 'read',
        'capabilities'  => 'mod/kalvidres:view',
        'services'      => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
    ),
     'mod_kalvidres_get_media' => array(
        'classname'     => 'mod_kalvidres\external',
        'methodname'    => 'get_media',
        'description'   => 'Returns kaltura video object with all details needed to create video instance for viewing',
        'type'          => 'read',
        'capabilities'  => 'mod/kalvidres:view',
        'services'      => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
    ),
      'mod_kalvidres_get_page_content' => array(
        'classname'     => 'mod_kalvidres\external',
        'methodname'    => 'get_page_content',
        'description'   => 'Returns content of page to check for video embeds',
        'type'          => 'read',
        'capabilities'  => 'mod/kalvidres:view',
        'services'      => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
    ),
    'mod_kalvidres_get_video_info' => array(
        'classname'     => 'mod_kalvidres\external',
        'methodname'    => 'get_video_info',
        'description'   => 'Returns kaltura player id, and partner id',
        'type'          => 'read',
        'capabilities'  => 'mod/kalvidres:view',
        'services'      => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
    ),
     'mod_kalvidres_get_book_chapters' => array(
        'classname'     => 'mod_kalvidres\external',
        'methodname'    => 'get_book_chapters',
        'description'   => 'Returns chapters of a book',
        'type'          => 'read',
        'capabilities'  => 'mod/kalvidres:view',
        'services'      => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
    ),
      
);
