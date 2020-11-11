<?php
namespace RESTAPI\Routes;

/**
 * @author     Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @author     <mlunzena@uos.de>
 * @license    GPL 2 or later
 * @deprecated Since Stud.IP 5.0. Will be removed in Stud.IP 5.2.
 */
class Discovery extends \RESTAPI\RouteMap
{
    /**
     * Schnittstellenbeschreibung
     *
     * @get /discovery
     */
    public function getDiscovery()
    {
        $routes = $this->router->getRoutes(true);
        foreach ($routes as $uri_template => $methods) {
            foreach ($methods as $method => $route) {
                $routes[$uri_template][$method] = $route['description'];
            }
        }
        return $routes;
    }
}
