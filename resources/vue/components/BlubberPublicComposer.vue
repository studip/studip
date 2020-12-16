<template>
    <div class="writer">
        <studip-icon shape="blubber" size="30" role="info"></studip-icon>
        <textarea :placeholder="$gettext('Schreib was, frag was. Enter zum Abschicken.')"
                  @keyup.enter.exact="submit"
                  @keyup="saveCommentToSession" @change="saveCommentToSession"></textarea>
        <label class="upload" :title="$gettext('Datei hochladen')">
            <input type="file" multiple style="display: none;" @change="upload">
            <studip-icon shape="upload" size="30"></studip-icon>
        </label>
    </div>
</template>
<script>
    export default {
        name: 'blubber-public-composer',
        methods: {
            submit (text) {
                if (!text || typeof text !== "string") {
                    text = $(this.$el).find("textarea").val();
                    $(this.$el).find("textarea").val("");
                    sessionStorage.removeItem(
                        'BlubberMemory-Writer-Public'
                    );
                }
                if (!text.trim()) {
                    return false;
                }
                let thread = this;

                //AJAX-Request ...
                STUDIP.api.POST(`blubber/threads`, {
                    data: {
                        content: text
                    }
                }).done((data) => {
                    this.$parent.addPosting(data.thread_posting);
                });
            },
            saveCommentToSession (event) {
                let value = event.target.value;
                sessionStorage.setItem(
                    `BlubberMemory-Writer-Public`,
                    value
                );
            },
            upload (event) {
                let files = typeof event.dataTransfer !== 'undefined'
                    ? event.dataTransfer.files // file drop
                    : event.target.files; // upload button
                let writer = this;
                let data = new FormData();
                for (let i in files) {
                    if (files[i].size > 0) {
                        data.append(`file_${i}`, files[i], files[i].name.normalize());
                    }
                }

                let request = new XMLHttpRequest();
                request.open('POST', `${STUDIP.ABSOLUTE_URI_STUDIP}dispatch.php/blubber/upload_files`);
                request.upload.addEventListener('progress', (event) => {
                    var percent = 0;
                    var position = event.loaded || event.position;
                    var total = event.total;
                    if (event.lengthComputable) {
                        percent = Math.ceil(position / total * 100);
                    }
                    //Set progress
                    $(writer.$el).css('background-size', `${percent}% 100%`);
                });
                request.addEventListener('load', function (event) {
                    let output = JSON.parse(this.response);
                    $(writer.$el).find("textarea").val(
                        $(writer.$el).find("textarea").val()
                        + " "
                        + output.inserts.join(" ")
                    );
                });
                request.addEventListener('loadend', function (event) {
                    $(writer.$el).css('background-size', '0% 100%');
                });
                request.send(data);
            }
        },
        mounted () { //when everything is initialized
            this.$nextTick(function () {
                $(this.$el).find('textarea').autoResize({
                    animateDuration: 0,
                    // More extra space:
                    extraSpace: 1
                });
                let memory = sessionStorage.getItem(`BlubberMemory-Writer-Public`);
                if (memory) {
                    $(this.$el).find('textarea').val(memory);
                }
            });
        }
    }
</script>
