import Dialog from './dialog.js';

function sprintf(string) {
    var args = arguments,
        index = 1;
    return string.replace(/%(s|u)/g, function(match, modifier) {
        if (index > args.length) {
            throw 'Invalid sprintf usage - not enough arguments';
        }
        var value = args[index];

        if (modifier === 'u') {
            value = parseInt(value, 10);
        }

        index += 1;

        return String(value);
    });
}

const Lightbox = {
    max_width: false,
    max_height: false,
    extra_height: 55, // TODO: While this seems to work, hardcoded values suck
    images: [],
    current: false,
    show: function(index) {
        this.current = index || 0;

        var image = new Image();
        image.onload = $.proxy(this, 'onload', image);
        image.src = this.getImage().src;
    },
    onload: function(image) {
        var wrapper = $('<div class="wrapper">');
        $('<a href="#" class="previous">').appendTo(wrapper);
        $('<a href="#" class="next">').appendTo(wrapper);

        wrapper.addClass(this.getClasses()).css({
            backgroundImage: sprintf('url(%s)', this.getImage().src)
        });

        $(document).one('dialog-open.lightbox', $.proxy(this, 'registerEvents'));

        Dialog.show(wrapper, {
            buttons: false,
            dialogClass: 'studip-lightbox',
            id: 'lightbox',
            resize: false,
            size: this.getSize(image),
            title: this.getTitle(),
            wikilink: false
        });
    },
    getImage: function() {
        return this.images[this.current];
    },
    getTitle: function() {
        var img = this.images[this.current],
            title = [];
        if (img.title) {
            title.push(img.title);
        }

        if (this.images.length > 1) {
            title.unshift(sprintf('Bild %u von %u'.toLocaleString(), this.current + 1, this.images.length));
        }
        return title.join(': ');
    },
    getClasses: function() {
        var classes = [];
        if (this.current === 0) {
            classes.push('first');
        }
        if (this.current === this.images.length - 1) {
            classes.push('last');
        }
        return classes.join(' ');
    },
    getSize: function(image) {
        var width = image.width,
            height = image.height;

        if (width > this.max_width) {
            height *= this.max_width / width;
            width = this.max_width;
        }
        if (height > this.max_height) {
            width *= this.max_height / height;
            height = this.max_height;
        }

        return Math.floor(width) + 'x' + Math.floor(height + this.extra_height);
    },
    setImages: function(images) {
        if (typeof images === 'string') {
            images = $(images);
        }
        if (images instanceof jQuery) {
            images = images.map(function() {
                return {
                    src: $(this).attr('href'),
                    title: $(this).data().title || $(this).attr('title')
                };
            });
        }
        this.images = images;
    },
    init: function() {
        // Values should match the ones in studip-dialog.js (this should be more generic)
        this.max_width = $(window).width() * 0.95;
        this.max_height = $(window).height() * 0.9 - Lightbox.extra_height;
    },
    registerEvents: function() {
        $('.studip-lightbox')
            .on('click', 'a.previous', function() {
                Lightbox.show(Lightbox.current - 1);
                return false;
            })
            .on('click', 'a.next', function() {
                Lightbox.show(Lightbox.current + 1);
                return false;
            });

        $(document)
            .on('keyup.lightbox', function(event) {
                if (event.keyCode === 37) {
                    $('.studip-lightbox .previous:visible').click();
                } else if (event.keyCode === 39) {
                    $('.studip-lightbox .next:visible').click();
                } else if (event.keyCode === 27) {
                    Dialog.close({id: 'lightbox'});
                } else {
                    return;
                }

                return false;
            })
            .one('dialog-close', $.proxy(this, 'unregisterEvents'));
    },
    unregisterEvents: function() {
        $(document).off('.lightbox');
    }
};

export default Lightbox;
