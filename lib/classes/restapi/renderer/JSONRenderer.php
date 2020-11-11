<?php
namespace RESTAPI\Renderer;

/**
 * Content renderer for json content.
 *
 * @author     Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @author     <mlunzena@uos.de>
 * @license    GPL 2 or later
 * @since      Stud.IP 3.0
 * @deprecated Since Stud.IP 5.0. Will be removed in Stud.IP 5.2.
 */
class JSONRenderer extends DefaultRenderer
{
    public function contentType()
    {
        return 'application/json';
    }

    public function extension()
    {
        return '.json';
    }

    public function render($response)
    {
        if (!isset($response['Content-Type'])) {
            $response['Content-Type'] = $this->contentType() . ';charset=utf-8';
        }

        if (isset($response->body)) {
            $response->body = json_encode($response->body);
        }
    }
}
