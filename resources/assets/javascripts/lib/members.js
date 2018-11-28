const Members = {
    addPersonToSelection: function(userId, name) {
        var target = $('#persons-to-add'),
            newEl = $('<li>').html(
                $('<span>')
                    .html(name)
                    .text()
            ),
            input = $('<input type="hidden" name="users[]">').val(userId),
            remove = $('<img>').attr('src', STUDIP.ASSETS_URL + 'images/icons/blue/trash.svg');

        remove.on('click', function() {
            $(this)
                .parent()
                .remove();
        });

        newEl.append(input, remove).appendTo(target);

        return false;
    }
};

export default Members;
