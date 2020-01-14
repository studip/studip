STUDIP.ready(
    function() {
        //Enable fullcalendar on DOM nodes that require it,
        //for dialogs and other pages:
        var nodes = jQuery('*[data-fullcalendar="1"]');
        jQuery.each(
            nodes,
            function (index, node) {
                if (node.calendar == undefined) {
                    if (jQuery(node).hasClass('semester-plan')) {
                        STUDIP.Fullcalendar.createSemesterCalendarFromNode(
                            node
                        );
                    } else {
                        STUDIP.Fullcalendar.createFromNode(
                            node
                        );
                    }
                }
            }
        );
    }
);
