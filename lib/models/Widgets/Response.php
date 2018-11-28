<?php
namespace Widgets;

use Flexi_Template;
use RESTAPI\RouteMap;
use Trails_Response;

/**
 * Widget API/execution response.
 *
 * @todo This is rather ugly and annoying besides the RESTAPI\Response and
 *       Trails_Response which basically do the same thing (working with http
 *       status, headers and responses). Ideally all of these should use the
 *       same PSR-7 compatible HTTP Response object.
 *
 * @author  Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @license GPL2 or any later version
 */
class Response
{
    protected $headers = [];
    protected $status  = 200;
    protected $content = false;

    protected $variables;

    /**
     * Constructs this response with a predefined set of variables that are
     * used to replace placeholders in headers.
     *
     * @param array $variables Set of variables
     */
    public function __construct(array $variables = [])
    {
        $this->variables = $variables;
    }

    /**
     * Adds a new header to the response. The header name and content may
     * contain placeholders in the form ":var" which are replaced with the
     * predefined set of variables in the constructor.
     *
     * @param string $name    Name of the header
     * @param string $contetn Content of the header
     * @param bool   $append  Should the header be appended to a previously
     *                        added header with the same name or should it
     *                        replace that header (defaults to false, replace)
     */
    public function addHeader($name, $content, $append = false)
    {
        // TODO: This is nasty
        foreach ($this->variables as $key => $value) {
            $name    = str_replace(":{$key}", $value, $name);
            $content = str_replace(":{$key}", $value, $content);
        }

        // TODO: mit tleilax absprechen; mit rawurlencode gibt es probleme!
        // $content = rawurlencode($content);

        if (!$append || !isset($this->headers[$name])) {
            $this->headers[$name] = $content;
        } else {
            $this->headers[$name] .= ", {$content}";
        }
    }

    /**
     * Adds an associative array of headers (the key is name of the header,
     * the value is the content).
     *
     * @param array $headers Array of headers
     * @param bool  $append  Append or replace existing headers (@see addheader)
     */
    public function addHeaders(array $headers, $append = false)
    {
        foreach ($headers as $name => $content) {
            $this->addHeader($name, $content, $append);
        }
    }

    /**
     * Returns the previously set headers.
     *
     * @return array of header (key = name, value = content)
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * Sets the http status of the response.
     *
     * @param int $status Status code of the response
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * Returns the status code of the response. If the status is not a redirect
     * (300), an error (400) or an exception (500) and no content has been set,
     * the status will always return 204 (= No Content).
     *
     * @return int http status of the response
     */
    public function getStatus()
    {
        return ($this->content === false && $this->status < 300)
             ? 204
             : $this->status;
    }

    /**
     * Sets the content of the response.
     *
     * @param mixed $content Content of the response (might be a string or
     *                       a flexi template)
     */
    public function setContent($content)
    {
        $this->content = $content;
    }

    /**
     * Returns the content of the response. If the content was set as a flexi
     * template, this will return the rendered template.
     *
     * @return string Content of the response
     */
    public function getContent()
    {
        return $this->content instanceof Flexi_Template
             ? $this->content->render()
             : $this->content;
    }

    /**
     * Connects this response with a route map of the Stud.IP core api.
     * This means, that this response is mapped to the route map's response
     * with all it's headers, the status and the content.
     *
     * @param RouteMap $map The connected route map
     */
    public function connectWithRouteMap(RouteMap $map)
    {
        $map->status($this->getStatus());
        $map->headers($this->getHeaders());
        $map->body($this->getContent());
    }

    /**
     * Connects this response with the response of a trails controller.
     * This means, that this response is mapped to the trails response
     * with all it's headers, the status and the content.
     *
     * @param Trails_Response $response The connected trails response
     */
    public function connectWithTrailsResponse(Trails_Response $response)
    {
        $response->set_status($this->getStatus());
        foreach ($this->getHeaders() as $name => $content) {
            $response->add_header($name, $content);
        }
        $response->set_body($this->getContent());
    }
}
