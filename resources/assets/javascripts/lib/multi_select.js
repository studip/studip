/*jslint esversion:6*/
/**
 * Turns a select-box into an easy to use multiple select-box
 */

const MultiSelect = {
    create: function (id, itemName, options = {}) {
        const count = $(id).find('option:selected').length;
        const count_template = _.template('<%= count %> ausgewählt'.toLocaleString());
        const update_counter = function () {
            const count = $(id).find('option:selected').length;
            $(id).next().find('.counter').text(count_template({count: count}));
        };

        if (!$(id).attr('multiple')) {
            $(id).attr('multiple', 'multiple').css('height', '6em');
        }
        $(id).multiSelect({
            selectableHeader:
                `<div class="header">
                    <a href="#" class="button select-all">${'Alle hinzufügen'.toLocaleString()}</a>
                </div>`,
            selectionHeader:
                `<div class="header">
                    <div class="counter">${count_template({count: count})}.</div>
                    <a href="#" class="button deselect-all">${'Alle entfernen'.toLocaleString()}</a>
                </div>`,
            keepOrder: true,
            cssClass: ['studip-multi-select', options.cssClass || ''].join(' ').trim(),
            afterInit: function () {
                $(id).next().find('.ms-elem-selectable,.ms-elem-selection').find('br').remove();
            },
            afterSelect: update_counter,
            afterDeselect: update_counter
        });

        $(id).next().find('.select-all').click(function () {
            $(id).multiSelect('select_all');
        });
        $(id).next().find('.deselect-all').click(function () {
            $(id).multiSelect('deselect_all');
        });
    }
};

export default MultiSelect;
