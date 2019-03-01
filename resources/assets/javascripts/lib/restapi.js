import Overlay from './overlay.js';

// Actual RESTAPI object
function RESTAPI() {
    this.total_requests = 0;
    this.request_count = 0;
    this.queue = [];
}

RESTAPI.supportedMethods = ['GET', 'HEAD', 'POST', 'PUT', 'PATCH', 'OPTIONS', 'DELETE'];

// Helper function that normalizes options
RESTAPI.adjustOptions = function(options) {
    return $.extend(
        {},
        {
            method: 'get',
            parameters: {},
            headers: {},
            data: {},
            overlay: false,
            async: false,
            before: false
        },
        options || {}
    );
};

RESTAPI.prototype.request = function(url, options) {
    // Normalize parameters
    if ($.type(url) === 'array') {
        // Remove empty trailing chunks
        while (url[url.lenth - 1] === '') {
            delete url[url.length - 1];
        }
        // Convert array to string
        url = url.join('/');
    }

    options = RESTAPI.adjustOptions(options);

    var deferred;

    if (options.async && this.request_count > 0) {
        // Request should be sent asynchronous after every other request
        // is finished. The configuration for this particular request is
        // stored in a deferred which is then queued for execution.
        deferred = $.Deferred();
        deferred.then(
            function() {
                this.request(url, options);
            }.bind(this)
        );

        this.queue.push(deferred);
    } else if ($.isFunction(options.before) && !options.before()) {
        // A before function was defined and returned false, so the request
        // is canceled
        deferred = $.Deferred(function(dfd) {
            dfd.reject();
        }).promise();
    } else {
        // Increase request counters, show overlay if neccessary
        if (this.request_count === 0 && options.overlay) {
            Overlay.show(true, null, true);
        }
        this.request_count += 1;
        this.total_requests += 1;

        // Actual request
        deferred = $.ajax(STUDIP.URLHelper.getURL('api.php/' + url), {
            method: options.method.toUpperCase(),
            data: $.isFunction(options.data) ? options.data() : options.data,
            headers: options.headers
        }).always(
            function() {
                // Decrease request counter, remove overlay if neccessary
                this.request_count -= 1;
                if (this.request_count === 0 && options.overlay) {
                    Overlay.hide();
                }
            }.bind(this)
        );
    }
    return deferred
        .always(
            function() {
                // Check if any request was queued
                if (this.request_count === 0 && this.queue.length > 0) {
                    this.queue.shift().resolve();
                }
            }.bind(this)
        )
        .promise();
};

// Create shortcut methods for easier access by method
RESTAPI.supportedMethods.forEach(function(method) {
    RESTAPI.prototype[method] = function(url, options) {
        options = RESTAPI.adjustOptions(options);
        options.method = method;

        return RESTAPI.prototype.request.call(this, url, options);
    };
});

export default RESTAPI;

export const api = new RESTAPI();
