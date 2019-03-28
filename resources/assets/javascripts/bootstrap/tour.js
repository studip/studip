/* ------------------------------------------------------------------------
 * Stud.IP Tour
 * ------------------------------------------------------------------------
 *
 * @author Arne Schröder, schroeder@data-quest.de
 * @description Studip Tour
 *
 * Parts of this script are a modified version of:
 *
 * jQuery aSimpleTour
 * @author alvaro.veliz@gmail.com
 * @servedby perkins (http://p.erkins.com)
 *
 * Dependencies :
 * - jQuery scrollTo
 *
 */

STUDIP.domReady(() => {
    //STUDIP.Tour.started = false;
    STUDIP.Tour.pending_ajax_request = false;

    jQuery(document).keyup(function(event) {
        if (STUDIP.Tour.started && event.keyCode === 37 && jQuery('#tour_prev').is(':visible')) {
            STUDIP.Tour.prev();
        } else if (STUDIP.Tour.started && event.keyCode === 39 && jQuery('#tour_next').is(':visible')) {
            STUDIP.Tour.next();
        } else if (STUDIP.Tour.started && event.keyCode === 27 && jQuery('#tour_end').is(':visible')) {
            STUDIP.Tour.destroy();
        }
    });

    jQuery(document).on('keyright', function(event) {
        STUDIP.Tour.prev();
    });
    jQuery(document).on('click', '.tour_link', function(event) {
        event.preventDefault();
        STUDIP.Tour.init(jQuery(this).attr('id'), 1);
    });

    jQuery(document).on('click', '#tour_next', function() {
        STUDIP.Tour.next();
    });

    jQuery(document).on('click', '#tour_prev', function() {
        STUDIP.Tour.prev();
    });

    jQuery(document).on('click', '#tour_end', function() {
        STUDIP.Tour.destroy();
    });
});
