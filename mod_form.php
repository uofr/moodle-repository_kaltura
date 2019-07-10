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
 * The main mod_kalvidres configuration form
 *
 * It uses the standard core Moodle formslib. For more info about them, please
 * visit: http://docs.moodle.org/en/Development:lib/formslib.php
 *
 * @package    mod_kalvidres
 * @copyright  (C) 2016-2017 Yamaguchi University <gh-cc@mlex.cc.yamaguchi-u.ac.jp>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once(dirname(dirname(dirname(__FILE__))) . '/local/kaltura/locallib.php');

defined('MOODLE_INTERNAL') || die();

header('Access-Control-Allow-Origin: *');
header('Cache-Control: no-cache');

require_once($CFG->dirroot . '/course/moodleform_mod.php');

require_login();

/**
 * class of Kaltura Media resource setting form.
 * @package mod_kalvidres
 * @copyright  (C) 2016-2017 Yamaguchi University <info-cc@ml.cc.yamaguchi-u.ac.jp>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_kalvidres_mod_form extends moodleform_mod {

    /** @var default player is set. */
    protected $_default_player = false;

    /**
     * This function outputs a resource information form.
     */
    protected function definition() {
        global $CFG, $PAGE;

        $kaltura = new kaltura_connection();
        $connection = $kaltura->get_connection(true, KALTURA_SESSION_LENGTH);

        $loginsession = '';

        if (!empty($connection)) {
            $loginsession = $connection->getKs();
        }

        /*
         * This line is needed to avoid a PHP warning when the form is submitted.
         * Because this value is set as the default for one of the formslib elements.
         */
        $uiconf_id = '';

        // Check if connection to Kaltura can be established.
        if ($connection) {
            $uiconf_id = local_kaltura_get_player_uiconf('player_resource');
        }

        if (local_kaltura_has_mobile_flavor_enabled() && local_kaltura_get_enable_html5()) {
            $url = new moodle_url(local_kaltura_htm5_javascript_url($uiconf_id));
            $PAGE->requires->js($url, true);
        }

        $mform =& $this->_form;

        /* Hidden fields */
        $attr = array('id' => 'entry_id');
        $mform->addElement('hidden', 'entry_id', '', $attr);
        $mform->setType('entry_id', PARAM_NOTAGS);

        $attr = array('id' => 'video_title');
        $mform->addElement('hidden', 'video_title', '', $attr);
        $mform->setType('video_title', PARAM_TEXT);

        $attr = array('id' => 'uiconf_id');
        $mform->addElement('hidden', 'uiconf_id', '', $attr);
        $mform->setDefault('uiconf_id', $uiconf_id);
        $mform->setType('uiconf_id', PARAM_INT);

        $attr = array('id' => 'widescreen');
        $mform->addElement('hidden', 'widescreen', '', $attr);
        $mform->setDefault('widescreen', 0);
        $mform->setType('widescreen', PARAM_INT);

        $attr = array('id' => 'height');
        $mform->addElement('hidden', 'height', '', $attr);
        $mform->setDefault('height', '365');
        $mform->setType('height', PARAM_TEXT);

        $attr = array('id' => 'width');
        $mform->addElement('hidden', 'width', '', $attr);
        $mform->setDefault('width', '400');
        $mform->setType('width', PARAM_TEXT);

        $mform->addElement('header', 'general', get_string('general', 'form'));

        $mform->addElement('text', 'name', get_string('name', 'kalvidres'), array('size'=>'64'));

        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        }
        else {
            $mform->setType('name', PARAM_CLEANHTML);
        }

        $mform->addRule('name', null, 'required', null, 'client');

        $this->standard_intro_elements(get_string('description', 'assign'));

        if (local_kaltura_login(true, '')) {
            $mform->addElement('header', 'video', get_string('video_hdr', 'kalvidres'));

			if (empty($this->current->entry_id)) {
                $this->add_media_definition($mform, null);
            }
            else {
                $this->add_media_definition($mform, $this->current->entry_id);
            }
        }
        else {
            $mform->addElement('static', 'connection_fail', get_string('conn_failed_alt', 'local_kaltura'));
        }
        $this->add_showpreview_option($mform);

        $mform->addElement('header', 'access', get_string('access_hdr', 'kalvidres'));
        $this->add_access_definition($mform);
        $this->standard_coursemodule_elements();
        $this->add_action_buttons();
    }

    /**
     * This function add "Access" part to module form.
     * @param object $mform - form object.
     */
    private function add_access_definition($mform) {
        $accessgroup = array();
        $options = array('0' => 'No', '1' => 'Yes');
        $select = $mform->addElement('select', 'internal', get_string('internal', 'mod_kalvidres'), $options);
        $select->setSelected('0');
        $accessgroup[] =& $select;
    }

  /**
   * This function add "show preview" part to module form.
   * @param object $mform - form object.
   */
  private function add_showpreview_option($mform) {
      $previewgroup = array();
      $options = array('0' => 'No', '1' => 'Yes');
      $select = $mform->addElement('select', 'showpreview', get_string('showpreview', 'mod_kalvidres'), $options);
      $select->setSelected('0');
      $previewgroup[] =& $select;
  }

    /**
     * This function add "Media" part to module form.
     * @param object $mform - form object.
     * @param string $entry_id - id of media entry.
     */
    private function add_media_definition($mform, $entry_id) {

        $thumbnail = $this->get_thumbnail_markup($entry_id);
        $mform->addElement('static', 'add_media_thumb', '&nbsp;', $thumbnail);

		$mediagrouplabel = (!empty($entry_id)) ? 'replace_media' : 'media_select';
        $mediagroup = array();
        $mediagroup[] =& $mform->createElement('button', 'add_media', get_string($mediagrouplabel, 'kalvidres'));
        $mediagroup[] =& $mform->createElement('button', 'upload_media', get_string('upload', 'mod_kalvidres'));
        $mediagroup[] =& $mform->createElement('button', 'record_media', get_string('record', 'mod_kalvidres'));

        $mform->addGroup($mediagroup, 'media_group', '&nbsp;', '&nbsp;', false);
    }

    /**
     * This function return HTML markup to display thumbnail.
     * @param string $entry_id - id of media entry.
     * @return string - HTML markup to display thumbnail.
     */
    private function get_thumbnail_markup($entry_id) {
        global $CFG;

        $source = '';

        /*
         * tabindex -1 is required in order for the focus event to be capture
         * amongst all browsers.
         */
        $attr = array('id' => 'notification',
                      'class' => 'notification',
                      'tabindex' => '-1');
        $output = html_writer::tag('div', '', $attr);

        $source = $CFG->wwwroot . '/local/kaltura/pix/vidThumb.png';;
        $alt    = get_string('media_select', 'kalvidres');
        $title  = get_string('media_select', 'kalvidres');

        if (!empty($entry_id)) {
			$entries = new KalturaStaticEntries();
            $entryobj = KalturaStaticEntries::getEntry($entry_id, null, false);
            if (isset($entryobj->thumbnailUrl)) {
                $source = $entryobj->thumbnailUrl;
                $alt    = $entryobj->name;
                $title  = $entryobj->name;
            }

        }

        $attr = array('id' => 'media_thumbnail',
                      'src' => $source,
                      'alt' => $alt,
                      'title' => $title);

        $output .= html_writer::empty_tag('img', $attr);

        return $output;

    }

    /**
     * This function changes form information after media selected.
     */
    public function definition_after_data() {
        $mform = $this->_form;

        if (!empty($mform->_defaultValues['entry_id'])) {
            foreach ($mform->_elements as $key => $data) {

                if ($data instanceof MoodleQuickForm_group) {

                    foreach ($data->_elements as $key2 => $data2) {
                        if (0 == strcmp('media_select', $data2->_attributes['name'])) {
                            $mform->_elements[$key]->_elements[$key2]->setValue(get_string('replace_media', 'kalvidres'));
                            break;
                        }

                        if (0 == strcmp('pres_info', $data2->_attributes['name'])) {
                            $mform->_elements[$key]->_elements[$key2]->setValue('');
                            break;
                        }
                    }
                }
            }
        }
    }

}
