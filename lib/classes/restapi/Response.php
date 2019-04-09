<?php
namespace RESTAPI;

/**
 * Response class for the rest api
 *
 * @author  <mlunzena@uos.de>
 * @license GPL 2 or later
 * @since   Stud.IP 3.0
 */
class Response implements \ArrayAccess
{
    public $body, $status, $headers;

    /**
     * Constructor, sets vital information if provided.
     *
     * @param String $body    Body contents of the response, optional,
     *                        defaults to empty string
     * @param int    $status  HTTP status code, optional, defaults to 200
     * @param Array  $headers HTTP headers, optional, defaults to no headers
     */
    public function __construct($body = '', $status = 200, $headers = [])
    {
        $this->body = $body;
        $this->status = (int) $status;
        $this->headers = (array) $headers;
    }

    /**
     * Detects whether the response status is of success type (HTTP status 2xx)
     *
     * @return bool True if status is of success type, false otherwise
     */
    public function isSuccess()
    {
        return 200 <= $this->status && $this->status <= 299;
    }

    /**
     * Finishes the response with the given response renderer.
     *
     * @param RESTAPI\Renderer\DefaultRenderer $content_renderer Used response
     *                                         renderer, only applied if body
     *                                         is not a callable closure
     */
    public function finish($content_renderer)
    {
        if (!is_callable($this->body)) {
            $content_renderer->render($this);
        }
    }

    /**
     * Sends the response.
     */
    public function output()
    {
        if (isset($this->status)) {
            if (mb_strpos(PHP_SAPI, 'cgi') === 0) {
                $this->sendHeader(sprintf('Status: %d %s', $this->status, $this->reason()));
            } else {
                $this->sendHeader(sprintf('HTTP/1.1 %d %s', $this->status, $this->reason()));
            }
        }

        foreach ($this->headers as $k => $v) {
            $this->sendHeader("$k: $v", false, $this->status);
        }

        if (is_callable($this->body)) {
            call_user_func($this->body);
        } else {
            echo $this->body;
        }
    }

    /**
     * Internally used function to actually send headers
     *
     * @param  string     the HTTP header
     * @param  bool       optional; TRUE if previously sent header should be
     *                    replaced - FALSE otherwise (default)
     * @param  integer    optional; the HTTP response code
     *
     * @return void
     */
    public function sendHeader($header, $replace = FALSE, $status = NULL) {
        if (isset($status)) {
            header($header, $replace, $status);
        }
        else {
            header($header, $replace);
        }
    }

    /**
     * Returns the reason phrase of this response according to RFC2616.
     *
     * @return string  the reason phrase for this response's status
     */
    public function reason() {
        $reason = [
            100 => 'Continue', 'Switching Protocols',
            200 => 'OK', 'Created', 'Accepted', 'Non-Authoritative Information',
                   'No Content', 'Reset Content', 'Partial Content',
            300 => 'Multiple Choices', 'Moved Permanently', 'Found', 'See Other',
                   'Not Modified', 'Use Proxy', '(Unused)', 'Temporary Redirect',
            400 => 'Bad Request', 'Unauthorized', 'Payment Required','Forbidden',
                   'Not Found', 'Method Not Allowed', 'Not Acceptable',
                   'Proxy Authentication Required', 'Request Timeout', 'Conflict',
                   'Gone', 'Length Required', 'Precondition Failed',
                   'Request Entity Too Large', 'Request-URI Too Long',
                   'Unsupported Media Type', 'Requested Range Not Satisfiable',
                   'Expectation Failed',
            500 => 'Internal Server Error', 'Not Implemented', 'Bad Gateway',
                   'Service Unavailable', 'Gateway Timeout',
                   'HTTP Version Not Supported'];

        return isset($reason[$this->status]) ? $reason[$this->status] : '';
    }

    // array access methods for headers
    public function offsetExists($offset)
    {
        return isset($this->headers[$offset]);
    }

    public function offsetGet($offset)
    {
        return @$this->headers[$offset];
    }

    public function offsetSet($offset, $value)
    {
        $this->headers[$offset] = $value;
    }

    public function offsetUnset($offset)
    {
        unset($this->headers[$offset]);
    }
}
