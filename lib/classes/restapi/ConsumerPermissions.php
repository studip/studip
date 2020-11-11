<?php
namespace RESTAPI;
use DBManager, PDO;

/**
 * REST API routing permissions
 *
 * @author     Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @license    GPL 2 or later
 * @since      Stud.IP 3.0
 * @deprecated Since Stud.IP 5.0. Will be removed in Stud.IP 5.2.
 */
class ConsumerPermissions
{
    /**
     * Create a permission object (for a certain consumer).
     * Permissions object will be cached for each consumer.
     *
     * @param mixed $consumer_id Id of consumer (optional, defaults to global)
     * @return Permissions Returns permissions object
     */
    public static function get($consumer_id = null)
    {
        static $cache = [];
        if (!isset($cache[$consumer_id])) {
            $cache[$consumer_id] = new self($consumer_id);
        }

        return $cache[$consumer_id];
    }

    private $consumer_id;
    private $permissions = [];

    /**
     * Creates the actual permission object (for a certain consumer).
     *
     * @param mixed $consumer_id Id of consumer (optional, defaults to global)
     */
    private function __construct($consumer_id = null)
    {
        $this->consumer_id = $consumer_id;

        // Init with global permissions
        $this->loadPermissions('global', true);

        // Specific consumers permissions?
        if ($consumer_id) {
            $this->loadPermissions($consumer_id, false);
        }
    }

    /**
     * Defines whether access if allowed for the current consumer to the
     * passed route via the passed method.
     *
     * @param String $route_id Route template (hash)
     * @param String $method   HTTP method
     * @param mixed  $granted  Granted state (PHP'ish boolean)
     * @param bool   $overwrite May values be overwritten
     * @return bool Indicates if value could be changed.
     */
    public function set($route_id, $method, $granted, $overwrite = false)
    {
        // If route_id is not an md5 hash, convert it
        if (!preg_match('/^[0-9a-f]{32}$/', $route_id)) {
            $route_id = md5($route_id);
        }

        if (!isset($this->permissions[$route_id])) {
            // Skip if not globally set and not allowed to overwrite
            if (!$overwrite) {
                return false;
            }
            $this->permissions[$route_id] = [];
        }

        // overwrite only if globally allowed
        if (!$overwrite && empty($this->permissions[$route_id][$method])) {
            return false;
        }

        $this->permissions[$route_id][$method] = (bool) $granted;

        return true;
    }

    /**
     * Convenience method for activating all routes in a route map.
     *
     * @param  RESTAPI\RouteMap $routemap RouteMap to activate
     */
    public function activateRouteMap(RouteMap $routemap)
    {
        foreach ($routemap->getRoutes() as $method => $routes) {
            foreach (array_keys($routes) as $route) {
                $this->set($route, $method, true, true);
            }
        }

        $this->store();
    }

    /**
     * Removes stored permissions for a given route and method.
     *
     * @param String $route_id Route template
     * @param String $method HTTP method
     * @return bool
     */
    public function remove($route_id, $method)
    {
        if (!isset($this->permissions[$route_id][$method])) {
            return false;
        }

        unset($this->permissions[$route_id][$method]);

        if (count($this->permission[$route_id]) === 0) {
            unset($this->permissions[$route_id]);
        }

        return true;
    }

    /**
     * Convenience method for deactivating all routes in a route map.
     *
     * @param RESTAPIRouteMap $routemap RouteMap to activate
     */
    public function deactivateRouteMap(RouteMap $routemap)
    {
        foreach ($routemap->getRoutes() as $method => $routes) {
            foreach (array_keys($routes) as $route) {
                $this->remove($route, $method);
            }
        }

        $this->store();
    }

    /**
     * Loads permissions for passed consumer.
     *
     * @param String $consumer_id Id of the consumer in question
     * @param bool   $overwrite May values be overwritten
     * @return Permissions Returns instance of self to allow chaining
     */
    protected function loadPermissions($consumer_id, $overwrite = false)
    {
        $query = "SELECT route_id, method, granted
                  FROM api_consumer_permissions
                  WHERE consumer_id = IFNULL(:consumer_id, 'global')";
        $statement = DBManager::get()->prepare($query);
        $statement->bindValue(':consumer_id', $consumer_id);
        $statement->execute();
        $permissions = $statement->fetchAll(PDO::FETCH_ASSOC);

        // Init with global permissions
        foreach ($permissions as $permission) {
            extract($permission);

            $this->set($route_id, $method, $granted, $overwrite);
        }

        return $this;
    }

    /**
     * Checks if access to passed route via passed method is allowed for
     * the current consumer.
     *
     * @param String $route  Route template
     * @param String $method HTTP method
     * @return bool Indicates whether access is allowed
     */
    public function check($route, $method)
    {
        $route_id = md5($route);

        return isset($this->permissions[$route_id][$method])
            && $this->permissions[$route_id][$method];
    }

    /**
     * Stores the set permissions.
     *
     * @return bool Returns true if permissions were stored successfully
     */
    public function store()
    {
        $result = true;

        $query = "INSERT INTO api_consumer_permissions (route_id, consumer_id, method, granted)
                  VALUES (:route, IFNULL(:consumer_id, 'global'), :method, :granted)
                  ON DUPLICATE KEY UPDATE granted = VALUES(granted)";
        $statement = DBManager::get()->prepare($query);
        $statement->bindParam(':route', $route_id);
        $statement->bindValue(':consumer_id', $this->consumer_id);
        $statement->bindParam(':method', $method);
        $statement->bindParam(':granted', $granted);

        foreach ($this->permissions as $route_id => $methods) {
            foreach ($methods as $method => $granted) {
                $granted = (int) !empty($granted);
                $result = $result && $statement->execute();
            }
        }

        return $result;
    }
}
