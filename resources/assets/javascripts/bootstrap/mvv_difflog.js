STUDIP.domReady(() => {
    $('del.diffdel').each(function() {
        var mvv_field = '';

        $(this)
            .parentsUntil('div')
            .each(function() {
                if ($(this).attr('data-mvv-field')) {
                    mvv_field = $(this).attr('data-mvv-field');
                    return true;
                }
            });

        if (mvv_field != '') {
            $(this)
                .parentsUntil('div')
                .each(function() {
                    if ($(this).attr('data-mvv-id')) {
                        mvv_id = $(this).attr('data-mvv-id');
                        return true;
                    }
                });
            var mvv_debug = $(this).text();

            var del = $(this);
            var fields = mvv_field.split(' ');

            for (var i = 0; i < fields.length; ++i) {
                var obj_elements = fields[i].split('.');

                if (obj_elements.length == 1) {
                    var senddata = { mvv_field: fields[i], mvv_debug: mvv_debug, log_action: 'del' };
                } else {
                    var senddata = { mvv_field: fields[i], mvv_id: mvv_id, log_action: 'update' };
                }

                var url = STUDIP.URLHelper.getURL('dispatch.php/shared/log_event/get_log_autor');
                $.post(
                    url,
                    senddata,
                    function(data) {
                        if (data) {
                            var info = 'Entfernt von ' + data.user + ' am ' + data.time;
                            del.attr('title', info);
                            del.after('<del class="difflog"> [' + info + '] </ins>');
                        }
                    },
                    'json'
                );
            }
        }
    });

    $('ins').each(function() {
        var mvv_field = '';
        var mvv_coid = '';
        var mvv_id = '';

        switch ($('ins').attr('class')) {
            case 'diffins':
                var mvv_log_action = 'new';
                break;
            case 'diffmod':
                var mvv_log_action = 'update';
                break;
            default:
                var mvv_log_action = null;
                break;
        }

        $(this)
            .parentsUntil('div')
            .each(function() {
                if ($(this).attr('data-mvv-field')) {
                    mvv_field = $(this).attr('data-mvv-field');
                    mvv_coid = $(this).attr('data-mvv-coid');
                    return false;
                }
            });

        if (mvv_field != '') {
            $(this)
                .parentsUntil('div')
                .each(function() {
                    if ($(this).attr('data-mvv-id')) {
                        mvv_id = $(this).attr('data-mvv-id');
                        return false;
                    }
                });

            var ins = $(this);
            var fields = mvv_field.split(' ');
            for (var i = 0; i < fields.length; ++i) {
                var obj_elements = fields[i].split('.');
                if (obj_elements.length == 1 && mvv_coid) {
                    var senddata = {
                        mvv_field: fields[i],
                        mvv_id: mvv_id,
                        mvv_coid: mvv_coid,
                        log_action: mvv_log_action
                    };
                } else if (fields[i] == 'mvv_modulteil_stgteilabschnitt.differenzierung' && mvv_coid) {
                    var classes = $(this)
                        .parent()
                        .attr('class')
                        .split(' ');
                    if (classes.length > 1) {
                        var mvv_debug =
                            $(this)
                                .parent()
                                .attr('data-mvv-index') +
                            ';' +
                            classes[1];
                        var senddata = {
                            mvv_field: fields[i],
                            mvv_id: mvv_id,
                            mvv_coid: mvv_coid,
                            log_action: mvv_log_action,
                            mvv_debug: mvv_debug
                        };
                    } else {
                        return true;
                    }
                } else {
                    var senddata = { mvv_field: fields[i], mvv_id: mvv_id, log_action: mvv_log_action };
                }

                var url = STUDIP.URLHelper.getURL('dispatch.php/shared/log_event/get_log_autor');
                $.post(
                    url,
                    senddata,
                    function(data) {
                        if (data) {
                            var info = 'Änderung durch ' + data.user + ' am ' + data.time;
                            ins.attr('title', info);
                            ins.after('<ins class="difflog"> [' + info + '] </ins>');
                        }
                    },
                    'json'
                );
            }
        }
    });

    $('.mvv-diff-added').each(function() {
        $(this)
            .find('table')
            .each(function() {
                if ($(this).attr('data-mvv-type')) {
                    var mvv_type = $(this).attr('data-mvv-type');
                    var mvv_id = $(this).attr('data-mvv-id');
                    var curtable = $(this);
                } else {
                    return true;
                }

                var url = STUDIP.URLHelper.getURL('dispatch.php/shared/log_event/get_log_autor');
                $.post(
                    url,
                    { mvv_field: 'mvv_' + mvv_type, mvv_id: mvv_id, log_action: 'new' },
                    function(data) {
                        if (data) {
                            var info = 'Hinzugefügt von ' + data.user + ' am ' + data.time;
                            curtable.attr('title', info);
                            curtable.append('<tr><td><ins class="difflog"> [' + info + '] </ins><td></tr>');
                        }
                    },
                    'json'
                );
            });
    });

    $('.mvv-diff-deleted').each(function() {
        $(this)
            .find('table')
            .each(function() {
                if ($(this).attr('data-mvv-type')) {
                    var mvv_type = $(this).attr('data-mvv-type');
                    var mvv_id = $(this).attr('data-mvv-id');
                    var curtable = $(this);
                } else {
                    return true;
                }

                var url = STUDIP.URLHelper.getURL('dispatch.php/shared/log_event/get_log_autor');
                $.post(
                    url,
                    { mvv_field: 'mvv_' + mvv_type, mvv_id: mvv_id, log_action: 'del' },
                    function(data) {
                        if (data) {
                            var info = 'Entfernt von ' + data.user + ' am ' + data.time;
                            curtable.attr('title', info);
                            curtable.append('<tr><td><del class="difflog"> [' + info + '] </del><td></tr>');
                        }
                    },
                    'json'
                );
            });
    });
});
