STUDIP.Dialog.registerHeaderHandler('X-Dialog-Notice', json => {
    json = JSON.parse(json);

    $(`#course-${json.id} td.actions .button`)
        .removeClass('has-notice has-no-notice')
        .addClass(json.notice.length > 0 ? 'has-notice' : 'has-no-notice')
        .attr('title', json.notice);

    STUDIP.Dialog.close();

    return false;
});
