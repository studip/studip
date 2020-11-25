/*jslint esversion: 6*/
const Blubber = {
    App: null, //This app is not always available. The app is blubber with a widget and the threads next to it.
    threads: [],
    init () {
        STUDIP.JSUpdater.register('blubber', Blubber.updateState, Blubber.getParamsForPolling);

        if ($('#blubber-index, #messenger-course').length) {
            let panel_data = $('.blubber_panel').data();
            STUDIP.Blubber.App = new Vue({
                el: '#layout_container',
                data: {
                    threads: $('.blubber_threads_widget').data('threads_data'),
                    thread_data: panel_data.thread_data,
                    active_thread: panel_data.active_thread,
                    threads_more_down: panel_data.threads_more_down,
                    waiting: false,
                    display_context_posting: 0
                },
                methods: {
                    changeActiveThread: function (thread_id) {
                        this.waiting = true;
                        let search = jQuery("form.sidebar-search input[name=search]").val();
                        let parameters = search ? {data: {"search": search}} : {};
                        STUDIP.api.GET(`blubber/threads/${thread_id}`, parameters).done((data) => {
                            this.active_thread = thread_id;
                            this.thread_data = data;
                        }).always(() => {
                            this.waiting = false;
                        }).fail(() => {
                            window.alert("Konnte die Konversation nicht laden. Probieren Sie es nachher erneut.".toLocaleString());
                        });
                        for (let i in this.threads) {
                            if (this.threads[i].thread_id === thread_id) {
                                this.threads[i].unseen_comments = 0;
                            }
                        }
                    }
                }
            });

            jQuery("form.sidebar-search").on("submit", function (event) {
                this.waiting = true;
                let search = jQuery("form.sidebar-search input[name=search]").val();
                if ($('#messenger-course').length === 0) {
                    STUDIP.api.GET(`blubber/threads`, {data: {"search": search}}).done((data) => {
                        STUDIP.Blubber.App.threads = data.threads;
                        STUDIP.Blubber.App.threads_more_down = data.more_down;
                        $('.blubber_thread_widget')[0].__vue__.display_more_down = data.more_down;
                    }).always(() => {
                        this.waiting = false;
                    }).fail(() => {
                        window.alert("Konnte die Suche nicht ausführen. Probieren Sie es nachher erneut.".toLocaleString());
                    });
                }
                let parameters = search ? {"search": search} : {"modifier": "olderthan"};
                STUDIP.api.GET(`blubber/threads/` + STUDIP.Blubber.App.active_thread + `/comments`, {data: parameters}).done((data) => {
                    STUDIP.Blubber.App.thread_data.comments = data.comments;
                    STUDIP.Blubber.App.thread_data.more_up = data.more_up;
                    STUDIP.Blubber.App.thread_data.more_down = data.more_down;
                    $('.blubber_thread')[0].__vue__.scrollDown();
                }).always(() => {
                    this.waiting = false;
                }).fail(() => {
                    window.alert("Konnte die Suche nicht ausführen. Probieren Sie es nachher erneut.".toLocaleString());
                });
                event.preventDefault();
                return false;
            });
            jQuery('#blubber-index, #messenger-course').on("click", 'a.blubber_hashtag', function (event) {
                let tag = jQuery(this).closest("a").data("tag");
                jQuery("form.sidebar-search input[name=search]").val("#" + tag);
                jQuery("form.sidebar-search").trigger("submit");
                event.preventDefault();
                return false;
            });
        }

        $(document).on('dialog-open', function() {
            $('.studip-dialog .blubber_panel').each(function () {
                let panel_data = $(this).data();
                new Vue({
                    el: this,
                    data: {
                        threads: panel_data.threads_data,
                        thread_data: panel_data.thread_data,
                        active_thread: panel_data.active_thread,
                        threads_more_down: panel_data.threads_more_down,
                        waiting: false,
                        display_context_posting: 0
                    }
                });
            });
        });
    },
    updateState(datagram) {
        for (const [method, data] of Object.entries(datagram)) {
            if (method in Blubber) {
                Blubber[method](data);
            }
        }
    },
    getParamsForPolling () {
        const data = {
            threads: [],
        };
        $('.blubber_thread').each(function () {
            data.threads.push(this.__vue__._props.thread_data.thread_posting.thread_id);
        });

        return data;
    },
    addNewComments (blubberdata) {
        $('.blubber_thread').each(function () {
            for (let thread_id in blubberdata) {
                if (this.__vue__._props.thread_data.thread_posting.thread_id === thread_id) {
                    this.__vue__.addComments(blubberdata[thread_id], true);
                    this.__vue__.scrollDown();
                }
            }
        });
    },
    removeDeletedComments: function (comment_ids) {
        $('.blubber_thread').each(function () {
            this.__vue__.removeDeletedComments(comment_ids);
        });
    },
    updateThreadWidget (threaddata) {
        for (let i in threaddata) {
            let exists = false;
            for (let k in STUDIP.Blubber.App.threads) {
                if (STUDIP.Blubber.App.threads[k].thread_id == threaddata[i].thread_id) {
                    exists = true;
                    STUDIP.Blubber.App.threads[k].name = threaddata[i].name;
                    STUDIP.Blubber.App.threads[k].timestamp = threaddata[i].timestamp;
                    STUDIP.Blubber.App.threads[k].avatar = threaddata[i].avatar;
                }
            }
            if (!exists) {
                STUDIP.Blubber.App.threads.push(threaddata[i]);
            }
        }
    },
    refreshThread (data) {
        STUDIP.Blubber.App.changeActiveThread(data.thread_id);
    },
    followunfollow () {
        let thread_id = jQuery(this).data("thread_id");
        let unfollowed = jQuery(this).hasClass("unfollowed");
        if (unfollowed) {
            jQuery(this).removeClass("unfollowed");
            unfollowed = false;
        } else {
            jQuery(this).addClass("unfollowed");
            unfollowed = true;
        }
        if (unfollowed) {
            STUDIP.api.POST(`blubber/threads/${thread_id}/unfollow`);
        } else {
            STUDIP.api.DELETE(`blubber/threads/${thread_id}/unfollow`);
        }
        return false;
    },
    Composer: {
        vue: null,
        init: function () {
            STUDIP.Blubber.Composer.vue = new Vue({
                el: '#blubber_contact_ids',
                data: {
                    users: []
                },
                methods: {
                    addUser: function (user_id, name) {
                        this.users.push({
                            user_id: user_id,
                            name: name
                        });
                    },
                    removeUser: function (event) {
                        let user_id = $(event.target).closest('li').find('input').val();
                        for (let i in this.users) {
                            if (this.users[i].user_id === user_id) {
                                this.$delete(this.users, i);
                            }
                        }
                    },
                    clearUsers: function () {
                        this.users = [];
                    }
                }
            });
        }
    }
};

export default Blubber;
