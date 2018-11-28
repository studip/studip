import Dialog from './dialog.js';

const Folders = {
    openAddFoldersWindow: function(folder_id, range_id) {
        Dialog.fromURL(
            STUDIP.ABSOLUTE_URI_STUDIP +
                'dispatch.php/folder/new?rangeId=' +
                range_id +
                '&parent_folder_id=' +
                folder_id +
                '&js=1',
            {
                title: 'Datei hinzufügen'.toLocaleString()
            }
        );
    },

    sendNewFolderForm: function() {
        var new_folder_form = jQuery('#new_folder_form');

        //get form fields to check if the required fields are set:
        var folder_name = jQuery(new_folder_form)
            .find('input[name="name"]')
            .val();
        var folder_type = jQuery(new_folder_form)
            .find('input[name="folder_type"]')
            .val();
        var parent_folder_id = jQuery(new_folder_form)
            .find('input[name="parent_folder_id"]')
            .val();

        if (folder_name && folder_type && parent_folder_id) {
            console.log('data present!');
            jQuery.ajax({
                method: 'POST',
                url: STUDIP.ABSOLUTE_URI_STUDIP + 'dispatch.php/folder/new',
                data: new_folder_form.serialize(),
                cache: false,
                success: function(data) {
                    Folders.updateFolderListEntry(data.folder_id, data.tr);
                    Dialog.close();
                }
            });
        } else {
            console.log('data missing!');
        }
    },

    updateFolderListEntry: function(folder_id, html, delay) {
        //updates the folder entry in the folder list
        var documents_table = jQuery('.documents[data-folder_id]');

        if (jQuery('#row_folder_' + folder_id).length > 0) {
            //row with folder-ID was found:
            jQuery('#row_folder_' + folder_id).replaceWith(html);
        } else {
            jQuery(documents_table).append(html);
        }
    },

    removeFolderListEntry: function(folder_id) {
        //removes a row from the folder list:
        if (jQuery('#row_folder_' + folder_id).length > 0) {
            //row with folder-ID was found:
            jQuery('#row_folder_' + folder_id).remove();
        }
    },

    delete: function(folder_id) {
        if (!folder_id) {
            return false;
        }

        jQuery.ajax({
            method: 'GET',
            url: STUDIP.ABSOLUTE_URI_STUDIP + 'dispatch.php/folder/delete/' + folder_id,
            data: null,
            cache: false,
            success: function(data) {
                if ($(data).hasClass('messagebox_success')) {
                    Folders.removeFolderListEntry(folder_id);
                }
                $('#layout_content').prepend(data);
            }
        });
    }
};

export default Folders;
