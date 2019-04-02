/*jslint esversion: 6*/
/* ------------------------------------------------------------------------
 * QuickSearch inputs
 * ------------------------------------------------------------------------ */

const QuickSearch = {
    /**
     * a helper-function to generate a JS-object filled with the variables of a form
     * like "{ input1_name : input1_value, input2_name: input2_value }"
     * @param selector string: ID of an input in a form-tag
     * @return: JSON-object (not as a string)
     */
    formToJSON: function(selector) {
        selector = jQuery(selector).parents('form');
        var form = {}; //the basic JSON-object that will be returned later
        jQuery(selector)
            .find(':input[name]')
            .each(function() {
                var name = jQuery(this).attr('name'); //name of the input
                var active = jQuery(this).attr('type') !== 'checkbox' || jQuery(this).is(':checked');
                if (active) {
                    if (form[name]) {
                        //for double-variables (not arrays):
                        form[name] = form[name] + ',' + jQuery(this).val();
                    } else {
                        form[name] = jQuery(this).val();
                    }
                }
            });
        return form;
    },
    /**
     * the function to be called from the QuickSearch class template
     * @param name string: ID of input
     * @param url string: URL of AJAX-response
     * @param func string: name of a possible function executed
     *        when user has selected something
     * @return: void
     */
    autocomplete: function(name, url, func, disabled) {
        if (disabled === undefined || disabled !== true) {
            var appendTo = 'body';
            if (jQuery(`#${name}_frame`).length > 0) {
                appendTo = `#${name}_frame`;
            } else if ($(`#${name}`).closest('.ui-dialog').length > 0) {
                appendTo = $(`#${name}`).closest('.ui-dialog');
            }
            jQuery('#' + name).autocomplete({
                delay: 500,
                minLength: 3,
                appendTo: appendTo,
                create: function() {
                    if ($(this).is('[autofocus]')) {
                        $(this).focus();
                    }
                },
                position: $('#' + name).is('.expand-to-left')
                    ? {
                          my: 'right top',
                          at: 'right bottom',
                          collision: 'none'
                      }
                    : {
                          my: 'left top',
                          at: 'left bottom',
                          collision: 'none'
                      },
                source: function(input, add) {
                    //get the variables that should be sent:
                    var send_vars = jQuery('#' + name)
                        .closest('form')
                        .serializeArray();
                    send_vars.push({
                        name: 'request',
                        value: input.term
                    });

                    jQuery
                        .ajax({
                            url: url,
                            type: 'post',
                            data: send_vars
                        })
                        .done(function(data) {
                            var stripTags = /<\w+(\s+("[^"]*"|'[^']*'|[^>])+)?>|<\/\w+>/gi;
                            //an array of possible selections

                            if (!data.length) {
                                add([{ value: '', label: 'Kein Ergebnis gefunden.'.toLocaleString(), disabled: true }]);
                                return;
                            }

                            var suggestions = _.map(data, function(val) {
                                //adding a label and a hidden item_id - don't use "value":
                                var label_text = val.item_name;
                                if (val.item_description !== undefined) {
                                    label_text += '<br>' + val.item_description;
                                }

                                return {
                                    //what is displayed in the drop down box
                                    label: label_text,
                                    //the hidden ID of the item
                                    item_id: val.item_id,
                                    //what is inserted in the visible input box
                                    value:
                                        val.item_search_name !== null
                                            ? val.item_search_name
                                            : jQuery('<div/>')
                                                  .html((val.item_name || '').replace(stripTags, ''))
                                                  .text()
                                };
                            });
                            //pass it to the function of UI-widget:
                            add(suggestions);
                        })
                        .fail(function(jqxhr, textStatus) {
                            add([
                                {
                                    value: '',
                                    label: 'Fehler'.toLocaleString() + ': ' + jqxhr.responseText,
                                    disabled: true
                                }
                            ]);
                        });
                },
                select: function(event, ui) {
                    if (ui.item.disabled) {
                        return;
                    }

                    //inserts the ID of the selected item in the hidden input:
                    jQuery('#' + name + '_realvalue').attr('value', ui.item.item_id);
                    //and execute a special function defined before by the programmer:
                    if (func) {
                        var proceed = func.bind(event.target)(ui.item.item_id, ui.item.value);
                        if (!proceed) {
                            jQuery(this).val('');
                            return false;
                        }
                    }
                }
            });

            if (jQuery('#' + name + '_frame').length) {
                // trigger search on button click
                jQuery('#' + name + '_frame input[type="submit"]').click(function(e) {
                    e.preventDefault();
                    QuickSearch.triggerSearch(name);
                });

                // trigger search on enter key down
                jQuery('#' + name).keydown(function(e) {
                    if (e.keyCode == 13) {
                        e.preventDefault();
                        QuickSearch.triggerSearch(name);
                    }
                });
            }

            var input  = jQuery('#' + name);
            var hidden = jQuery('#' + name + '_realvalue');
            if (input.is('[required]')) {
                input.closest('form').submit(function (event) {
                    if (hidden.val() === '') {
                        input[0].setCustomValidity('Bitte wählen Sie einen gültigen Wert aus!'.toLocaleString());
                        event.preventDefault();
                     }
                 });
             }
        }
    },

    // start searching now
    triggerSearch: function(name) {
        var term = jQuery('#' + name).val();
        jQuery('#' + name).autocomplete({ minLength: 1 });
        jQuery('#' + name).autocomplete('search', term);
        jQuery('#' + name).autocomplete({ minLength: 3 });
    },

    reset: function(form_name, quick_search_name) {
        if (!form_name || !quick_search_name) {
            return;
        }
        document.forms[form_name].elements[quick_search_name].value = '';
        document.forms[form_name].elements[quick_search_name + '_parameter'].value = '';
    }
};

export default QuickSearch;
