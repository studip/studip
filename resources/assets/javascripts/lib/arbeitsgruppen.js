/* ------------------------------------------------------------------------
 * Studentische Arbeitsgruppen
 * ------------------------------------------------------------------------ */

const Arbeitsgruppen = {
    toggleOption: function(user_id) {
        if (jQuery('#user_opt_' + user_id).is(':hidden')) {
            jQuery('#user_opt_' + user_id).show('slide', { direction: 'left' }, 400, function() {
                jQuery('#user_opt_' + user_id).css('display', 'inline-block');
            });
        } else {
            jQuery('#user_opt_' + user_id).hide('slide', { direction: 'left' }, 400);
        }
    }
};

export default Arbeitsgruppen;
