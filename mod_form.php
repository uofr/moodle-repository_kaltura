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

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/course/moodleform_mod.php');

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
        global $PAGE;

        $mform =& $this->_form;

        $mform->addElement('hidden', 'entry_id', '', ['id' => 'entry_id']);
        $mform->setType('entry_id', PARAM_TEXT);
        $attr = array('id' => 'video_title');
        $mform->addElement('hidden', 'video_title', '', $attr);
        $mform->setType('video_title', PARAM_TEXT);

        $mform->addElement('header', 'general', get_string('general', 'form'));

        $mform->addElement('text', 'name', get_string('name', 'kalvidres'), array('size'=>'64'));
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');

        $this->standard_intro_elements(get_string('description', 'assign'));

        $mform->addElement('header', 'video', get_string('video_hdr', 'mod_kalvidres'));
        if ($this->current->video_title) {
            $mform->addElement('static', '', '', '<h5 data-region="selected-entry-header">' . get_string('selected_entry', 'local_kaltura', $this->current->video_title) . "</h5>");
        } else {
            $mform->addElement('static', '', '', '<h5 data-region="selected-entry-header">' . get_string('no_selected_entry', 'local_kaltura') . "</h5>");
        }
        $thumbnail_markup = $this->get_thumbnail_markup($this->current->entry_id);
        $mform->addElement('static', 'add_media_thumb', '&nbsp;', $thumbnail_markup);
        $buttongroup = [];
        $buttongroup[] =& $mform->createElement('button', 'add_media', get_string('media_select', 'mod_kalvidres'));
        $buttongroup[] =& $mform->createElement('button', 'upload_media', get_string('media_upload', 'mod_kalvidres'), ['data-action' => 'upload', 'data-upload-type' => 'media']);
        $buttongroup[] =& $mform->createElement('button', 'record_media', get_string('webcam_upload', 'mod_kalvidres'), ['data-action' => 'upload', 'data-upload-type' => 'record']);
        $mform->addGroup($buttongroup, 'media_group', '&nbsp;', '&nbsp;', false);

        $mform->addElement('select', 'showpreview', get_string('showpreview', 'mod_kalvidres'), ['No', 'Yes']);

        $mform->addElement('header', 'access', get_string('access_hdr', 'kalvidres'));
        $mform->addElement('select', 'internal', get_string('internal', 'mod_kalvidres'), ['No', 'Yes']);

        $this->standard_coursemodule_elements();

        $this->add_action_buttons();

        $PAGE->requires->js_amd_inline("
            require([
                'core/modal_factory',
                'core/pubsub',
                'local_kaltura/modal_video_picker',
                'local_mymedia/modal_upload',
                'local_mymedia/mymedia_events',
                'local_mymedia/mymedia_ajax'
            ],
            function(
                ModalFactory,
                PubSub,
                ModalVideoPicker,
                ModalUpload,
                MyMediaEvents,
                MyMediaAjax
            ){
                Promise.all([
                    ModalFactory.create({type: ModalVideoPicker.getType()}),
                    ModalFactory.create({type: ModalUpload.getType()})
                ])
                .then(modals => {
                    const [modal, modalUpload] = modals;

                    modal.contextid = {$PAGE->context->id};
                    $('#id_add_media').on('click', () => {
                        modal.show();
                    });

                    $('[data-action=\"upload\"').on('click', (e) => {
                        modalUpload.renderUploadForm({$PAGE->context->id}, $(e.currentTarget).attr('data-upload-type'));
                    });

                    PubSub.subscribe(MyMediaEvents.uploadComplete, (entryid) => {
                        MyMediaAjax.getEntry({$PAGE->context->id}, entryid)
                            .then((entry) => {
                                console.log(entry);
                                $('#entry_id').val(entry.id);
                                $('#id_name').val(entry.name);
                                $('#media_thumbnail').attr('src', entry.thumbnailUrl);
                                $('#video_title').val(entry.name);
                                getString('selected_entry', 'local_kaltura', entry.name)
                                .then(string => $('[data-region=\"selected-entry-header\"]').text(string));
                            });
                    });
                });
            });
        ");
    }

    /**
     * This function return HTML markup to display thumbnail.
     * @param string $entry_id - id of media entry.
     * @return string - HTML markup to display thumbnail.
     */
    private function get_thumbnail_markup($entry_id) {
        global $CFG;

        $source = '';

        $attr = array('id' => 'notification',
                      'class' => 'notification',
                      'tabindex' => '-1');
        $output = html_writer::tag('div', '', $attr);

        $source = $CFG->wwwroot . '/local/kaltura/pix/vidThumb.png';;
        $alt    = get_string('media_select', 'kalvidres');
        $title  = get_string('media_select', 'kalvidres');

        if (!empty($entry_id)) {
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
                      'title' => $title,
                      'class' => 'kaltura-selected-thumb');

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
