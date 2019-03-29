const Overlay = {
    delay: 300,
    element: null,
    selector: '.ui-front.modal-overlay',
    timeout: null
};

Overlay.reset = function() {
    if (this.timeout !== null) {
        clearTimeout(this.timeout);
        this.timeout = null;
    }
};

Overlay.schedule = function(callback, delay) {
    this.reset();
    if (delay !== undefined && !delay) {
        callback.call(this);
    } else {
        this.timeout = setTimeout(callback.bind(this), this.delay);
    }
};

Overlay.show = function(ajax, containment, secure, callback, delay) {
    this.schedule(function() {
        if (this.element === null) {
            containment = containment || 'body';

            this.element = $('<div class="ui-front modal-overlay">');
            if (ajax) {
                this.element.addClass('modal-overlay-ajax');
                if (ajax === 'dark') {
                    this.element.addClass('modal-overlay-dark');
                }
            }
            if (containment !== 'body') {
                this.element.addClass('modal-overlay-local');
            } else {
                // Blur background
                $('#layout_wrapper').addClass('has-overlay');
            }
            this.element.appendTo(containment);
        }

        if (secure) {
            $(window).on('beforeunload.overlay', Overlay.securityHandler);
        }
        if ($.type(callback) === 'function') {
            callback.call(this);
        }
    }, delay);
};

Overlay.hide = function(delay) {
    this.schedule(function() {
        if (this.element !== null) {
            this.element.remove();
            this.element = null;
        }

        $('#layout_wrapper').removeClass('has-overlay');
        $(window).off('beforeunload.overlay');
    }, delay);
};

// Secure the overlay
Overlay.securityHandler = function(event) {
    event = event || window.event || {};
    event.returnValue = 'Ihre Eingaben wurden bislang noch nicht gespeichert.'.toLocaleString();
    return event.returnValue;
};

// Allows progress information
Overlay.showProgress = function(title, ajax, secure, delay) {
    this.show(
        ajax,
        null,
        secure,
        function() {
            if ($('h1', this.selector).length === 0) {
                $(this.selector)
                    .append($('<h1>').text(title))
                    .append('<progress max="100" value="0">')
                    .append('<ul class="overlay-progress-log">');
            }
        },
        delay
    );
};

Overlay.updateProgress = function(percent, message) {
    $('progress', this.selector).val(percent);
    if (message) {
        this.progressInfo(message);
    }
};

Overlay.progressInfo = function(message) {
    var li = $('<li>').text(message);
    $('.overlay-progress-log', this.selector).prepend(li);
    li.delay(1000).hide('fade', 300, function() {
        $(this).remove();
    });
};

export default Overlay;
