class InlineEditing
{
    static init(element) {
        if (!element) {
            return;
        }

        var text = jQuery(element).text().trim();

        var icon_path = STUDIP.ASSETS_URL + '/images/icons/blue/NAME.svg';
        var input_type = jQuery(element).data('input-type').toLowerCase();
        var input_name = jQuery(element).data('input-name');
        var icon_size = jQuery(element).data('icon-size');
        if (!icon_size) {
            icon_size = '20px';
        }

        //Build the display container:
        var text_container = jQuery('<span class="text"></span>');
        jQuery(text_container).text(text);
        var icon_container = jQuery('<div></div>');
        var icon_element = jQuery('<img class="edit-button"></img>');
        jQuery(icon_element).attr('width', icon_size);
        jQuery(icon_element).attr('height', icon_size);
        jQuery(icon_element).attr('src', icon_path.replace('NAME', 'edit'));
        jQuery(icon_container).append(icon_element);
        var display_container = jQuery(
            '<div class="display-container"></div>'
        );
        jQuery(display_container).append(text_container);
        jQuery(display_container).append(icon_container);

        var input_field = undefined;
        var edit_icons_container = undefined;
        var accept_icon = jQuery('<img class="save-button"></img>');
        jQuery(accept_icon).attr('width', icon_size);
        jQuery(accept_icon).attr('height', icon_size);
        jQuery(accept_icon).attr('src', icon_path.replace('NAME', 'accept'));
        var abort_icon = jQuery('<img class="abort-button"></img>');
        jQuery(abort_icon).attr('width', icon_size);
        jQuery(abort_icon).attr('height', icon_size);
        jQuery(abort_icon).attr('src', icon_path.replace('NAME', 'decline'));

        if (input_type == 'textarea') {
            input_field = jQuery('<textarea class="input-field"></textarea>');
            jQuery(input_field).attr('name', input_name);
            jQuery(input_field).text(text);
            edit_icons_container = jQuery('<div></div>');
        } else {
            input_field = jQuery('<input class="input-field">');
            jQuery(input_field).attr('type', input_type);
            jQuery(input_field).attr('name', input_name);
            jQuery(input_field).val(text);
            edit_icons_container = jQuery('<span></span>');
        }
        jQuery(edit_icons_container).append(accept_icon);
        jQuery(edit_icons_container).append(abort_icon);

        var edit_container = jQuery('<div class="edit-container invisible"></div>');
        jQuery(edit_container).append(input_field);
        jQuery(edit_container).append(edit_icons_container);

        jQuery(element).empty();
        jQuery(element).append(display_container);
        jQuery(element).append(edit_container);
    };


    static activate(element) {
        var container = jQuery(element).parents('[data-inline-editing]');
        if (!container) {
            return;
        }

        jQuery(container).children('.display-container').addClass('invisible');
        jQuery(container).children('.edit-container').removeClass('invisible');
    };


    static save(element) {
        var container = jQuery(element).parents('[data-inline-editing]');
        if (!container) {
            return;
        }
        var ajax_url = jQuery(container).data('inline-editing');

        var text_field = jQuery(container).find('.text')[0];
        if (!text_field) {
            return;
        }
        var input_field = jQuery(container).find('.input-field')[0];
        if (!input_field) {
            return;
        }
        var input_name = jQuery(container).data('input-name');
        var input_value = jQuery(input_field).val();
        var data = {
            quiet: 1
        };
        data[input_name] = input_value;

        jQuery.ajax(
            {
                url: ajax_url,
                method: 'POST',
                data: data
            }
        ).done(
            function() {
                jQuery(text_field).text(input_value);
                jQuery(container).find('.edit-container').addClass('invisible');
                jQuery(container).find('.display-container').removeClass('invisible');
            }
        ).fail(
            function(data) {
                jQuery(input_field).css('border-color', 'red');
                if (data) {
                    jQuery(container).find('.error-message').val(data);
                }
            }
        );
    };


    static abort(element) {
        var container = jQuery(element).parents('[data-inline-editing]');
        if (!container) {
            return;
        }

        jQuery(container).children('.edit-container').addClass('invisible');
        jQuery(container).children('.display-container').removeClass('invisible');

    };
}


export default InlineEditing;
