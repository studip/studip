<?php
namespace RESTAPI\Renderer;

/**
 * Debug content renderer.
 *
 * @author  Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @author  <mlunzena@uos.de>
 * @license GPL 2 or later
 * @since   Stud.IP 3.0
 */
class DebugRenderer extends DefaultRenderer
{
    /**
     * Returns an associated content type.
     */
    public function contentType()
    {
        return 'text/plain';
    }

    /**
     * Returns an associated extension.
     */
    public function extension()
    {
        return '.debug';
    }

    /**
     * Response transformation function.
     *
     * @param \RESTAPI\Response $response  the response to transform
     */
    public function render($response)
    {
        if (!isset($response['Content-Type'])) {
            $response['Content-Type'] = $this->contentType() . ';charset=utf-8';
        }

        $debug = function ($label, $data) {
            echo str_pad('', 78, '=') . PHP_EOL;
            echo str_pad('- ' . $label, 77, ' ') . '-' . PHP_EOL;
            echo str_pad('', 78, '=') . PHP_EOL;
            var_export($data);
            echo PHP_EOL;
        };

        ob_start();
        $debug('Response Status', $response->status);
        $debug('Response Header', $response->headers);
        $debug('Response Body',   $response->body);
        $debug('Request', $GLOBALS['_' . $_SERVER['REQUEST_METHOD']]);
        $response->body = ob_get_clean();
    }
}
