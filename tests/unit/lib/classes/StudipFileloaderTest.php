<?php

/**
 * Testcase for StudipFileloader class.
 *
 *
 * @author    mlunzena
 * @copyright (c) Authors
 */

class StudipFileloaderTestCase extends \Codeception\Test\Unit {

    public function setUp() {
        ArrayFileStream::set_filesystem(
            [
                'pathto' => [
                    'config-1.php' => '<? $CONF = 17; '
                    , 'config-2.php' => '<? $CONF = 17 + $offset; '
                ]]);

        if (!stream_wrapper_register("var", "ArrayFileStream")) {
            new Exception("Failed to register protocol");
        }
    }

    public function tearDown() {
        stream_wrapper_unregister("var");
    }


    public function test_should_inject_vars() {
        $container = [];
        StudipFileloader::load('var://pathto/config-1.php', $container);
        $this->assertEquals(['CONF' => 17], $container);
    }

    public function test_should_inject_vars_twice() {

        foreach (range(1,2) as $i) {
            $container = [];
            StudipFileloader::load('var://pathto/config-1.php', $container);
        }
        $this->assertEquals(['CONF' => 17], $container);
    }

    public function test_should_use_optional_bindings()
    {
        $container = [];
        $offset = 25;
        StudipFileloader::load('var://pathto/config-2.php', $container, compact('offset'));
        $this->assertEquals(['CONF' => 42], $container);
    }

    public function test_should_balk_upon_file_not_found()
    {
        $this->expectException(\PHPUnit\Framework\Exception::class);
        StudipFileloader::load('var://pathto/not-there.php', $container);
    }
}
