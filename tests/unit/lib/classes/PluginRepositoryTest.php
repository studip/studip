<?php
/*
 * plugin_repository_test.php - unit tests for the PluginRepository class
 *
 * Copyright (c) 2009  Elmar Ludwig
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */

require_once dirname(__FILE__) . '/../../bootstrap.php';
require_once 'lib/plugins/engine/PluginRepository.class.php';

class PluginRepositoryTest extends PHPUnit_Framework_TestCase
{
    public function setUp ()
    {
        $GLOBALS['SOFTWARE_VERSION'] = '1.9.0';
        $GLOBALS['CACHING_ENABLE'] = false;

        $url = 'file://'.dirname(__FILE__).'/plugin_repository_test.xml';
        $this->repository = new PluginRepository($url);
    }

    public function testGetPlugin ()
    {
        $data = $this->repository->getPlugin('Alija');

        $this->assertSame($data['version'], '0.5');
        $this->assertSame($data['url'],
            'http://plugins.studip.de/uploads/Plugins/alija-0.5.zip');

        $this->assertNull($this->repository->getPlugin('Vips'));
        $this->assertNull($this->repository->getPlugin('Unknown'));
    }

    public function testGetPlugins ()
    {
        $plugins = $this->repository->getPlugins();

        $this->assertEquals(2, count($plugins));
        $this->assertNotNull($plugins['Alija']);
        $this->assertNotNull($plugins['TracTickets']);

        $plugins = $this->repository->getPlugins('Ticket');

        $this->assertEquals(1, count($plugins));
        $this->assertNotNull($plugins['TracTickets']);
    }
}
