/*jslint browser: true, unparam: true */
/*global jQuery, STUDIP */
const News = {
    /**
     * (Re-)initialise news-page, f.e. to stay in dialog
     */
    init: function (id) {
        // prevent forms within dialog from reloading whole page, and reload dialog instead
        $('#' + id + ' form').on('click', function (event) {
            $(this).data('clicked', $(event.target));
        });
        $(document).on('change', '#' + id + ' form .news_date', function () {
            // This is neccessary since datepickers are initialiszed on focus
            // which might not have occured yet
            STUDIP.UI.Datepicker.init();

            var start = $('#news_startdate').blur().datepicker('getDate'),
                duration,
                end,
                result;
            if ($(this).is('#news_duration')) {
                // datepicker assumes beginning of day (00:00), but the duration includes the end date (until 23:59)
                duration = window.parseInt(this.value, 10) - 1;
                result   = new Date(start);
                result.setDate(result.getDate() + duration);

                $('#news_enddate').datepicker('setDate', result);
            } else {
                start    = $('#news_startdate').datepicker('getDate');
                end      = $('#news_enddate').datepicker('getDate');
                // datepicker assumes beginning of day (see above) and we need to add a day to the duration
                duration = Math.round((end - start) / (24 * 60 * 60 * 1000)) + 1;
                duration = Math.max(0, duration);

                $('#news_duration').val(duration);
            }
        });

        $('#' + id + ' form').on('submit', function (event) {
            event.preventDefault();

            var textarea, button, form_route, form_data;
            if (STUDIP.editor_enabled) {
                textarea = $('textarea.news_body');
                // wysiwyg is active, ensure HTML markers are set
                textarea.val(STUDIP.wysiwyg.markAsHtml(textarea.val()));
            }

            button     = $(this).data('clicked').attr('name');
            form_route = $(this).attr('action');
            form_data  = $(this).serialize() + '&' + button + '=1';

            $(this).find('input[name=' + button + ']').showAjaxNotification('left');
            News.update_dialog(id, form_route, form_data);
        });
    },

    init_dialog: function () {
        $('.add_toolbar').addToolbar();
    },

    get_dialog: function (id, route) {
        // initialize dialog
        $('body').append('<div id="' + id + '"></div>');
        $('#' + id).dialog({
            modal: true,
            height: News.dialog_height,
            title: 'Dialog wird geladen...'.toLocaleString(),
            width: News.dialog_width,
            close: function () {
                $('#' + id).remove();
            }
        });

        // load actual dialog content
        $.get(route, 'html').done(function (html, status, xhr) {
            $('#' + id).dialog('option', 'title', decodeURIComponent(xhr.getResponseHeader('X-Title')));
            $('#' + id).html(html);
            $('#' + id + '_content').css({
                height : (News.dialog_height - 120) + 'px',
                maxHeight: (News.dialog_height - 120) + 'px'
            });

            News.init_dialog();
            News.init(id);
        }).fail(function () {
            window.alert('Fehler beim Aufruf des News-Controllers'.toLocaleString());
        });
    },

    update_dialog: function (id, route, form_data) {
        if (!News.pending_ajax_request) {
            News.pending_ajax_request = true;

            $.post(route, form_data, 'html').done(function (html) {
                var obj;

                News.pending_ajax_request = false;
                if (html.length > 0) {
                    $('#' + id).html(html);
                    $('#' + id + '_content').css({
                        'height' : (News.dialog_height - 120) + 'px',
                        'maxHeight': (News.dialog_height - 120) + 'px'
                    });
                    // scroll to anker
                    obj = $('a[name=anker]');
                    if (obj.length > 0) {
                        $('#' + id + '_content').scrollTop(obj.position().top);
                    }
                } else {
                    $('#' + id).dialog('close');
                    obj = $('#admin_news_form');
                    if (obj.length > 0) {
                        $('#admin_news_form').submit();
                    } else {
                        location.replace(STUDIP.URLHelper.getURL(location.href, {nsave: 1}));
                    }
                }

                News.init_dialog();
                News.init(id);
            }).fail(function () {
                News.pending_ajax_request = false;
                window.alert('Fehler beim Aufruf des News-Controllers'.toLocaleString());
            });
        }
    },

    toggle_category_view: function (id) {
        if ($('input[name=' + id + '_js]').val() === 'toggle') {
            $('input[name=' + id + '_js]').val('');
        } else {
            $('input[name=' + id + '_js]').val('toggle');
        }
        if ($('#' + id + '_content').is(':visible')) {
            $("#" + id + '_content').slideUp(400);
            $('#' + id + ' input[type=image]:first')
                .attr('src', STUDIP.ASSETS_URL + 'images/icons/blue/arr_1right.svg');
        } else {
            $('#' + id + '_content').slideDown(400);
            $('#' + id + ' input[type=image]:first')
                .attr('src', STUDIP.ASSETS_URL + 'images/icons/blue/arr_1down.svg');
        }
    }
};

export default News;
