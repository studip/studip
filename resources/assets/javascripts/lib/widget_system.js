/*jslint browser: true, esversion: 6*/

import Dialog from './dialog.js';
import extractCallback from './extract_callback.js';
import { api } from './restapi.js';
import crc32 from './crc32.js';

const GRID_CLASSNAME = 'studip-widget-grid';

function renderServerSide(grid, item) {
    return api.POST(['widgets', grid.id, item.el.data().widgetId], {
        data: {
            x: item.x,
            y: item.y,
            width: item.width,
            height: item.height
        }
    }).then((response, status, jqxhr) => {
        var element_id = jqxhr.getResponseHeader('X-Widget-Element-Id');
        item.el.attr('data-element-id', element_id)
            .find('.grid-stack-item-content')
            .replaceWith(response);
        return jqxhr;
    });
}

// Create widget system class
class WidgetSystem
{
    constructor(id, container_element) {
        this.id = id;

        // Initialize grid
        this.grid = $(container_element).addClass(GRID_CLASSNAME).gridstack({
            acceptWidgets: '.widget-to-add',
            width: 6, // TODO: Same as Widgets\Container model
            cellHeight: 'auto',
            handle: '.widget-header',
            resizable: { autoHide: false }
        });

        this.gridstack = this.grid.data('gridstack');

        this.hashcode = crc32(JSON.stringify(this.serialize()));

        // Store new layout after widget was dragged
        this.grid.on('change', () => {
            this.store();
        }).on('added', (event, items) => {
            // Disable updating of items
            this.gridstack.batchUpdate();

            items.forEach((item) => {
                renderServerSide(this, item).done((response, status, jqxhr) => {
                    $(document).trigger('widget-add', [jqxhr]);
                });
            });

            // Update items (resizables as well)
            this.gridstack.commit();
            this.gridstack.enableResize(true, true);
        }).on('click', '.widget-action:not([href])', (event) => {
            // This is quite nasty. Since we use delegates on a specific element
            // and not document, the default confirm handler will not be invoked
            // until after this handler has finished. Thus, we will need to replicate
            // the behaviour of the original data-confirm handler.
            $.Deferred((dfd0) => {
                if (event.isDefaultPrevented()) {
                    dfd0.reject();
                } else if (!$(event.target).attr('data-confirm')) {
                    dfd0.resolve();
                } else {
                    var question = $(event.target).data().confirm;
                    Dialog.confirm(question).then(dfd0.resolve, dfd0.reject);
                }
            }).done(() => {
                var action = $(event.target).closest('[data-action]').data().action;
                var element_id = $(event.target).closest('.grid-stack-item').data('element-id');
                var container_id = this.id; // TODO: this or event.target?
                var path = ['widgets', container_id, action, element_id];

                if ($(event.target).data().hasOwnProperty('admin')) {
                    path.push(1);
                }

                api.POST(path).then((response, status, jqxhr) => {
                    return $.Deferred(function(dfd) {
                        var command = jqxhr.getResponseHeader('X-Widget-Execute');
                        var hasContent = status !== 'nocontent';
                        var is_json = jqxhr.getResponseHeader('Content-Type').match(/json/);
                        var wrapper = $(event.target).closest('.grid-stack-item');
                        var callback;
                        var payload;
                        var timeout;
                        var result = true;
                        if (command) {
                            command = decodeURIComponent(command);
                            callback = extractCallback(command);

                            if (is_json && hasContent) {
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
                            timeout = setTimeout(() => {
                                wrapper.off('transitionrun transitionend');
                                dfd.resolve(response, status, jqxhr);
                            }, 100);

                            wrapper.one('transitionrun', () => {
                                clearTimeout(timeout);
                                wrapper.one('transitionend', () => {
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
                }).done((response, status, jqxhr) => {
                    $(document).trigger(`widget-${action}`, [jqxhr]);
                });
            }).always(() => event.preventDefault());
        }).on('resizestart resizestop', (event) => {
            $(event.target).toggleClass('resizing', event.type === 'resizestart');
        });
    }

    getElement(element_id) {
        var element = this.grid.find(`[data-element-id="${element_id}"]`);
        if (element.length === 0) {
            throw `Unknown element with id ${element_id}`;
        }

        return element;
    }

    store() {
        api.PUT(['widgets', this.id], {
            before: () => {
                var elements = this.serialize();
                var hashcode = JSON.stringify(elements).crc32();
                var result = hashcode !== this.hashcode;

                this.hashcode = hashcode;

                return result;
            },
            data: () => {
                return { elements: this.serialize() };
            },
            async: true
        });
    }

    serialize() {
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

        return result.sort((a, b) => a.y - b.y || a.x - b.x);
    }

    addElement(html, options = {}) {
        var element = $(html).hide();

        this.grid.append(element);
        this.grid.packery('appended', element).packery();
        this.initializeWidgets(element);

        if (!options.hasOwnProperty('refresh') || options.refresh) {
            this.refreshElementLookup();
        }

        element.show();

        if (options.hasOwnProperty('position')) {
            this.grid.packery('fit', element[0], options.position.left, options.position.top);
        }

        return this;
    }

    removeElement(element_id) {
        var element = this.getElement(element_id);
        this.gridstack.removeWidget(element);
    }

    lockElement(element_id, state = true) {
        var element = this.getElement(element_id);

        this.gridstack.locked(element, state);
        this.gridstack.movable(element, !state);
        this.gridstack.resizable(element, !state);

        if (state) {
            $(element).closest('.grid-stack-item').attr('data-gs-locked', '');
        } else {
            $(element).closest('.grid-stack-item').removeAttr('data-gs-locked');
        }
    }

    setRemovableElement(element_id, state = true) {
        var element = this.getElement(element_id);

        if (state) {
            $(element).closest('.grid-stack-item').attr('data-gs-removable', '');
        } else {
            $(element).closest('.grid-stack-item').removeAttr('data-gs-removable');
        }
    }
}

// Create widget system object
const WidgetSystemFacade = {
    cache: {},

    initialize(selector) {
        var element = $(selector);
        var data = element.data().widgetsystem;

        if (!this.cache.hasOwnProperty(data.id)) {
            this.cache[data.id] = new WidgetSystem(data.id, selector);
        }

        return this.cache[data.id];
    },

    get(id) {
        if (!this.cache.hasOwnProperty(id)) {
            throw `Widgetsystem with id ${id} has not been initialized yet`;
        }
        return this.cache[id];
    }
};

export default WidgetSystemFacade;
