const Dates = {
    addTopic: function() {
        var topic_name = $('#new_topic').val(),
            termin_id = $('#new_topic')
                .closest('[data-termin-id]')
                .data().terminId;

        if (!topic_name) {
            $('#new_topic').focus();
            return;
        }

        $.post(STUDIP.URLHelper.getURL('dispatch.php/course/dates/add_topic'), {
            title: topic_name,
            termin_id: termin_id
        }).done(function(response) {
            if (response.hasOwnProperty('li')) {
                $('#new_topic')
                    .closest('[data-termin-id]')
                    .find('.themen-list')
                    .append(response.li);
                $('#date_' + termin_id)
                    .find('.themen-list')
                    .append(response.li);
            }

            $('#new_topic')
                .val('')
                .focus();
        });
    },
    removeTopicFromIcon: function() {
        var topic_id = $(this)
                .closest('li')
                .data('issue_id'),
            termin_id = $(this)
                .closest('[data-termin-id]')
                .data().terminId;
        Dates.removeTopic(termin_id, topic_id);
    },
    removeTopic: function(termin_id, topic_id) {
        $.ajax({
            url: STUDIP.URLHelper.getURL('dispatch.php/course/dates/remove_topic'),
            data: {
                issue_id: topic_id,
                termin_id: termin_id
            },
            dataType: 'json',
            type: 'post'
        }).done(function() {
            $('.topic_' + termin_id + '_' + topic_id).remove();
        });
    }
};

export default Dates;
