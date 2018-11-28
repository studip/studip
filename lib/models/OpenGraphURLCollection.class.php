<?php
/**
 * Specialized version of SimpleORMapCollection that accepts only
 * OpenGraphURL objects and provides a method that renders the collection
 * to html.
 *
 * @author  Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @license GPL2 or any later version
 */
class OpenGraphURLCollection extends SimpleORMapCollection
{
    /**
     * Returns the class name of the object this collection accepts.
     *
     * @return String containing the provided class name 'OpenGraphURL'
     */
    public function getClassName()
    {
        return 'OpenGraphURL';
    }

    /**
     * Renders the collection to html. The collection is usually wrapped in
     * a wrapper element but in some edge cases you might want to choose not
     * to do so. This is enabled by the only parameter this method accepts.
     *
     * Note: I advice you not to exclude the wrapper. The javascript that
     * handles the collection will fail!
     *
     * @param bool $with_wrapper Should the collection be wrapped in a wrapper
     *                           element (optional, defaults to true)
     * @return String containing the rendered collection as a html chunk
     */
    public function render($with_wrapper = true)
    {
        if (!Config::Get()->OPENGRAPH_ENABLE || count($this) === 0) {
            return '';
        }

        $rendered_urls = $this->sendMessage('render');
        $rendered_urls = array_filter($rendered_urls);

        if (count($rendered_urls) === 0) {
            return '';
        }

        if ($with_wrapper) {
            $template = $GLOBALS['template_factory']->open('shared/opengraph-container.php');
            $template->urls = $rendered_urls;
            $result = $template->render();
        } else {
            $result = implode("\n", $rendered_urls);
        }

        return $result;
    }
}
