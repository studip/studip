/*jslint esversion: 6*/

import CSS from './css.js';

/**
 * Tooltip library for Stud.IP
 *
 * @author Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @copyright Stud.IP Core Group 2014
 * @license GPL2 or any later version
 * @since Stud.IP 3.1
 */

let count = 0;
let threshold = 0;

class Tooltip {
    static get count() {
        return count;
    }

    static set count(value) {
        count = value;
    }

    // Threshold used for "edge detection" (imagine a padding along the edges)
    static get threshold() {
        return threshold;
    }

    static set threshold(value) {
        threshold = value;
    }

    /**
     * Returns a new unique id of a tooltip.
     *
     * @return {string} Unique id
     * @static
     */
    static getId() {
        const id = `studip-tooltip-${Tooltip.count}`;
        Tooltip.count += 1;
        return id;
    }

    /**
     * Constructs a new tooltip at given location with given content.
     * The applied css class may be changed by the fourth parameter.
     *
     * @class
     * @classdesc Stud.IP tooltips provide an improved layout and handling
     *            of contents (including html) than the browser's default
     *            tooltip through title attribute would
     *
     * @param {int} x - Horizontal position of the tooltip
     * @param {int} y - Vertical position of the tooltip
     * @param {string} content - Content of the tooltip (may be html)
     * @param {string} css_class - Optional name of the applied css class /
     *                             defaults to 'studip-tooltip'
     */
    constructor(x, y, content, css_class) {
        // Obtain unique id of the tooltip
        this.id = Tooltip.getId();

        // Create dom element of the tooltip, apply id and class and attach
        // to dom
        this.element = $('<div>');
        this.element.addClass(css_class || 'studip-tooltip');
        this.element.attr('id', this.id);
        this.element.appendTo('body');

        // Set position and content and paint the tooltip
        this.position(x, y);
        this.update(content);
        this.paint();
    }

    /**
     * Translates the arrow(s) under a tooltip using css3 translate
     * transforms. This is needed at the edges of the screen.
     * This implies that a current browser is used. The translation could
     * also be achieved by adjusting margins but that way we would need
     * to hardcode values into this function since it's a struggle to
     * obtain the neccessary values from the CSS pseudo selectors in JS.
     *
     * Internal, css rules are dynamically created and applied to the current
     * document by using the methods provided in the file studip-css.js.
     *
     * @param {int} x - Horizontal offset
     * @param {int} y - Vertical offset
     */
    translateArrows(x, y) {
        CSS.removeRule(`#${this.id}::before`);
        CSS.removeRule(`#${this.id}::after`);

        if (x !== 0 || y !== 0) {
            const rule = `translate(${x}px, ${y}px);`;
            CSS.addRule(`#${this.id}::before`, { transform: rule }, ['-ms-', '-webkit-']);
            CSS.addRule(`#${this.id}::after`, { transform: rule }, ['-ms-', '-webkit-']);
        }
    }

    /**
     * Updates the position of the tooltip.
     *
     * @param {int} x - Horizontal position of the tooltip
     * @param {int} y - Vertical position of the tooltip
     */
    position(x, y) {
        this.x = x;
        this.y = y;
    }

    /**
     * Updates the contents of the tooltip.
     *
     * @param {string} content - Content of the tooltip (may be html)
     */
    update(content) {
        this.element.html(content);
    }

    /**
     * "Paints" the tooltip. This method actually computes the dimensions of
     * the tooltips, checks for screen edges and calculates the actual offset
     * in the current document.
     * This method is neccessary due to the fact that position and content
     * can be changed apart from each other.
     * Thus: Don't forget to repaint after adjusting any of the two.
     */
    paint() {
        const width = this.element.outerWidth(true);
        const height = this.element.outerHeight(true);
        const maxWidth = $(document).width();
        let x = this.x - width / 2;
        let y = this.y - height;
        let arrowOffset = 0;

        if (x < Tooltip.threshold) {
            arrowOffset = x - Tooltip.threshold;
            x = Tooltip.threshold;
        } else if (x + width > maxWidth - Tooltip.threshold) {
            arrowOffset = x + width - maxWidth + Tooltip.threshold;
            x = maxWidth - width - Tooltip.threshold;
        }
        this.translateArrows(arrowOffset, 0);

        this.element.css({
            left: x,
            top: y
        });
    }

    /**
     * Toggles the visibility of the tooltip. If no state is provided,
     * the tooltip will be hidden if visible and vice versa. Pretty straight
     * forward and no surprises here.
     * This method implicitely calls paint before a tooltip is shown (in case
     * it was forgotten).
     *
     * @param {bool} visible - Optional visibility parameter to set the
     *                         tooltip to a certain state
     */
    toggle(visible) {
        if (visible) {
            this.paint();
        }
        this.element.toggle(visible);
    }

    /**
     * Reveals the tooltip.
     *
     * @see Tooltip.toggle
     */
    show() {
        this.toggle(true);
    }

    /**
     * Hides the tooltip.
     *
     * @see Tooltip.toggle
     */
    hide() {
        this.toggle(false);
    }

    /**
     * Removes the tooltip
     */
    remove() {
        this.element.remove();
    }
}

export default Tooltip;
