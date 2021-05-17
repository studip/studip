<template>
    <div class="blubber_thread"
         :id="'blubberthread_' + thread_data.thread_posting.thread_id"
         @dragover.prevent="dragover" @dragleave.prevent="dragleave"
         @drop.prevent="upload">
        <div class="responsive-visible context_info" v-if="thread_data.notifications">
            <a href="#"
               @click.prevent="toggleFollow()"
               class="followunfollow"
               :class="{unfollowed: !thread_data.followed}"
               :title="$gettext('Benachrichtigungen für diese Konversation abstellen.')"
               :data-thread_id="thread_data.thread_posting.thread_id">
                <StudipIcon shape="remove/notification2" :size="20" class="follow text-bottom"></StudipIcon>
                <StudipIcon shape="notification2" :size="20" class="unfollow text-bottom"></StudipIcon>
                {{ $gettext('Benachrichtigungen aktiviert') }}
            </a>
        </div>
        <div class="scrollable_area" v-scroll>
            <div class="all_content">
                <div class="thread_posting" v-if="hasContent(thread_data.thread_posting.content)">
                    <div class="contextinfo">
                        <studip-date-time :timestamp="thread_data.thread_posting.mkdate" :relative="true"></studip-date-time>
                        <a :href="getUserProfileURL(thread_data.thread_posting.user_id, thread_data.thread_posting.user_username)">{{ thread_data.thread_posting.user_name }}</a>
                        <a :href="getUserProfileURL(thread_data.thread_posting.user_id, thread_data.thread_posting.user_username)" class="avatar" :style="{ backgroundImage: 'url(' + thread_data.thread_posting.avatar + ')' }"></a>
                    </div>
                    <div class="content" v-html="thread_data.thread_posting.html"></div>
                    <div class="link_to_comments"></div>
                </div>

                <div v-if="!hasContent(thread_data.thread_posting.content) && !thread_data.comments.length" class="empty_blubber_background">
                    <div v-translate>Starte die Konversation jetzt!</div>
                </div>

                <ol class="comments" aria-live="polite">

                    <li class="more" v-if="thread_data.more_up">
                        <studip-asset-img file="ajax-indicator-black.svg" width="20"></studip-asset-img>
                    </li>

                    <li :class="comment.class"
                        v-for="comment in sortedComments"
                        :data-comment_id="comment.comment_id"
                        :key="comment.comment_id">
                        <a :href="getUserProfileURL(comment.user_id, comment.user_username)" class="avatar" :title="comment.user_name" :style="{ backgroundImage: 'url(' + comment.avatar + ')' }"></a>
                        <div class="content">
                            <a :href="getUserProfileURL(comment.user_id, comment.user_username)" class="name">{{ comment.user_name }}</a>
                            <div v-html="comment.html" class="html"></div>
                            <textarea class="edit"
                                      v-html="comment.content"
                                      @keyup.enter.exact="saveComment"
                                      @keyup.escape.exact="editComment"></textarea>
                        </div>
                        <div class="time">
                            <studip-date-time :timestamp="comment.mkdate" :relative="true"></studip-date-time>
                            <a href="" v-if="comment.writable" @click.prevent="editComment" class="edit_comment" :title="$gettext('Bearbeiten.')">
                                <studip-icon shape="edit" size="14" role="inactive"></studip-icon>
                            </a>
                            <a href="" @click.prevent="answerComment" class="answer_comment" :title="$gettext('Hierauf antworten.')">
                                <studip-icon shape="export" size="14" role="inactive"></studip-icon>
                            </a>
                        </div>
                    </li>

                    <li class="more" v-if="thread_data.more_down">
                        <studip-asset-img file="ajax-indicator-black.svg" width="20"></studip-asset-img>
                    </li>

                </ol>
            </div>
        </div>
        <div class="writer" v-if="thread_data.thread_posting.commentable">
            <studip-icon shape="blubber" size="30" role="info"></studip-icon>
            <textarea :placeholder="writerTextareaPlaceholder"
                      @keyup.enter.exact="submit"
                      @keyup.up.exact="editPreviousComment"
                      @keyup="saveCommentToSession" @change="saveCommentToSession"></textarea>
            <a class="send" @click="submit" :title="$gettext('Abschicken')">
                <studip-icon shape="arr_2up" size="30"></studip-icon>
            </a>
            <label class="upload" :title="$gettext('Datei hochladen')">
                <input type="file" multiple style="display: none;" @change="upload">
                <studip-icon shape="upload" size="30"></studip-icon>
            </label>
        </div>
    </div>
</template>

<script>
    export default {
        name: 'blubber-thread',
        data: function () {
            return {
                already_loading_up: 0,
                already_loading_down: 0
            };
        },
        props: ['thread_data'],
        methods: {
            submit (text) {
                if (!text || typeof text !== "string") {
                    text = $(this.$el).find(".writer textarea").val();
                    $(this.$el).find(".writer textarea").val("");
                    if (this.thread_data.thread_posting.thread_id) {
                        sessionStorage.removeItem(
                            'BlubberMemory-Writer-' + this.thread_data.thread_posting.thread_id
                        );
                    }
                }
                if (!text.trim()) {
                    return false;
                }
                let formatted_text = text.replace(/\n/g, "<br>");
                let comment = {
                    comment_id: Math.random().toString(36),
                    avatar: '',
                    html: formatted_text,
                    content: text,
                    mkdate:  Math.floor(Date.now() / 1000),
                    name: 'Nobody',
                    class: 'mine new',
                    writable: 1
                };
                this.addComment(comment);
                let thread = this;

                //AJAX-Request ...
                STUDIP.api.POST(`blubber/threads/${this.thread_data.thread_posting.thread_id}/comments`, {
                    data: {
                        content: text
                    }
                }).then(data => {
                    // Check following state
                    if (this.thread_data.notifications) {
                        STUDIP.api.GET(`blubber/threads/${this.thread_data.thread_posting.thread_id}/follow`).then(followed => {
                            jQuery('.followunfollow').toggleClass('unfollowed', !followed);
                        });
                    }
                    return data;
                }).done(data => {
                    comment.comment_id = data.comment_id;
                    comment.avatar = data.avatar;
                    comment.user_name = data.user_name;
                    comment.mkdate = data.mkdate;
                    comment.html = data.html;
                    comment.class = data.class;

                    thread.$nextTick(() => {
                        STUDIP.Markup.element($(thread.$el).find(`.comments > li[data-comment_id="${data.comment_id}"]`));
                    });
                });

                this.$nextTick(() => {
                    // DOM updated
                    this.scrollDown();
                });
            },
            saveCommentToSession (event) {
                let value = event.target.value;
                if (this.thread_data.thread_posting.thread_id) {
                    sessionStorage.setItem(
                        `BlubberMemory-Writer-${this.thread_data.thread_posting.thread_id}`,
                        value
                    );
                }
                $(this.$el).find('.writer').toggleClass(
                    'filled',
                    value.trim() !== ''
                );
            },
            scrollDown () {
                this.$nextTick(function () {
                    let element = this.$el;

                    let scroll = () => {
                        $(element).find('.scrollable_area').scrollTo(
                            $(element).find('.scrollable_area .all_content').height()
                        );
                    };

                    $(element).find('.scrollable_area img').on('load', scroll);
                    scroll();
                });
            },
            addComments (comments, new_ones) {
                comments.forEach((comment) => {
                    if (new_ones) {
                        comment.class += ' new';
                    }
                    this.addComment(comment);
                });
            },
            addComment (comment) {
                this.$nextTick(() => {
                    STUDIP.Markup.element($(this.$el).find(`.comments > li[data-comment_id="${comment.comment_id}"]`));
                });
                for (let i in this.thread_data.comments) {
                    if (this.thread_data.comments[i].comment_id === comment.comment_id) {
                        this.thread_data.comments[i].content = comment.content;
                        this.thread_data.comments[i].html = comment.html;
                        return;
                    }
                }
                this.thread_data.comments.push(comment);
            },
            removeComment (comment_id) {
                this.thread_data.comments.forEach((comment, i) => {
                    if (comment.comment_id === comment_id) {
                        this.$delete(this.thread_data.comments, i);
                    }
                });
            },
            upload (event) {
                let files = typeof event.dataTransfer !== 'undefined'
                    ? event.dataTransfer.files // file drop
                    : event.target.files; // upload button
                let thread = this;
                let data = new FormData();
                for (let i in files) {
                    if (files[i].size > 0) {
                        data.append(`file_${i}`, files[i], files[i].name.normalize());
                    }
                }

                var request = new XMLHttpRequest();
                request.open('POST', `${STUDIP.ABSOLUTE_URI_STUDIP}dispatch.php/blubber/upload_files`);
                request.upload.addEventListener('progress', (event) => {
                    var percent = 0;
                    var position = event.loaded || event.position;
                    var total = event.total;
                    if (event.lengthComputable) {
                        percent = Math.ceil(position / total * 100);
                    }
                    //Set progress
                    $(thread.$el).find('.writer').css('background-size', `${percent}% 100%`);
                });
                request.addEventListener('load', function (event) {
                    let output = JSON.parse(this.response);
                    thread.submit(output.inserts.join(" "));
                });
                request.addEventListener('loadend', function (event) {
                    $(thread.$el).find('.writer').css('background-size', '0% 100%');
                });
                request.send(data);

                this.dragleave();
            },
            dragover () {
                $(this.$el).addClass('dragover');
            },
            dragleave () {
                $(this.$el).removeClass('dragover');
            },
            getUserProfileURL (user_id, username) {
                if (username) {
                    return STUDIP.URLHelper.getURL('dispatch.php/profile', {
                        username: username
                    });
                } else {
                    return STUDIP.URLHelper.getURL('dispatch.php/profile/extern/' + user_id);
                }
            },
            editComment (event) {
                let li;
                if (typeof event === 'string') {
                    let comment_id = event;
                    li = $(this.$el).find(`.comments > li[data-comment_id="${comment_id}"]`);
                } else {
                    li = $(event.target).closest('li[data-comment_id]');
                    let comment_id = $(event.target).closest('li[data-comment_id]').data('comment_id');
                }
                li.find('.content').toggleClass('editing');
                let textarea = li.find('.content textarea').last()[0];
                textarea.focus();
                textarea.setSelectionRange(textarea.value.length, textarea.value.length);
                li.find('.content textarea:not(.auto-resizable)').addClass('auto-resizable').autoResize({
                    animateDuration: 0
                });
            },
            answerComment (event) {
                let li;
                if (typeof event === 'string') {
                    let comment_id = event;
                    li = $(this.$el).find(`.comments > li[data-comment_id="${comment_id}"]`);
                } else {
                    li = $(event.target).closest('li[data-comment_id]');
                    let comment_id = $(event.target).closest('li[data-comment_id]').data('comment_id');
                }
                let comment_id = $(li).data('comment_id');
                let comment_data = null;
                this.thread_data.comments.forEach((comment, i) => {
                    if (comment.comment_id === comment_id) {
                        comment_data = comment;
                    }
                });
                if (comment_data) {
                    let quote = '[quote=' + comment_data.user_name + ']' + (comment_data.content.replace(/\[quote[^\]]*\].*\[\/quote\]/g, '')).trim() + "[/quote]\n";
                    $(this.$el).find('.writer textarea').val(quote);
                    let textarea = $(this.$el).find('.writer textarea').last()[0];
                    textarea.focus();
                    textarea.setSelectionRange(textarea.value.length, textarea.value.length);
                }
            },
            saveComment (event) {
                let thread = this;
                let li = $(event.target).closest('li[data-comment_id]');
                let comment_id = li.data('comment_id');
                let content = li.find('textarea').val();

                thread.thread_data.comments.forEach((comment) => {
                    if (comment.comment_id === comment_id) {
                        comment.html = content;
                    }
                });

                li.find('.content').removeClass('editing');

                STUDIP.api.PUT(`blubber/threads/${this.thread_data.thread_posting.thread_id}/comments/${comment_id}`, {
                    data: {
                        content: content
                    },
                }).done((output) => {
                    if (this.hasContent(output.content)) {
                        thread.thread_data.comments.forEach((comment) => {
                            if (comment.comment_id === comment_id) {
                                comment.html = output.html;
                                comment.content = output.content;

                                thread.$nextTick(() => {
                                    STUDIP.Markup.element($(thread.$el).find(`.comments > li[data-comment_id="${comment_id}"]`));
                                });
                            }
                        });
                    } else {
                        thread.removeComment(comment_id);
                    }
                    $(thread.$el).find('.writer textarea').focus();
                });
            },
            removeDeletedComments: function (comment_ids) {
                for (let i in comment_ids) {
                    this.removeComment(comment_ids[i]);
                }
            },
            editPreviousComment () {
                if (!$(this.$el).find('.writer textarea').val().trim()) {
                    let comment = $(this.$el).find('.comments li.mine').last();
                    if (comment.length > 0) {
                        this.editComment(comment.data('comment_id'));
                    }
                }
            },
            toggleFollow () {
                STUDIP.Blubber.followunfollow(
                    this.thread_data.thread_posting.thread_id,
                    !this.thread_data.followed
                ).done(state => {
                    this.thread_data.followed = state;
                });
            },
            hasContent (input) {
                return input && input.trim().length > 0;
            }
        },
        directives: {
            scroll: {
                // directive definition
                inserted: function (el) {
                    let thread = $(el).closest('.blubber_thread')[0].__vue__;

                    $(el).on('scroll', (event) => {
                        let top = $(el).scrollTop();
                        let height = $(el).find('.all_content').height();

                        $(el).toggleClass('scrolled', top > 0);

                        thread.$root.display_context_posting = top >= $(el).find('.all_content .thread_posting').height()
                            ? 1
                            : 0;
                        if (thread.thread_data.more_up && top < 1000 && !thread.already_loading_up) {
                            thread.already_loading_up = 1;

                            let earliest_mkdate = thread.thread_data.comments.reduce((min, comment) => {
                                return min === null ? comment.mkdate : Math.min(min, comment.mkdate);
                            }, null);

                            //load older comments
                            STUDIP.api.GET(`blubber/threads/${thread.thread_data.thread_posting.thread_id}/comments`, {
                                data: {
                                    modifier: 'olderthan',
                                    timestamp: earliest_mkdate,
                                    limit: 50
                                }
                            }).done((data) => {
                                top = $(el).scrollTop();
                                thread.addComments(data.comments, false);
                                thread.thread_data.more_up = data.more_up;
                                thread.$nextTick(function () {
                                    //scroll to the position where we were:
                                    let new_height = $(el).find(".all_content").height();
                                    let new_scroll_top = new_height - height + top;
                                    $(el).scrollTo(
                                        new_scroll_top
                                    );
                                });
                            }).done(() => {
                                thread.already_loading_up = 0;
                            });
                        }

                        if (thread.thread_data.more_down && (top > $(thread).find(".scrollable_area .all_content").height() - 1000) && !thread.already_loading_down) {
                            thread.already_loading_down = 1;

                            let latest_mkdate = thread.thread_data.comments.reduce((max, comment) => {
                                return Math.max(max, comment.mkdate);
                            }, null);

                            //load newer comments
                            STUDIP.api.GET(`blubber/threads/${thread.thread_data.thread_posting.thread_id}/comments`, {
                                data: {
                                    modifier: 'newerthan',
                                    timestamp: latest_mkdate,
                                    limit: 50
                                }
                            }).done((data) => {
                                thread.addComments(data.comments, false);
                                thread.thread_data.more_down = data.more_down;
                            }).always(() => {
                                thread.already_loading_down = 0;
                            });
                        }
                    });
                }
            }
        },
        mounted () { //when everything is initialized
            this.$nextTick(function () {
                if (this.thread_data.comments.length > 0) {
                    this.scrollDown();
                }

                $(this.$el).find('.writer textarea').autoResize({
                    animateDuration: 0,
                    // More extra space:
                    extraSpace: 1
                });

                $(this.$el).find('.comments .content .html').each(function () {
                    STUDIP.Markup.element(this);
                });

                if (this.thread_data.thread_posting.thread_id) {
                    let memory = sessionStorage.getItem(`BlubberMemory-Writer-${this.thread_data.thread_posting.thread_id}`);
                    if (memory) {
                        $(this.$el)
                            .find('.writer').addClass('filled')
                            .find('textarea').val(memory);
                    }
                }
            });
        },
        computed: {
            sortedComments () {
                return this.thread_data.comments.sort((a, b) => a.mkdate - b.mkdate);
            },
            writerTextareaPlaceholder() {
                return this.hasContent(this.thread_data.thread_posting.content)
                    ? this.$gettext('Kommentar schreiben. Enter zum Abschicken.')
                    : this.$gettext('Nachricht schreiben. Enter zum Abschicken.');
            }
        },
        updated () {
            this.$nextTick(function () {
                if (this.thread_data.thread_posting.thread_id) {
                    let memory = sessionStorage.getItem('BlubberMemory-Writer-' + this.thread_data.thread_posting.thread_id);
                    $(this.$el).find('.writer textarea').val(memory);
                }
            });
        },
        watch: {
            thread_data (new_data, old_data) {
                if (new_data.thread_posting.thread_id !== old_data.thread_posting.thread_id) {
                    //if the thread got reloaded by a new thread
                    //markup contents
                    this.$nextTick(function () {
                        $(this.$el).find(".comments .content .html").each(function () {
                            STUDIP.Markup.element(this);
                        });
                    });
                    //and scroll down:
                    this.scrollDown();
                }
            }
        }
    }
</script>
