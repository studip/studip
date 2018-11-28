import Dialog from './dialog.js';
import extractCallback from './extract_callback.js';
import { api } from './restapi.js';

function makeCRCTable() {
    var c,
        crcTable = [],
        n,
        k;
    for (n = 0; n < 256; n += 1) {
        c = n;
        for (k = 0; k < 8; k += 1) {
            c = c & 1 ? 0xedb88320 ^ (c >>> 1) : c >>> 1;
        }
        crcTable[n] = c;
    }
    return crcTable;
}

var crcTable = makeCRCTable();

const crc32 = function() {
    var crc = 0 ^ -1,
        i;

    for (i = 0; i < this.length; i += 1) {
        crc = (crc >>> 8) ^ crcTable[(crc ^ this.charCodeAt(i)) & 0xff];
    }

    return (crc ^ -1) >>> 0;
};

var GRID_CLASSNAME = 'studip-widget-grid';

var renderServerSide = function(grid, item) {
    return api
        .POST(['widgets', grid.id, item.el.data().widgetId], {
            data: {
                x: item.x,
                y: item.y,
                width: item.width,
                height: item.height
            }
        })
        .then(function(response, status, jqxhr) {
            var element_id = jqxhr.getResponseHeader('X-Widget-Element-Id');
            item.el
                .attr('data-element-id', element_id)
                .find('.grid-stack-item-content')
                .replaceWith(response);
            return jqxhr;
        });
};

// Create widget system class
function WidgetSystem(id, container_element) {
    this.id = id;

    // Initialize grid
    this.grid = $(container_element)
        .addClass(GRID_CLASSNAME)
        .gridstack({
            acceptWidgets: '.widget-to-add',
            width: 6, // TODO: Same as Widgets\Container model
            cellHeight: 'auto',
            handle: '.widget-header',
            resizable: { autoHide: false }
        });

    this.gridstack = this.grid.data('gridstack');

    this.hashcode = crc32(JSON.stringify(this.serialize()));

    // Store new layout after widget was dragged
    this.grid
        .on(
            'change',
            function() {
                this.store();
            }.bind(this)
        )
        .on(
            'added',
            function(event, items) {
                // Disable updating of items
                this.gridstack.batchUpdate();

                items.forEach(
                    function(item) {
                        renderServerSide(this, item).done(function(response, status, jqxhr) {
                            $(document).trigger('widget-add', [jqxhr]);
                        });
                    }.bind(this)
                );

                // Update items (resizables as well)
                this.gridstack.commit();
                this.gridstack.enableResize(true, true);
            }.bind(this)
        )
        .on(
            'click',
            '.widget-action:not([href])',
            function(event) {
                // This is quite nasty. Since we use delegates on a specific element
                // and not document, the default confirm handler will not be invoked
                // until after this handler has finished. Thus, we will need to replicate
                // the behaviour of the original data-confirm handler.
                $.Deferred(function(dfd0) {
                    if (event.isDefaultPrevented()) {
                        dfd0.reject();
                    } else if (!$(event.target).attr('data-confirm')) {
                        dfd0.resolve();
                    } else {
                        var question = $(event.target).data().confirm;
                        Dialog.confirm(question).then(dfd0.resolve, dfd0.reject);
                    }
                })
                    .done(
                        function() {
                            var action = $(event.target)
                                    .closest('[data-action]')
                                    .data().action,
                                element_id = $(event.target)
                                    .closest('.grid-stack-item')
                                    .data('element-id'),
                                container_id = this.id,
                                path = ['widgets', container_id, action, element_id];

                            if (
                                $(event.target)
                                    .data()
                                    .hasOwnProperty('admin')
                            ) {
                                path.push(1);
                            }

                            api.POST(path)
                                .then(
                                    function(response, status, jqxhr) {
                                        return $.Deferred(function(dfd) {
                                            var command = jqxhr.getResponseHeader('X-Widget-Execute'),
                                                hasContent = status !== 'nocontent',
                                                wrapper = $(event.target).closest('.grid-stack-item'),
                                                callback,
                                                payload,
                                                timeout,
                                                result = true;
                                            if (command) {
                                                command = decodeURIComponent(command);
                                                callback = extractCallback(command);

                                                if (
                                                    jqxhr.getResponseHeader('Content-Type').match(/json/) &&
                                                    hasContent
                                                ) {
                                                    try {
                                                        payload = $.parseJSON(jqxhr.responseText);
                                                    } catch (e) {
                                                        console.log('error parsing json response', jqxhr.responseText);
                                                        payload = null;
                                                    }
                                                } else {
                                                    payload = jqxhr.responseText;
                                                }

                                                // TODO: This will try to detect whether the callback triggers
                                                //       any css transitions. If so, the layout will be repacked
                                                //       when the transition has ended. Otherwise it is repacked
                                                //       after a short delay
                                                timeout = setTimeout(function() {
                                                    wrapper.off('transitionrun transitionend');
                                                    dfd.resolve(response, status, jqxhr);
                                                }, 100);

                                                wrapper.one('transitionrun', function() {
                                                    clearTimeout(timeout);
                                                    wrapper.one('transitionend', function() {
                                                        dfd.resolve(response, status, jqxhr);
                                                    });
                                                });

                                                result = callback(payload);
                                            }
                                            if (result !== false && hasContent) {
                                                $('.widget-content', wrapper).html(response);
                                                dfd.resolve(response, status, jqxhr);
                                            }
                                        }).promise();
                                    }.bind(this)
                                )
                                .done(function(response, status, jqxhr) {
                                    $(document).trigger('widget-' + action, [jqxhr]);
                                });
                        }.bind(this)
                    )
                    .always(function() {
                        event.preventDefault();
                    });
            }.bind(this)
        )
        .on('resizestart resizestop', function(event) {
            $(this).toggleClass('resizing', event.type === 'resizestart');
        });
}

WidgetSystem.prototype.getElement = function(element_id) {
    var element = this.grid.find('[data-element-id="' + element_id + '"]');
    if (element.length === 0) {
        throw 'Unknown element with id ' + element_id;
    }

    return element;
};

WidgetSystem.prototype.store = function() {
    api.PUT(['widgets', this.id], {
        before: function() {
            var elements = this.serialize(),
                hashcode = JSON.stringify(elements).crc32(),
                result = hashcode !== this.hashcode;
            this.hashcode = hashcode;
            return result;
        }.bind(this),
        data: function() {
            return { elements: this.serialize() };
        }.bind(this),
        async: true
    });
};

WidgetSystem.prototype.serialize = function() {
    var result = [];
    this.gridstack.grid.nodes.forEach(function(node) {
        result.push({
            id: $(node.el).data('element-id'),
            x: node.x,
            y: node.y,
            width: node.width,
            height: node.height
        });
    });

    return result.sort(function(a, b) {
        return a.y - b.y || a.x - b.x;
    });
};

WidgetSystem.prototype.addElement = function(html, options) {
    var element = $(html).hide();
    this.grid.append(element);
    this.grid.packery('appended', element).packery();
    this.initializeWidgets(element);

    options = options || {};

    if (!options.hasOwnProperty('refresh') || options.refresh) {
        this.refreshElementLookup();
    }

    element.show();

    if (options.hasOwnProperty('position')) {
        this.grid.packery('fit', element[0], options.position.left, options.position.top);
    }

    return this;
};

WidgetSystem.prototype.removeElement = function(element_id) {
    var element = this.getElement(element_id);
    this.gridstack.removeWidget(element);
};

WidgetSystem.prototype.lockElement = function(element_id, state) {
    if (state === undefined) {
        state = true;
    }

    var element = this.getElement(element_id);
    this.gridstack.locked(element, state);
    this.gridstack.movable(element, !state);
    this.gridstack.resizable(element, !state);

    if (state) {
        $(element)
            .closest('.grid-stack-item')
            .attr('data-gs-locked', '');
    } else {
        $(element)
            .closest('.grid-stack-item')
            .removeAttr('data-gs-locked');
    }
};

WidgetSystem.prototype.setRemovableElement = function(element_id, state) {
    if (state === undefined) {
        state = true;
    }

    var element = this.getElement(element_id);

    if (state) {
        $(element)
            .closest('.grid-stack-item')
            .attr('data-gs-removable', '');
    } else {
        $(element)
            .closest('.grid-stack-item')
            .removeAttr('data-gs-removable');
    }
};

// Create widget system object
const WidgetSystemFacade = {
    cache: {},

    initialize(selector) {
        var element = $(selector),
            data = element.data().widgetsystem;

        if (!this.cache.hasOwnProperty(data.id)) {
            this.cache[data.id] = new WidgetSystem(data.id, selector);
        }

        return this.cache[data.id];
    },

    get(id) {
        if (!this.cache.hasOwnProperty(id)) {
            throw 'Widgetsystem with id ' + id + ' has not been initialized yet';
        }
        return this.cache[id];
    }
};

export default WidgetSystemFacade;
