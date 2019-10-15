<?php
/** @namespace RESTAPI
 *
 * Im Namensraum RESTAPI sind alle Klassen und Funktionen versammelt,
 * die für die RESTful Web Services von Stud.IP benötigt werden.
 */
namespace RESTAPI;
use BadMethodCallException;

/**
 * Die Aufgabe des Routers ist das Anlegen und Auswerten eines
 * Mappings von sogenannten Routen (Tupel aus HTTP-Methode und Pfad)
 * auf Code.
 *
 * Dazu werden zunächst Routen mittels der Funktion
 * Router::registerRoutes registriert.
 *
 * Wenn dann ein HTTP-Request eingeht, kann mithilfe von
 * Router::dispatch und HTTP-Methode bzw. Pfad der zugehörige Code
 * gefunden und ausgeführt werden. Der Router bildet aus dem
 * Rückgabewert des Codes ein Response-Objekt, das er als Ergebnis
 * zurück meldet.
 *
 * @code
 * $router = Router::getInstance();
 *
 * // register a sample Route
 * $router->registerRoutes(new ExampleRoute);
 *
 * // dispatch to therein defined Routes
 * $response = $router->dispatch('/example', 'GET');
 *
 * // render response
 * $response->output();
 *
 * @endcode
 *
 * @author  Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @author  <mlunzena@uos.de>
 * @license GPL 2 or later
 * @see     Inspired by http://blog.sosedoff.com/2009/07/04/simpe-php-url-routing-controller/
 * @since   Stud.IP 3.0
 */
class Router
{
    // instances are cached here
    protected static $instances = [];

    /**
     * Holds the user object of the user that is accessing the API.
     * This is null for nobody users.
     */
    protected $user = null;

    /**
     * Returns (and if neccessary, initializes) a (cached) router object for an
     * optional consumer id.
     *
     * @param mixed $consumer_id ID of the consumer (defaults to 'global')
     *
     * @return Router returns the Router instance associated to the
     *                consumer ID (or to the 'global' ID)
     */
    public static function getInstance($consumer_id = null)
    {
        $consumer_id = $consumer_id ?: 'global';

        if (!isset(self::$instances[$consumer_id])) {
            self::$instances[$consumer_id] = new self($consumer_id);
        }
        return self::$instances[$consumer_id];
    }

    // All supported method need to be defined here
    protected static $supported_methods = [
        'get', 'post', 'put', 'delete', 'patch', 'options', 'head'
    ];

    /**
     * Returns a list of all supported methods.
     *
     * @return array of methods as strings
     */
    public static function getSupportedMethods()
    {
        return self::$supported_methods;
    }

    // registered routes by method and uri template
    protected $routes = [];

    // registered content renderers
    protected $renderers = [];

    // identified or forced content renderer
    protected $content_renderer = false;

    // default renderer
    protected $default_renderer = false;

    // registered conditions
    protected $conditions = [];

    // registered descriptions
    protected $descriptions = [];

    // registered consumers
    protected $consumers = [];

    // associated permissions
    protected $permissions = false;

    /**
     * Constructs the router.
     *
     * @param mixed $consumer_id  the ID of the consumer this router
     *                             should associate to
     */
    protected function __construct($consumer_id)
    {
        $this->permissions = ConsumerPermissions::get($consumer_id);
        $this->registerRenderer(new Renderer\DefaultRenderer);
    }

    /**
     * Registers a handler for a specific combination of request method
     * and uri template.
     *
     * @param String  $request_method   expected HTTP request method
     * @param String  $uri_template     expected URI template, for
     *                                  example: \code "/user/:user_id/events" \endcode
     * @param Array   $handler          request handler array:
     *                                  \code array($object, "methodName") \endcode
     * @param Array   $conditions       (optional) an associative
     *                                  array using the name of
     *                                  parameters as keys and regexps
     *                                  as value
     * @param string  $source           (optional) this denotes the
     *                                  origin of a route. Usually
     *                                  either 'core' or 'plugin', but
     *                                  defaults to 'unknown'.
     *
     * @param bool $allow_nobody Whether the route can be accessed
     *     as nobody user (true) or not (false). Defaults to false.
     *
     * @return Router  returns itself to allow chaining
     * @throws Exception  if passed HTTP request method is not supported
     */
    public function register($request_method, $uri_template, $handler, $conditions = [], $source = 'unknown', $allow_nobody = false)
    {
        // Normalize method and test whether it's supported
        $request_method = mb_strtolower($request_method);
        if (!in_array($request_method, self::$supported_methods)) {
            throw new \Exception('Method "' . $request_method . '" is not supported.');
        }

        // Initialize routes storage for this method if neccessary
        if (!isset($this->routes[$request_method])) {
            $this->routes[$request_method] = [];
        }

        // Normalize uri template (always starts with a slash)
        if ($uri_template[0] !== '/') {
            $uri_template = '/' . $uri_template;
        }

        // Sanitize conditions
        foreach ($conditions as $var => $pattern) {
            if ($pattern[0] !== $pattern[mb_strlen($pattern) - 1] || ctype_alnum($pattern[0])) {
                $conditions[$var] = '/' . $pattern . '/';
            }
        }

        $this->routes[$request_method][$uri_template] =
            compact('handler', 'conditions', 'source', 'allow_nobody');

        // Return instance to allow chaining
        return $this;
    }

    /**
     * Registers the routes defined in a RouteMap instance using
     * docblock annotations (like @get) of its methods.
     *
     * \code
     * $router = \RESTAPI\Router::getInstance();
     *
     * $router->registerRoutes(new ExampleRouteMap());
     * \endcode
     *
     * @param RouteMap $map  the RouteMap instance to register
     *
     * @return Router returns itself to allow chaining
     */
    public function registerRoutes(RouteMap $map)
    {
        // Investigate object, define whether it's located in the core system
        // or a plugin, respect any defined class conditions and iterate
        // through it's methods to find any defined route
        $ref      = new \ReflectionClass($map);
        $filename = $ref->getFilename();
        $source   = mb_strpos($filename, 'plugins_packages') !== false
                  ? 'plugin'
                  : 'core';

        foreach (self::$supported_methods as $http_method) {
            foreach ($map->getRoutes($http_method) as $uri_template => $data) {
                // Register (and describe) route
                $this->register(
                    $http_method, $uri_template,
                    $data['handler'], $data['conditions'],
                    $source,
                    $data['allow_nobody']
                );
                if ($data['description']) {
                    $this->describe(
                        $uri_template,
                        $data['description'],
                        $http_method
                    );
                }
            }
        }

        return $this;
    }

    /**
     * Describe one or more routes.
     *
     * \code
     * $router = \RESTAPI\Router::getInstance();
     *
     * // describe a single route
     * $router->describe('/foo', 'returns everything about foo', 'get');
     *
     * // describe several routes that use the same path
     * $router->describe('/foo', array(
     *     'get'    => 'returns everything about foo',
     *     'put'    => 'updates all of foo',
     *     'delete' => 'empty up foo'
     * ));
     *
     * // describe several routes
     * $router->describe(array(
     *     '/foo' => array(
     *                   'get'    => 'returns everything about foo',
     *                   'put'    => 'updates all of foo',
     *                   'delete' => 'empty up foo'),
     *     '/bar' => array(...),
     * ));
     * \endcode
     *
     * @param String|Array $uri_template  URI template to describe or pass an
     *                                    array to describe multiple routes.
     * @param String|null  $description   description of the route
     * @param String       $method        method to describe.
     *
     * @return Router  returns instance of itself to allow chaining
     */
    public function describe($uri_template, $description = null, $method = 'get')
    {
        // describe multiple routes at once
        if (func_num_args() === 1 && is_array($uri_template)) {
            foreach ($uri_template as $template => $description) {
                $this->describe($template, $description);
            }
        }

        // describe routes that use the same URI template
        elseif (func_num_args() === 2 && is_array($description)) {
            foreach ($description as $method => $desc) {
                $this->describe($uri_template, $desc, $method);
            }
        }

        // describe a single route
        else {
            if (!isset($this->descriptions[$uri_template])) {
                $this->descriptions[$uri_template] = [];
            }
            if (isset($this->routes[$method][$uri_template])) {
                $this->descriptions[$uri_template][$method] = $description;
            } else {
                // Try to find route with different method
                foreach ($this->routes as $m => $templates) {
                    if (isset($templates[$uri_template])) {
                        $this->descriptions[$uri_template][$m] = $description;
                        break;
                    }
                }
            }
        }
        return $this;
    }

    /**
     * Get list of registered routes - optionally with their descriptions.
     *
     * @param bool $describe      (optional) include descriptions,
     *                              defaults to `false`
     * @param bool $check_access  (optional) only show methods this router's
     *                              consumer is authorized to,
     *                              defaults to `true`
     *
     * @return Array list of registered routes
     */
    public function getRoutes($describe = false, $check_access = true)
    {
        $this->setupRoutes();

        $result = [];
        foreach ($this->routes as $method => $routes) {
            foreach ($routes as $uri => $route) {
                if ($check_access && !$this->permissions->check($uri, $method)) {
                    continue;
                }
                if (!isset($result[$uri])) {
                    $result[$uri] = [];
                }
                if ($describe) {
                    $result[$uri][$method] = [
                        'description' => $this->descriptions[$uri][$method] ?: null,
                        'source'      => $route['source'] ?: 'unknown',
                    ];
                } else {
                    $result[$uri][] = $method;
                }
            }
        }
        ksort($result);
        if ($describe) {
            $result = array_map(function ($item) {
                ksort($item);
                return $item;
            }, $result);
        }
        return $result;
    }

    /**
     * Dispatches an URI across the defined routes and produces a
     * Response object which may then be send back (using #output).
     *
     * @param mixed  $uri     URI to dispatch (defaults to `$_SERVER['PATH_INFO']`)
     * @param String $method  Request method (defaults to the method
     *                        of the actual HTTP request or "GET")
     *
     * @return Response  a Response object containing status, headers
     *                   and body
     * @throws RouterException  may throw such an exception if there
     *                          is no matching route (404) or if there
     *                          is one, but the consumer is not
     *                          authorized to it (403)
     */
    public function dispatch($uri = null, $method = null)
    {
        $this->setupRoutes();

        $uri = $this->normalizeDispatchURI($uri);
        $method = $this->normalizeRequestMethod($method);

        $content_renderer = $this->negotiateContent($uri);

        list($route, $parameters, $allow_nobody) = $this->matchRoute($uri, $method, $content_renderer);
        if (!$route) {
            //No route found for the combination of URI and method.
            //We return the allowed methods for the route in the HTTP header:
            $methods = $this->getMethodsForUri($uri);
            if (count($methods) > 0) {
                header('Allow: ' . implode(', ', $methods));
                throw new RouterException(405);
            } else {
                //Route not found.
                throw new RouterException(404);
            }
        }
        //At this point, a route is found.
        //We need to check if it can be used as nobody user or not.
        if (!$route['allow_nobody'] && !$this->user) {
            //Nobody users aren't allowed for this route.
            throw new RouterException(401, 'Unauthorized (no consumer)');
        }

        try {
            $response = $this->execute($route, $parameters);
        } catch (RouterHalt $halt) {
            $response = $halt->response;
        }

        $response->finish($content_renderer);

        return $response;
    }

    /**
     * Searches and registers available routes.
     */
    private function setupRoutes()
    {
        // A bit ugly, I confess
        static $was_setup = false;
        if ($was_setup) {
            return;
        }
        $was_setup = true;

        // Register default routes
        $routes = words('Activity Blubber Contacts Course Discovery Events FileSystem Forum Messages News Schedule Semester Studip User UserConfig Wiki');

        foreach ($routes as $route) {
            require_once "app/routes/$route.php";
            $class = "\\RESTAPI\\Routes\\$route";
            $this->registerRoutes(new $class);
        }

        // Register plugin routes
        $router = $this;
        array_walk(
            array_flatten(\PluginEngine::sendMessage('RESTAPIPlugin', 'getRouteMaps')),
            function ($route) use ($router) {
                $router->registerRoutes($route);
            }
        );
    }

    /**
     * Takes a route and the parameters out of the requested path and
     * executes the handler of the route.
     *
     * @param Array $route      the matched route out of
     *                          Router::matchRoute; an array with keys
     *                          'handler', 'conditions' and 'source'
     * @param Array $parameters the matched parameters out of
     *                          Router::matchRoute; something like:
     *                          `array('user_id' => '23a21d...e78f')`
     * @return Response  the resulting Response object which is then
     *                   polished in Router::dispatch
     */
    protected function execute($route, $parameters)
    {
        $handler = $route['handler'];

        if (!is_object($handler[0])) {
            throw new RuntimeException("Handler is not a method.");
        }

        $handler[0]->init($this, $route);

        if (method_exists($handler[0], 'before')) {
            $handler[0]->before($this, $handler, $parameters);
        }

        $result = call_user_func_array($handler, $parameters);

        if (method_exists($result, 'toArray')) {
            $result = $result->toArray();
        }

        // $result is stronger than $response->body
        if (isset($result)) {
            $handler[0]->body($result);
        }

        if (method_exists($handler[0], 'after')) {
            $handler[0]->after($this, $parameters);
        }

        return $handler[0]->response;
    }

    /**
     * Registers a content renderer.
     *
     * @param ContentRenderer $renderer    instance of a content renderer
     * @param boolean         $is_default  (optional) set this
     *                                     renderer as default?;
     *                                     defaults to `false`
     *
     * @return Router returns itself to allow chaining
     */
    public function registerRenderer($renderer, $is_default = false)
    {
        $this->renderers[$renderer->extension()] = $renderer;
        if ($is_default) {
            $this->default_renderer = $renderer;
        }

        return $this;
    }

    private function normalizeDispatchURI($uri)
    {
        return $uri === null ? $_SERVER['PATH_INFO'] : $uri;
    }

    private function normalizeRequestMethod($method)
    {
        return mb_strtolower($method ?: \Request::method() ?: 'get');
    }

    /**
     * Negotiate content using the registered content renderers. The
     * first ContentRenderer that returns `true` when calling
     * ContentRenderer::shouldRespondTo gets the job.
     *
     * @param String $uri  the URI to which the content renderers may respond
     *
     * @return ContentRenderer  either a ContentRenderer that responds
     *                          to the URI or the default
     *                          ContentRenderer of this router.
     */
    protected function negotiateContent($uri)
    {
        $content_renderer = null;
        foreach ($this->renderers as $renderer) {
            if ($renderer->shouldRespondTo($uri)) {
                $content_renderer = $renderer;
                break;
            }
        }
        if (!$content_renderer) {
            $content_renderer = $this->default_renderer ?: reset($this->renderers);
        }
        return $content_renderer;
    }

    /**
     * Tries to match a route given a URI and a HTTP request method.
     *
     * @param String $uri     the URI to match
     * @param String $method  the HTTP request method to match
     * @param ContentRenderer $content_renderer the used
     *                                          ContentRenderer which
     *                                          is needed to remove
     *                                          a file extension
     *
     * @return Array  an array containing the matched route and the
     *                found parameters
     */
    protected function matchRoute($uri, $method, $content_renderer)
    {
        $matched    = null;
        $parameters = [];
        if (isset($this->routes[$method])) {
            if ($content_renderer->extension() && mb_strpos($uri, $content_renderer->extension()) !== false) {
                $uri = mb_substr($uri, 0, -mb_strlen($content_renderer->extension()));
            }

            foreach ($this->routes[$method] as $uri_template => $route) {
                if (!isset($route['uri_template'])) {
                    $route['uri_template'] = new UriTemplate($uri_template, $route['conditions']);
                }

                if ($route['uri_template']->match($uri, $prmtrs)) {
                    if (!$this->permissions->check($uri_template, $method)) {
                        throw new RouterException(403, "Route not activated");
                    }
                    $matched = $route;
                    $parameters = $prmtrs;
                    break;
                }
            }
        }
        return [$matched, $parameters];
    }

    /**
     * Returns all methods the given uri responds to.
     *
     * @param String $uri the URI to match
     *
     * @return array of all of responding methods
     */
    protected function getMethodsForUri($uri)
    {
        $methods = [];

        foreach ($this->routes as $method => $templates) {
            foreach ($templates as $uri_template => $route) {
                if (!isset($route['uri_template'])) {
                    $route['uri_template'] = new UriTemplate($uri_template, $route['conditions']);
                }

                if ($route['uri_template']->match($uri)
                    && $this->permissions->check($uri_template, $method))
                {
                    $methods[] = $method;
                }
            }
        }

        return array_map('strtoupper', $methods);
    }


    /**
     * Sets up the authentication for the router.
     */
    public function setupAuth()
    {
        // Detect consumer
        $consumer = Consumer\Base::detectConsumer();
        if (!$consumer) {
            return null;
        }

        $this->user = $consumer->getUser();

        // Set authentication if present
        if ($this->user) {
            // Skip fake authentication if user is already logged in
            if ($GLOBALS['user']->id !== $this->user->id) {

                $GLOBALS['auth'] = new \Seminar_Auth();
                $GLOBALS['auth']->auth = [
                    'uid'   => $this->user->user_id,
                    'uname' => $this->user->username,
                    'perm'  => $this->user->perms,
                ];

                $GLOBALS['user'] = new \Seminar_User($this->user->user_id);

                $GLOBALS['perm'] = new \Seminar_Perm();
                $GLOBALS['MAIL_VALIDATE_BOX'] = false;
            }
            setTempLanguage($GLOBALS['user']->id);
        }

        return $this->user;
    }
}
