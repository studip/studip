/*global jQuery, STUDIP */
STUDIP.domReady(() => {
    STUDIP.Avatar.init('#avatar-upload');

    // Get file data on drop
    var dropZone = document.getElementById('avatar-overlay');

    if (dropZone) {
        dropZone.addEventListener('dragover', function(e) {
            e.stopPropagation();
            e.preventDefault();
            e.target.parentNode.classList.add("dragging");
        });

        dropZone.addEventListener('dragleave', function(e) {
            e.stopPropagation();
            e.preventDefault();
            e.target.parentNode.classList.remove("dragging");
        });

        dropZone.addEventListener('drop', function(e) {
            e.stopPropagation();
            e.preventDefault();
            e.target.parentNode.classList.remove("dragging");
            var files = e.dataTransfer.files;
            var div = e.target.parentNode;
            var avatar_dialog = div.getElementsByTagName('a')[0];

            if (!div.getAttribute('accept') || !div.getAttribute('accept').includes(files[0].type)) {
                alert(div.getAttribute('data-message-unaccepted'));
                return false;
            }

            if (!div.getAttribute('data-max-size') || files[0].size > div.getAttribute('data-max-size')) {
                alert(div.getAttribute('data-message-too-large'));
                return false;
            }

            avatar_dialog.click();
            div.files = files;
            STUDIP.dialogReady(() => {
                STUDIP.Avatar.readFile(div);
            });
        });
    }
});
