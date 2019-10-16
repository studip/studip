<?php
namespace RESTAPI;
use Request, Config, DocBlock;

/**
 * RouteMaps define and group routes to resources.
 *
 * Instances of RouteMaps are registered with the RESTAPI\Router to
 * participate in the routing business.
 *
 * A RouteMap defines at least one handler method which has to be
 * annotated with one of these annotations correlating to HTTP request
 * methods:
 *
 * @code
 * / * *
 *  * An example handler method
 *  *
 *  * @get /foo
 *  * @post /bar/:id
 *  * @put /baz/:id/:other_id
 *  * @delete /
 *  * /
 *  public function anyMethodName($id, $other_id = null) {}
 * @endcode
 *
 * By default, all API routes are unaccessible for nobody users.
 * To explicitly allow access for nobody users, add the allow_nobody
 * tag to the handler method's DocBlock. Example:
 *
 * @code
 * / * *
 *  * Another example handler method
 *  *
 *  * @get /foo
 *  *
 *  * @allow_nobody
 *  * /
 * @endcode
 *
 * As soon as the Router matches a HTTP request to a handler defined
 * in a RouteMap, it calls RouteMap::init to initialize it and
 * especially the instance field `$this->response` of type
 * RESTAPI\Response. You do not call RouteMap::init on your own.
 *
 * After the router has initialized this RouteMap, the router tries to
 * call a method `before` of this signature:
 *
 * @code
 * public function before(Router $router, Array $handler, Array $parameters);
 * @endcode
 *
 * The parameter `$handler` is a callable (as in function is_callable)
 * consisting of the instance of this RouteMap and the name of a
 * method of this instance. You may change the values of this array to
 * redirect to another handler.
 *
 * The parameter `$parameters` is an associative array whose keys
 * correlate to the placeholders in the matched URI template. The
 * values are the actual values of that placeholders in regard to the
 * HTTP request.
 *
 *
 * After calling RouteMap::before control is transfered to the actual
 * handler method. The values of the placeholders in the URI template
 * of the annotation are send as arguments to the handler.
 *
 * Example: We have got this handler method defined:
 *
 * @code
 * / * *
 *  * @get /foo/:id/bar/:other_id
 *  * /
 * public function fooHandler($id, $other_id) {
 * }
 * @endcode
 *
 * The router receives a request like this: `http://[..]/foo/1/bar/2`
 * and matches it to our `fooHandler` which is then called something
 * like that:
 *
 * @code
 * $result = $routeMap->fooHandler(1, 2);
 * @endcode
 *
 * In your handler methods you have to process the input and return
 * some output data, which is then rendered in an appropriate way
 * after negotiating the content format in the Router.
 *
 * Thus the return value of your handler method becomes the body of
 * the HTTP response.
 *
 *
 * The RouteMap class defines several methods to ease up your work
 * with the HTTP specifica.
 *
 * The methods RouteMap::status, RouteMap::headers and RouteMap::body
 * correlate to the components of a HTTP response.
 *
 * There are helpers for returning paginated collections, see
 * RouteMap::paginated.
 *
 * If you encounter an error or have to stop further processing, see
 * methods RouteMap::halt, RouteMap::error and RouteMap::notFound.
 *
 * These methods are \a DISRUPTIVE as they immediately stop the control
 * flow in your handler:
 *
 * @code
 * public function fooHandler($id)
 * {
 *   // do something
 *
 *   $this->halt();
 *
 *   // this line will never be reached
 * }
 * @endcode
 *
 * If you want to simply send a redirection response (HTTP status code
 * of 302 or 303), you may find calling RouteMap::redirect helpful.
 *
 * To generate a URL to a handler, use RouteMap::url
 *
 * When you find the need to return the content of a file, please see
 * RouteMap::sendFile which will help you with streaming it to the
 * client. For custom streaming just return a Closure from your
 * handler method.
 *
 * There are several other methods which you may find useful each
 * matching a HTTP header:
 *
 *   - RouteMap::contentType
 *   - RouteMap::etag
 *   - RouteMap::expires
 *   - RouteMap::cacheControl
 *   - RouteMap::lastModified
 *
 * You can access the data sent in the body of the current HTTP
 * request using the `$this->data` instance variable.
 *
 *   - If the request was of Content-Type `application/json`, the
 *     body of the request is decoded using `json_decode`.
 *   - If the request was of Content-Type
 *     `application/x-www-form-urlencoded`, the body of the request is
 *     decoded using `parse_str`.
 *   - Otherwise the request will not be parsed and `$this->data` will
 *     just contain the raw string.
 *
 * NOTE: The result of the described parsing will always contain
 *       strings encoded in windows-1252. If the original body
 *       was UTF-8 encoded, it is automatically re-encoded to windows-1252.
 *
 * @author  Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @author  <mlunzena@uos.de>
 * @license GPL 2 or later
 * @since   Stud.IP 3.0
 */
abstract class RouteMap
{
    /**
     * Internal property which is used by RouteMap::paginated and
     * contains everything about a paginated collection.
     */
    protected $pagination = false;

    /**
     * The offset into a RouteMap::paginated collection as requested
     * by the client.
     */
    protected $offset;

    /**
     * The limit of a RouteMap::paginated collection as requested
     * by the client.
     */
    protected $limit;

    /**
     * Constructor of the route map. Initializes neccessary offset and limit
     * parameters for pagination.
     */
    public function __construct()
    {
        $this->offset = Request::int('offset', 0);
        $this->limit  = Request::int('limit', Config::get()->ENTRIES_PER_PAGE);
    }

    /**
     * Initializes the route map by binding it to a router and passing in
     * the current route.
     *
     * @param RESTAPI\Router $router Router to bind this route map to
     * @param Array          $route  The matched route out of
     *                               Router::matchRoute; an array with keys
     *                               'handler', 'conditions' and 'source'
     */
    public function init($router, $route)
    {
        $this->router   = $router;
        $this->route    = $route;
        $this->response = new Response();

        if ($mediaType = $this->getRequestMediaType()) {
            $this->data = $this->parseRequestBody($mediaType);
        }
    }

    /**
     * Marks this chunk of data as a slice of a larger data set with
     * a sum of "total" entries.
     *
     * @param mixed $data         Chunk of data (should be sliced according
     *                            to current offset and limit parameters).
     * @param int   $total        The total number of data entries in the
     *                            according set.
     * @param array $uri_params   Neccessary parameters when generating uris
     *                            for the current route.
     * @param array $query_params Optional query parameters.
     */
    public function paginated($data, $total, $uri_params = [], $query_params = [])
    {
        $uri = $this->url($this->route['uri_template']->inject($uri_params), $query_params);

        $this->paginate($uri, $total);
        return $this->collect($data);
    }


    /**
     * Low level method for paginating collections. You better use
     * RouteMap::paginated instead of this.
     *
     * Set the pagination data used by the RouteMap::collect.
     *
     * @param String $uri_format
     * @param int    $total
     * @param mixed  $offset
     * @param mixed  $limit
     *
     * @return RESTAPI\Routemap Returns instance of self to allow chaining
     */
    public function paginate($uri_format, $total, $offset = null, $limit = null)
    {
        $total  = (int)$total;
        $offset = (int)($offset ?: $this->offset ?: 0);
        $limit  = (int)($limit ?: $this->limit);

        $this->pagination = compact('uri_format', 'total', 'offset', 'limit');

        return $this;
    }

    /**
     * Low level method for paginating collections. You better use
     * RouteMap::paginated instead of this.
     *
     * Adjusts the result set to return a collection. A collection consists
     * of the passed data array and the associated pagination information
     * if available.
     *
     * Be aware that the passed data has to be already sliced according to
     * the pagination information.
     *
     * @param Array $data Actual dataset
     * @return Array Collection "object"
     */
    public function collect($data)
    {
        $collection = [
            'collection' => $data
        ];
        if ($this->pagination) {
            extract($this->pagination);

            $offset = $offset - $offset % $limit;
            $max    = ($total % $limit)
                    ? $total - $total % $limit
                    : $total - $limit;

            $pagination = compact('total', 'offset', 'limit');
            if ($total > $limit) {
                $links = [];

                foreach ([
                             'first' => 0,
                             'previous' => max(0, $offset - $limit),
                             'next' => min($max, $offset + $limit),
                             'last' => $max]
                         as $key => $offset)
                {
                    $links[$key] = \URLHelper::getURL($uri_format, compact('offset', 'limit'));
                }

                $pagination['links'] = $links;
            }
            $collection['pagination'] = $pagination;
        }
        return $collection;
    }

    /************************/
    /* REQUEST BODY METHODS */
    /************************/

    // find the requested media type
    private function getRequestMediaType()
    {
        if ($contentType = $_SERVER['CONTENT_TYPE']) {
            $contentTypeParts = preg_split('/\s*[;,]\s*/', $contentType);
            return mb_strtolower($contentTypeParts[0]);
        }
    }

    // media-types that we know how to process
    private static $mediaTypes = [
        'application/json' => 'parseJson',
        'application/x-www-form-urlencoded' => 'parseFormEncoded',
        'multipart/form-data' => 'parseMultipartFormdata'
    ];

    // cache the request body
    private static $_request_body;

    // reads the HTTP request body
    private function parseRequestBody($mediaType)
    {
        // read it only once
        if (!isset(self::$_request_body)) {
            self::$_request_body = file_get_contents('php://input');
        }

        if (isset(self::$mediaTypes[$mediaType])) {
            $result = call_user_func([__CLASS__, self::$mediaTypes[$mediaType]], self::$_request_body);
            if ($result) {
                return $result;
            }
        }
        return self::$_request_body;
    }

    // strategy to decode JSON strings
    private static function parseJson($input)
    {
        return json_decode($input, true);
    }

    // strategy to decode form encoded strings
    private static function parseFormEncoded($input)
    {
        parse_str($input, $result);
        return $result;
    }

    // strategy to decode a multipart message. Used for file-uploads.
    private static function parseMultipartFormdata($input)
    {

        $data = [];
        if (Request::isPost()) {
            foreach ($_POST as $key => $value) {
                $data[$key] = $value;
            }
            $data['_FILES'] = $_FILES;
            return $data;
        }
        $boundary = self::getMultipartBoundary();
        if (!$boundary) {
            return $data;
        }
        $input = explode("--".$boundary, $input);

        array_pop($input);
        array_shift($input);

        foreach ($input as $part) {
            $part = ltrim($part, "\r\n");
            list($head, $body) = explode("\r\n\r\n", $part, 2);

            $tmpheaders = $headers = [];
            foreach (explode("\r\n", $head) as $headline) {
                if (preg_match('/^[^\s]/', $headline)) {
                    $lineIsHeader = preg_match('/([^:]+):\s*(.*)$/', $headline, $matches);
                    if ($lineIsHeader) {
                        $tmpheaders[] = ['index' => mb_strtolower(trim($matches[1])), 'value' => trim($matches[2])];
                    }
                } else {
                    //noch zur letzten Zeile hinzuzählen
                    end($tmpheaders);
                    $lastkey = key($tmpheaders);
                    $tmpheaders[$lastkey]['value'] .= " ".mb_substr($headline, 1);
                }
            }
            foreach ($tmpheaders as $header) {
                $headers[$header['index']] = $header['value'];
            }

            $contentType = "";
            if (isset($headers['content-type'])) {
                preg_match("/^([^;\s]*)/", $headers['content-type'], $matches);
                $contentType = mb_strtolower($matches[1]);
            }
            switch ($headers["transfer-encoding"]) {
                case "quoted-printable":
                    $body = quoted_printable_decode($body);
                    break;
                case "base64":
                    $body = base64_decode(preg_replace("/(\r?\n|\r)/", "", trim($body)));
                    break;
                case "7bit":
                case "8bit":
                default:
                    //nothing to do
            }
            $matches = [];
            preg_match("/name=([^;\s]*)/i", $headers['content-disposition'], $matches);
            $name = str_replace(["'", '"'], '', $matches[1]);
            if (!$contentType) {
                $data[$name] = mb_substr($body, 0, mb_strlen($body) - 2);
            } else {
                switch ($contentType) {
                    case 'application/json':
                        $data = array_merge($data, self::parseJson($body));
                        break;
                    case 'application/x-www-form-urlencoded':
                        $data = array_merge($data, self::parseFormEncoded($body));
                        break;
                    default:
                        $matches = [];
                        preg_match("/filename=([^;\s]*)/i", $headers['content-disposition'], $matches);
                        if (!$matches[1]) {
                            preg_match('/filename=([^;\s]*)/i', $headers['content-type'], $matches);
                        }
                        $filename = str_replace(["'", '"'], '', $matches[1]);
                        $tmp_name = $GLOBALS['TMP_PATH']."/uploadfile_".md5(uniqid());
                        $handle = fopen($tmp_name, 'wb');
                        $filesize = fwrite($handle, $body, (mb_strlen($body) - 2));
                        fclose($handle);
                        $data['_FILES'][$name] = [
                            'name' => $filename,
                            'type' => $contentType,
                            'tmp_name' => $tmp_name,
                            'size' => $filesize
                        ];
                }
            }
        }
        return $data;
    }

    private static function getMultipartBoundary()
    {
        if ($contentType = $_SERVER['CONTENT_TYPE']) {
            foreach (preg_split('/\s*[;,]\s*/', $contentType) as $part) {
                if (mb_strtolower(mb_substr($part, 0, 8)) === "boundary") {
                    $part = explode("=", $part);
                    return $part[1];
                }
            }
        }
        return null;
    }


    /**
     * Set the HTTP status of the current response.
     *
     * @param integer $status  the HTTP status of the response
     */
    public function status($status)
    {
        $this->response->status = $status;
    }

    /**
     * Set multiple response headers of the current response by
     * merging them with already set ones.
     *
     * @code
     * $routemap->headers(array('X-example' => "yep"));
     * @endcode
     *
     * @param array $headers  the headers to set
     *
     * @return array  the headers of the current response
     */
    public function headers($headers = [])
    {
        if (sizeof($headers)) {
            $this->response->headers = array_merge($this->response->headers, $headers);
        }
        return $this->response->headers;
    }

    /**
     * Set the HTTP body of the current response.
     *
     * @param string $body  the body to send back
     */
    public function body($body)
    {
        $this->response->body = $body;
    }


    /**
     * Set the Content-Type of the HTTP response given a mime type and
     * optionally further parameters as discusses in RFC 2616 14.17.
     *
     * If no charset is given, it defaults to Stud.IP's 'windows-1252'.
     *
     * Examples:
     *
     * @code
     * // results in "Content-Type: image/gif"
     * $this->contentType('image/gif);
     *
     * // results in "Content-Type: text/html;charset=ISO-8859-4"
     * $this->contentType('text/html;charset=ISO-8859-4');
     *
     * // results in "Content-Type: text/html;charset=ISO-8859-4"
     * $this->contentType('text/html', array('charset' => 'ISO-8859-4'));
     *
     * // results in "Content-type: multipart/byteranges; boundary=THIS_STRING_SEPARATES"
     * $this->contentType('multipart/byteranges', array('boundary' => 'THIS_STRING_SEPARATES'));
     *
     * @endcode
     *
     * @param string $mime_type  a string describing a MIME type like 'application/json'
     * @param array  $params     optional parameters as described above
     */
    public function contentType($mime_type, $params = [])
    {
        if (!isset($params['charset'])) {
            $params['charset'] = 'utf-8';
        }

        if (mb_strpos($mime_type, 'charset') !== FALSE) {
            unset($params['charset']);
        }

        if (sizeof($params)) {
            $mime_type .= mb_strpos($mime_type, ';') !== FALSE ? ', ' : ';';
            $ps = [];
            foreach ($params as $k => $v) {
                $ps[] = $k . '=' . $v;
            }
            $mime_type .= join(', ', $ps);
        }

        $this->response['Content-Type'] = $mime_type;
    }

    /**
     * (Nice) sugar for calling RouteMap::halt and therefore
     * as \a DISRUPTIVE. Code after calling RouteMap::error will not
     * be evaluated.
     *
     * @see RouteMap::halt
     *
     * @param integer $status  a number indicating the HTTP status
     *                         code; probably something 4xx or 5xx-ish
     * @param string $body     optional; the body of the HTTP response
     *
     */
    public function error($status, $body = null)
    {
        $this->halt($status, [], $body);
    }


    /**
     * Sets the HTTP response's Etag header and halts, if the incoming
     * HTTP request was a matching conditional GET using an
     * 'If-None-Match' header. Thus it is a possibly \a DISRUPTIVE
     * method as it will stop evaluation in that case and send a '304
     * Not Modified'.
     *
     * Detail: If the request contains an If-Match or If-None-Match
     * header set to `*`, a RouteMap assumes a match on safe
     * (e.g. GET) and idempotent (e.g. PUT) requests. (In those cases
     * it thinks that the resource already exists and therefore
     * matches a wildcard.). This can be changed by passing an
     * appropriate value for the `$new_resource` parameter.

     * Details of this can be found in RFC 2616 14.24 and 14.26
     *
     * @param string $value       an identifier uniquely identifying the
     *                            current state of a resource
     * @param bool $strong_etag   optional; indicates whether the etag
     *                            is a weak or strong (which is the
     *                            default) cache validator. Have a look
     *                            at the RFC for details.
     * @param bool $new_resource  optional; a way to tell the RouteMap
     *                            that this is a new or existing
     *                            resource. See above.
     */

    public function etag($value, $strong_etag = true, $new_resource = null)
    {
        // Before touching this code, please double check RFC 2616
        // 14.24 and 14.26.

        if (!isset($new_resource)) {
            $new_resource = Request::isPost();
        }

        $value = '"' . $value . '"';
        if (!$strong_etag) {
            $value = 'W/' . $value;
        }
        $this->response['ETag'] = $value;

        if ($this->response->isSuccess() || $this->response->status === 304) {
            if ($this->etagMatches($_SERVER['HTTP_IF_NONE_MATCH'], $new_resource)) {
                $this->halt($this->isRequestSafe() ? 304 : 412);
            }
            if (isset($_SERVER['HTTP_IF_MATCH'])
                && !$this->etagMatches($_SERVER['HTTP_IF_MATCH'], $new_resource)) {
                $this->halt(412);
            }
        }
    }

    // Helper method checking if a ETag value list includes the current ETag.
    private function etagMatches($list, $new_resource)
    {
        if ($list === '*') {
            return !$new_resource;
        }

        return in_array($this->response['ETag'],
                        preg_split('/\s*,\s*/', $list));
    }

    // Helper method checking if the request is safe
    private function isRequestSafe()
    {
        $method = Request::method();
        return $method === 'GET' or $method === 'HEAD' or $method === 'OPTIONS' or $method === 'TRACE';
    }

    /**
     * This sets the `Expires` header and the `Cache-Control`
     * directive `max-age`.
     *
     * Amount is an integer number of seconds in the future indicating
     * when the response should be considered "stale". The
     * `$cache_control` parameter is passed to RouteMap#cacheControl
     * along with the automatically generated `max_age` directive.
     *
     * @param int $amount  an integer specifying the number of seconds
     *                     this resource will go stale.
     * @param array $cache_control  optional; more directives for
     *                     RouteMap::cacheControl which is always
     *                     automatically called using the computed max_age
     */
    public function expires($amount, $cache_control = [])
    {
        $time = time() + $amount;
        $max_age = $amount;

        $cache_control[] = "max-age=$max_age";
        $this->cacheControl($cache_control);

        $this->response['Expires'] = $this->httpDate($time);
    }

    /**
     * This sets the Cache-Control header of the HTTP response.
     *
     * Example:
     *
     * @code
     * $this->cacheControl(array('public', 'must-revalidate'));
     * @endcode
     *
     * @param array $values  an array containing Cache-Control
     *                       directives.
     */
    public function cacheControl($values)
    {
        if (is_array($values) && sizeof($values)) {
            $this->response['Cache-Control'] = join(', ', $values);
        }
    }

    /**
     * This very important method stops further execution of your
     * code. You may specify a status code, headers and the body of
     * the resulting response. As the name implies, this method is \a
     * DISRUPTIVE and will not return.
     *
     * @code
     * // stops any further code of a route
     * $this->halt();
     *
     * // you may specify an HTTP status
     * $this->halt(409):
     *
     * // you may specify the HTTP response's body
     * $this->halt('my ethereal body')
     *
     * // or even both
     * $this->halt(100, 'Yes, pleazze!')
     *
     * // giving headers
     * $this->halt(417, array('Content-Type' => 'x-not-a-cat'), 'Cats only!')
     * @endcode
     *
     * This method is called by every single \a DISRUPTIVE method.
     *
     * @param integer $status  optional; the response's status code
     * @param array $headers   optional; (additional) header lines
     *                           which get merged with already set headers
     * @param string $body     optional; the response's body
     */
    public function halt(/* [status], [headers], [body] */)
    {
        $args   = func_get_args();
        $result = [];

        $constraints = [
            'status'  => 'is_int',
            'headers' => 'is_array',
            'body'    => function ($i) { return isset($i); } // #existy
        ];
        foreach ($constraints as $state => $constraint) {
            if ($constraint(current($args))) {
                call_user_func([$this, $state], array_shift($args));
            }
        }

        throw new RouterHalt($this->response);
    }

    /**
     * This method sets the Last-Modified header of the HTTP response
     * and halts on matching conditional GET requests. Thus this
     * method is \a DISRUPTIVE in certain circumstances.
     *
     * You have to give an integer typed timestamp (in seconds since
     * epoch) to specify the data of the last modification to the
     * requested resource.
     *
     * If the current HTTP request contains an `If-Modified-Since`
     * header, its value is compared to the specified `$time`
     * parameter. Unless the header's value is sooner than the given
     * `$time`, further execution is precluded and the RouteMap
     * returns with a '304 Not Modified'.
     *
     * @param integer $time  a timestamp described in seconds since epoch
     */
    public function lastModified($time)
    {

        $this->response['Last-Modified'] = $this->httpDate($time);

        if (isset($_SERVER['HTTP_IF_NONE_MATCH'])) {
            return;
        }

        if ($this->response->status === 200
            && isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
            // compare based on seconds since epoch
            $since = $this->httpdate($_SERVER['HTTP_IF_MODIFIED_SINCE']);
            if ($since >= (int) $time) {
                $this->halt(304);
            }
        }

        if (($this->response->isSuccess() || $this->response->status === 412)
            && isset($_SERVER['HTTP_IF_UNMODIFIED_SINCE'])) {

            // compare based on seconds since epoch
            $since = $this->httpdate($_SERVER['HTTP_IF_UNMODIFIED_SINCE']);

            if ($since < (int) $time) {
                $this->halt(412);
            }
        }
    }

    private function httpDate($timestamp)
    {
        return gmdate('D, d M Y H:i:s \G\M\T', (int) $timestamp);
    }

    /**
     * Halts execution and returns a '404 Not Found' response.
     *
     * Sugar for calling RouteMap::error(404) and therefore
     * \a DISRUPTIVE. Code after calling RouteMap::notFound will
     * not be evaluated.
     *
     * @see RouteMap::error
     * @see RouteMap::halt
     *
     * @param string $body     optional; the body of the HTTP response
     */
    public function notFound($body = null)
    {
        $this->halt(404, $body);
    }

    /**
     * Stops your code and redirects to the URL provided. This method
     * is \a DISRUPTIVE like RouteMap#halt
     *
     * In addition to the URL you may provide the status code,
     * (additional) headers and a request body as you would when
     * calling RouteMap#halt.
     *
     * @code
     * $this->redirect('/foo', 201, array('X-Some-Header' => 1234), 'and even a body');
     * @endcode
     *
     * @see RouteMap::halt
     *
     * @param string $url the URL to redirect to; it will be filtered
     *                    using RouteMap#url, so you may call it with
     *                    those nice and small strings used in the
     *                    annotations
     * @param mixed $args optional; any combinations of the three
     *                    parameters as in RouteMap::halt
     */
    public function redirect($url, $args = null)
    {
        $this->status($_SERVER["SERVER_PROTOCOL"] === 'HTTP/1.1' && !Request::isGet() ? 303 : 302);
        $this->response['Location'] = $this->url($url);

        $args = array_slice(func_get_args(), 1);
        call_user_func_array([$this, 'halt'], $args);
    }


    /**
     * Stops execution of your code and starts sending the specified
     * file. This method is \a DISRUPTIVE.
     *
     * Using the `$opts` parameter you may specify the file's mime
     * content type, sending an appropriate 'Content-Type' header, and
     * you may specify the 'Content-Disposition' of the file transfer.
     *
     * Example:
     *
     * @code
     * $this->sendFile('/tmp/c29tZSB0ZXh0', array(
     *     'type' => 'image/png',
     *     'disposition' => 'inline',
     *     'filename' => 'cutecats.png'));
     * @endcode
     *
     * @param string $_path  the filesystem path to the file to send
     * @param array  $opts   optional; specify the content type,
     *                       disposition and filename
     */
    public function sendFile($_path, $opts = [])
    {
        $path = realpath($_path);

        if (!file_exists($path)) {
            $this->notFound('File to send does not exist');
        }

        if (isset($opts['type'])) {
            $this->contentType($opts['type']);
        } else if (!isset($this->response['Content-Type'])) {
            $this->contentType(get_mime_type($path));
        }

        if ($opts['disposition'] === 'attachment' || isset($opts['filename'])) {
            $this->response['Content-Disposition'] = 'attachment; ';
            $filename = $opts['filename'] ?: $path;
            $this->response['Content-Disposition'] .= encode_header_parameter('filename', basename($filename));
        }

        elseif ($opts['disposition'] === 'inline') {
            $this->response['Content-Disposition'] = 'inline';
        }

        // TODO add HTTP 'Range' support

        $size = filesize($path);
        $this->response['Content-Length'] = $size;

        $this->halt(200, $this->response->headers, function () use ($path) { readfile($path); });
    }


    /**
     * Generate a URL to a given handler using a URL fragment and URL
     * parameters.
     *
     * Example:
     * @code
     * // result in something like "/some/path/api.php/course/123/members?status=student"
     * $this->url('course/123/members', array('status' => 'student'));
     * @endcode
     *
     * @param string $addr       a URL fragment to a handler
     * @param array $url_params  optional; URL parameters to add to
     *                           the generated URL
     *
     * @return string  the resulting URL
     */
    public function url($addr, $url_params = null)
    {
        $addr = ltrim($addr, '/');
        return \URLHelper::getURL("api.php/$addr", $url_params, true);
    }

    /**
     * A `vsprintf` like variant to the RouteMap::url method.
     *
     * Example:
     * @code
     * // results in "[...]/api.php/foo/some_id?status=student"
     * $this->urlf("foo/%s", array("some_id"), array('status' => 'student'));
     * @endcode
     *
     * @param string $addr_f        a URL fragment to a handler
     *                              containing sprintf-ish format sequences
     * @param array $format_params  values to fill into the format markers
     * @param array $url_params     optional; URL parameters to add to
     *                              the generated URL
     *
     * @return string  the resulting URL
     */

    public function urlf($addr_f, $format_params, $url_params = null)
    {
        return $this->url(vsprintf($addr_f, $format_params), $url_params);
    }

    /**
     * Returns a list of all the routes this routemap provides.
     *
     * @param string $http_method Return only the routes for this specific
     *                            http method (optional)
     *
     * @return array of all routes grouped by method
     */
    public function getRoutes($http_method = null)
    {
        $ref      = new \ReflectionClass($this);
        $docblock = DocBlock::ofClass($this);
        $class_conditions = $this->extractConditions($docblock);

        // Create result array by creating an associative array from all
        // supported methods as keys
        $routes = array_fill_keys(Router::getSupportedMethods(), []);

        // Restrict routes to given http method (if given)
        if ($http_method !== null) {
            $routes = [$http_method => []];
        }

        // Iterate through all methods of the routemap
        foreach ($ref->getMethods() as $ref_method) {
            // Not public? Not an api route!
            if (!$ref_method->isPublic()) {
                continue;
            }

            // Parse docblock
            $docblock = DocBlock::ofMethod($ref_method->class, $ref_method->name);

            // No docblock tags? Not an api route!
            if (!$docblock->tags) {
                continue;
            }

            // Any specific condition to consider?
            $conditions = $this->extractConditions($docblock, $class_conditions);

            // Iterate through all possible methods in order to identify
            // any according docblock tags
            $allow_nobody = isset($docblock->tags['allow_nobody']);
            foreach (array_keys($routes) as $http_method) {
                if (!isset($docblock->tags[$http_method])) {
                    //The tag for the current HTTP method cannot be found
                    //in the route's DocBlock tags.
                    continue;
                }

                // Route all defined method and uri template combinations to
                // the according methods of the object.
                foreach ($docblock->tags[$http_method] as $uri_template) {
                    $routes[$http_method][$uri_template] = [
                        'handler'      => [$this, $ref_method->name],
                        'conditions'   => $conditions,
                        'description'  => $docblock->desc ?: false,
                        'allow_nobody' => $allow_nobody
                    ];
                }
            }
        }

        // Return all routes grouped or just the routes for the wanted method
        return func_num_args() === 1
             ? reset($routes)
             : $routes;
    }

    /**
     * Extracts defined conditions from a given docblock.
     *
     * @param DocBlock $docblock   DocBlock to examine
     * @param Array    $conditions Optional array of already defined
     *                             conditions to extend
     * @return Array of all extracted conditions with the variable name
     *         as key and pattern to match as value
     */
    protected function extractConditions($docblock, $conditions = [])
    {
        if (!empty($docblock->tags['condition'])) {
            foreach ($docblock->tags['condition'] as $condition) {
                list($var, $pattern) = explode(' ', $condition, 2);
                $conditions[$var] = $pattern;
            }
        }

        return $conditions;
    }
}
