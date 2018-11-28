/* ------------------------------------------------------------------------
 * Standard dialogs for confirmation or messages
 * ------------------------------------------------------------------------ */

const Dialogs = {
    showConfirmDialog: function(question, confirm) {
        // compile template
        var getTemplate = _.memoize(function(name) {
            return _.template(jQuery('#' + name).html());
        });

        var confirmDialog = getTemplate('confirm_dialog');
        $('body').append(
            confirmDialog({
                question: question,
                confirm: confirm
            })
        );

        return false;
    },

    closeConfirmDialog: function() {
        $('div.modaloverlay').remove();
    }
};

export default Dialogs;
