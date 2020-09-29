import $ from 'jquery';

import Notification from 'core/notification';
import {subscribe} from 'core/pubsub';
import {get_string as getString} from 'core/str';

import ModalFactory from 'core/modal_factory';
import ModalVideoPicker from 'local_kaltura/modal_video_picker';
import ModalVideoPickerEvents from 'local_kaltura/modal_video_picker_events';

const SELECTORS = {
    OPEN_VIDEO_PICKER: '#id_add_media',
    ENTRY_ID: '#entry_id',
    VIDEO_TITLE: '#video_title',
    ENTRY_THUMBNAIL: '#media_thumbnail',
    SELECTED_ENTRY_HEADER: '[data-region="selected-entry-header"]'
};

export const init = async (contextid, entryid, entryname, entrythumbnail) => {
    try {
        const modal = await ModalFactory.create({type: ModalVideoPicker.getType()});
        modal.contextid = contextid;
        modal.selectedEntryId = entryid;
        modal.selectedEntryName = entryname;
        modal.selectedEntryThumbnail = entrythumbnail;
        registerEventListeners(modal);
    } catch(error) {
        Notification.exception(error);
    }
};

const registerEventListeners = (modal) => {

    $(SELECTORS.OPEN_VIDEO_PICKER).on('click', () => {
        modal.show();
    });

    subscribe(ModalVideoPickerEvents.entrySelected, async (entry) => {
        $(SELECTORS.ENTRY_ID).val(entry.entryId);
        $(SELECTORS.ENTRY_THUMBNAIL).attr('src', entry.entryThumbnail);
        $(SELECTORS.VIDEO_TITLE).val(entry.entryName);
        const selectedEntryText = await getString('selected_entry', 'local_kaltura', entry.entryName);
        $(SELECTORS.SELECTED_ENTRY_HEADER).text(selectedEntryText);
    });

};