/*jslint esversion: 6*/
let options = {
    title: document.title,
    prefix: ''
};

export default {
    get title () {
        return options.title;
    },

    set title (title) {
        options.title = title;
        this.displayTitle();
    },

    get title_prefix () {
        return options.prefix;
    },

    set title_prefix (prefix) {
        options.prefix = prefix;
        this.displayTitle();
    },

    displayTitle () {
        document.title = `${options.prefix}${options.title}`;
    }
};
