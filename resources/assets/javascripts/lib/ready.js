/*jslint esversion: 6*/

function ready(callback, top = false) {
    if (top) {
        ready.handlers.unshift({
            type: false,
            callback: callback
        });
    } else {
        ready.handlers.push({
            type: false,
            callback: callback
        });
    }
    return this; // = STUDIP
}
ready.handlers = [];
ready.trigger = function (type, context) {
    ready.handlers.filter(handler => !handler.type || handler.type === type).forEach(handler => {
        handler.callback({
            target: context || document
        });
    });

    let event = $.Event('studip-ready');
    event.target = context || document;
    $(document).trigger(event);
};

function domReady(callback, top = false) {
    if (top) {
        ready.handlers.unshift({
            type: 'dom',
            callback: callback
        });
    } else {
        ready.handlers.push({
            type: 'dom',
            callback: callback
        });
    }
    return this; // = STUDIP
}

function dialogReady(callback, top = false) {
    if (top) {
        ready.handlers.unshift({
            type: 'dialog',
            callback: callback
        });
    } else {
        ready.handlers.push({
            type: 'dialog',
            callback: callback
        });
    }
    return this; // = STUDIP
}

export { ready, domReady, dialogReady };
