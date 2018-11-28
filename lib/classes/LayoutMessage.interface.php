<?php
/**
 * Generic interface for messages that may be displayed in Stud.IP at the
 * top of the layout's content.
 *
 * @author  Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @license GPL2 or any later version
 * @since   Stud.IP 4.2
 */
interface LayoutMessage
{
    /**
     * Renders the message as html.
     *
     * @return string
     */
    public function __toString();
}
