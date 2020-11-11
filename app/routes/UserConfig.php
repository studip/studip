<?php
namespace RESTAPI\Routes;

use RESTAPI\RouteMap;
use RESTAPI\Router;

/**
 * API routes for accessing user config values.
 *
 * @author     Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @license    GPL2 or any later version
 * @since      Stud.IP 3.4
 * @deprecated Since Stud.IP 5.0. Will be removed in Stud.IP 5.2.
 *
 * @condition user_id ^[0-9a-f]{1,32}$
 *
 * @status 404 if user does not exist
 * @status 403 if user may access the request config item
 */
class UserConfig extends RouteMap
{
    // Stores the user's config instance
    private $config;

    /**
     * Performs checks if the user exists and may actually access the
     * requested config.
     *
     * @param Router $router     Instance of the api router
     * @param array  $handler    Detected handler router
     * @param array  $parameters Parameters of the called route
     */
    public function before(Router $router, array $handler, array $parameters)
    {
        // Check whether user exist
        if (\User::find($parameters['user_id']) === null) {
            $this->error(404, sprintf('User %s not found', $parameters['user_id']));
        }

        // Check whether user accesses own config or user is root
        if ($parameters['user_id'] !== $GLOBALS['user']->id && $GLOBALS['user']->perms !== 'root') {
            $this->error(403, 'User may only access own config');
        }

        $this->config = \UserConfig::get($parameters['user_id']);
    }

    /**
     * Returns the value of a specific config entry for a given user
     *
     * @get /user/:user_id/config/:field
     *
     * @return mixed Value for the request config item
     * @status 404 if config item does not exist
     */
    public function getConfig($user_id, $field)
    {
        // Check whether key exists in config
        if (!isset($this->config[$field])) {
            $this->error(404, sprintf('No config item for field %s and user %s',
                                      $field, $user_id));
        }

        return $this->config[$field];
    }

    /**
     * Stored the value of a specific config entry for a given user
     *
     * @put /user/:user_id/config/:field
     *
     * @status 204 on success
     * @status 400 if no value is given
     */
    public function setConfig($user_id, $field)
    {
        if (!isset($this->data['value'])) {
            $this->error(400, 'No value given in request');
        }

        $this->config->store($field, $this->data['value']);

        $this->status(204);
    }

    /**
     * Removes a specific config entry for a given user
     *
     * @delete /user/:user_id/config/:field
     *
     * @status 204 on success
     */
    public function deleteConfig($user_id, $field)
    {
        $this->config->delete($field);

        $this->status(204);
    }
}
