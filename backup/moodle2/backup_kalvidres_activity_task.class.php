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
 * Backup activity script.
 * @package   mod_kalvidres
 * @copyright (C) 2010 onwards Eloy Lafuente (stronk7) {@link http://stronk7.com}
 * @copyright (C) 2016-2017 Yamaguchi University <info-cc@ml.cc.yamaguchi-u.ac.jp>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/kalvidres/backup/moodle2/backup_kalvidres_stepslib.php');

/**
 * kalvidres backup task.
 * @package    mod_kalvidres
 * @copyright  (C) 2010 onwards Eloy Lafuente (stronk7) {@link http://stronk7.com}
 * @copyright  (C) 2016-2017 Yamaguchi University <info-cc@ml.cc.yamaguchi-u.ac.jp>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class backup_kalvidres_activity_task extends backup_activity_task {
	
    protected function define_my_settings() {
        // No particular settings for this activity.
    }
	
    /**
     * Define (add) particular steps this activity can have
     */
    protected function define_my_steps() {
        $this->add_step(new backup_kalvidres_activity_structure_step('kalvidres_structure', 'kalvidres.xml'));
    }

    /**
     * Code the transformations to perform in the activity in
     * order to get transportable (encoded) links
     * @param string $content
     * @return string
     */
    static public function encode_content_links($content) {
        global $CFG;

        $base = preg_quote($CFG->wwwroot, "/");

        // Link to the list of kalvidres.
        $search = "/(". $base . "\/mod\/kalvidres\/index.php\?id\=)([0-9]+)/";
        $content = preg_replace($search, '$@KALVIDRESINDEX*$2@$', $content);

        // Link to kalvidres view by moduleid.
        $search = "/(" . $base . "\/mod\/kalvidres\/view.php\?id\=)([0-9]+)/";
        $content = preg_replace($search, '$@KALVIDRESVIEWBYID*$2@$', $content);

        return $content;
    }
}
