jQuery(document).ready(
    function () {
        jQuery(document).on(
            'click',
            '.resource-tree .expand-action',
            function (event) {
                var li_element = jQuery(event.target).parent();
                if (!li_element) {
                    return;
                }
                jQuery(event.target).css('transform', 'rotate(90deg)');

                jQuery(li_element).siblings().css('display', 'none');
                //Show the layer of resources that lies
                //below the clicked resource:
                var ul_elements = jQuery(li_element).children('ul');
                jQuery(ul_elements).css('display', 'block');
                jQuery(ul_elements).children('li').css('display', 'list-item');

                jQuery(event.target).removeClass('expand-action');
                jQuery(event.target).addClass('collapse-action');
            }
        );


        jQuery(document).on(
            'click',
            '.resource-tree .collapse-action',
            function (event) {
                var li_element = jQuery(event.target).parent();
                if (!li_element) {
                    return;
                }
                jQuery(event.target).css('transform', '');

                jQuery(li_element).siblings().css('display', '');
                //Show the layer of resources that lies
                //below the clicked resource:
                var ul_elements = jQuery(li_element).children('ul');
                jQuery(ul_elements).css('display', 'none');
                jQuery(ul_elements).children('li').css('display', 'none');

                jQuery(event.target).removeClass('collapse-action');
                jQuery(event.target).addClass('expand-action');
            }
        );
    }
);
