<?php
namespace Widgets;

use Range;

/**
 * This interface defines all functions that a widget should offer.
 *
 * @author  Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @license GPL2 or any later version
 * @since   Stud.IP 4.1
 */
interface WidgetInterface
{
    /**
     * Returns whether the widget is suitable for the given range (and an
     * optional scope).
     *
     * @param Range $range
     * @param mixed $scope
     * @return bool
     */
    public function suitableForRange(Range $range, $scope = null);

    /**
     * Returns the id of the widget.
     *
     * @return int
     */
    public function getId();

    /**
     * Return the name of the widget.
     *
     * @return string
     */
    public function getName();

    /**
     * Returns the description of the widget.
     *
     * @return string
     */
    public function getDescription();

    /**
     * Returns the title of the widget (this is the actual header of the
     * rendered widget.
     *
     * @return string
     */
    public function getTitle();

    /**
     * Returns the content of the widget (this is the actual content of the
     * rendered widget).
     *
     * @return string
     */
    public function getContent(Range $range, $scope);

    /**
     * Connect this widget with an element. This is neccessary for the url
     * generation from inside the widget. Otherwise, there are no means to
     * retrieve the element and/or container the widget is used in.
     *
     * @param Element $element Element to connect with
     */
    public function connectWithElement(Element $element);

    /**
     * Retrieves a previously connected element.
     *
     * @return mixed Element or null if not element has been connected
     */
    public function getElement();

    /**
     * Create a url for a widget's action.
     *
     * @param string $to         Target
     * @param array  $parameters Optional url parameters
     * @return string
     */
    public function url_for($to, $parameters = []);

    /**
     * Sets the defined options for this widget instance. This method is
     * usually only called when a widget container is loaded and the elements
     * are initialized.
     *
     * @param array $options
     */
    public function setOptions(array $options = []);

    /**
     * Returns the options for this widget instance. This might and should be
     * overwritten by a subclass.
     *
     * @return mixed
     */
    public function getOptions();

    /**
     * Returns whether the widget should have a layout or not.
     *
     * @return bool
     * @todo Really neccessary? Seems to got lost in development
     */
    public function hasLayout();

    /**
     * Returns whether this widget instance may be removed from a container.
     *
     * @return bool
     */
    public function mayBeRemoved();

    /**
     * Returns whether this widget instance may be duplicated or used more than
     * once in a container.
     *
     * @return bool
     */
    public function mayBeDuplicated();

    /**
     * Returns a list of possible widget actions.
     *
     * @return array of WidgetAction
     */
    public function getActions(Range $range, $scope);
}
