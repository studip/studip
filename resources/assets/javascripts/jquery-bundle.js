import 'expose-loader?$!jquery';
import 'expose-loader?jQuery!jquery';

import 'jquery-ui/ui/widget.js';
import 'jquery-ui/ui/position.js';
import 'jquery-ui/ui/data.js';
import 'jquery-ui/ui/disable-selection.js';
import 'jquery-ui/ui/escape-selector.js';
import 'jquery-ui/ui/focusable.js';
import 'jquery-ui/ui/form.js';
import 'jquery-ui/ui/form-reset-mixin.js';
import 'jquery-ui/ui/ie.js';
import 'jquery-ui/ui/jquery-1-7.js';
import 'jquery-ui/ui/keycode.js';
import 'jquery-ui/ui/labels.js';
import 'jquery-ui/ui/plugin.js';
import 'jquery-ui/ui/safe-active-element.js';
import 'jquery-ui/ui/safe-blur.js';
import 'jquery-ui/ui/scroll-parent.js';
import 'jquery-ui/ui/tabbable.js';
import 'jquery-ui/ui/unique-id.js';
import 'jquery-ui/ui/version.js';
import 'jquery-ui/ui/widgets/draggable.js';
import 'jquery-ui/ui/widgets/droppable.js';
import 'jquery-ui/ui/widgets/resizable.js';
import 'jquery-ui/ui/widgets/selectable.js';
import 'jquery-ui/ui/widgets/sortable.js';
import 'jquery-ui/ui/widgets/accordion.js';
import 'jquery-ui/ui/widgets/autocomplete.js';
import 'jquery-ui/ui/widgets/button.js';
import 'jquery-ui/ui/widgets/checkboxradio.js';
import 'jquery-ui/ui/widgets/controlgroup.js';
import 'jquery-ui/ui/widgets/datepicker.js';
import 'jquery-ui/ui/widgets/dialog.js';
import 'jquery-ui/ui/widgets/menu.js';
import 'jquery-ui/ui/widgets/mouse.js';
import 'jquery-ui/ui/widgets/progressbar.js';
import 'jquery-ui/ui/widgets/selectmenu.js';
import 'jquery-ui/ui/widgets/slider.js';
import 'jquery-ui/ui/widgets/spinner.js';
import 'jquery-ui/ui/widgets/tabs.js';
import 'jquery-ui/ui/widgets/tooltip.js';
import 'jquery-ui/ui/effect.js';
import 'jquery-ui/ui/effects/effect-blind.js';
import 'jquery-ui/ui/effects/effect-bounce.js';
import 'jquery-ui/ui/effects/effect-clip.js';
import 'jquery-ui/ui/effects/effect-drop.js';
import 'jquery-ui/ui/effects/effect-explode.js';
import 'jquery-ui/ui/effects/effect-fade.js';
import 'jquery-ui/ui/effects/effect-fold.js';
import 'jquery-ui/ui/effects/effect-highlight.js';
import 'jquery-ui/ui/effects/effect-puff.js';
import 'jquery-ui/ui/effects/effect-pulsate.js';
import 'jquery-ui/ui/effects/effect-scale.js';
import 'jquery-ui/ui/effects/effect-shake.js';
import 'jquery-ui/ui/effects/effect-size.js';
import 'jquery-ui/ui/effects/effect-slide.js';
import 'jquery-ui/ui/effects/effect-transfer.js';

import 'jquery-ui-timepicker-addon';

import 'multiselect';

import 'jquery.scrollto';

import 'jquery-ui-touch-punch';

import './studip-jquery-tweaks.js';
import './studip-jquery.multi-select.tweaks.js';
import './studip-jquery-selection-helper.js';

import select2 from 'select2/dist/js/select2.full.js';

import 'sticky-kit/dist/sticky-kit.js';

import 'gridstack';
import 'gridstack/dist/gridstack.jQueryUI.js';

import 'blueimp-file-upload';
import 'blueimp-file-upload/js/jquery.iframe-transport.js';

import './jquery/jquery.filtertable-1.5.7.js';

import Toolbar from './lib/toolbar.js';

$.fn.extend({
    // Adds the toolbar to an element
    addToolbar: function(button_set) {
        return this.each(function() {
            Toolbar.initialize(this, button_set);
        });
    }
});

// Create jQuery "plugin" that just reverses the elements' order. This is
// neccessary since the navigation is built and afterwards, we need to
// check the navigation's open status in reverse order (from bottom to top)
jQuery.fn.reverse = [].reverse;

$.fn.extend({
    showAjaxNotification: function(position) {
        position = position || 'left';
        return this.each(function() {
            if ($(this).data('ajax_notification')) {
                return;
            }

            $(this).wrap('<span class="ajax_notification" />');
            var that = this,
                notification = $('<span class="notification" />')
                    .hide()
                    .insertBefore(this),
                changes = {
                    marginLeft: 0,
                    marginRight: 0
                };

            changes[position === 'right' ? 'marginRight' : 'marginLeft'] = notification.outerWidth(true);

            $(this)
                .data({
                    ajax_notification: notification
                })
                .parent()
                .animate(changes, 'fast', function() {
                    var offset = $(that).position(),
                        styles = {
                            left: offset.left - notification.outerWidth(true),
                            top:
                                offset.top +
                                Math.max(0, Math.floor(($(that).height() - notification.outerHeight(true)) / 2))
                        };
                    if (position === 'right') {
                        styles.left += $(this).outerWidth(true);
                    }
                    notification.css(styles).fadeIn('fast');
                });
        });
    },
    hideAjaxNotification: function() {
        return this.each(function() {
            var $this = $(this).stop(),
                notification = $this.data('ajax_notification');
            if (!notification) {
                return;
            }

            notification.stop().fadeOut('fast', function() {
                $this.animate({ marginLeft: 0, marginRight: 0 }, 'fast', function() {
                    $this.unwrap();
                });
                $(this).remove();
            });
            $(this).removeData('ajax_notification');
        });
    }
});

$.extend($.expr[':'], {
    invalid: function(elem, index, match) {
        var invalids = document.querySelectorAll(':invalid'),
            result = false,
            len = invalids.length || 0,
            i;

        for (i = 0; i < len; i += 1) {
            if (elem === invalids[i]) {
                result = true;
                break;
            }
        }

        return result;
    }
});

$(document).ready(() => {
    STUDIP.ready.trigger('dom');
}).on('dialog-update', (event, data) => {
    STUDIP.ready.trigger('dialog', data.dialog);
});
