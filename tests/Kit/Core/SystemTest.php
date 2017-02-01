<?php

use Kit\Core\System;
use Kit\Core\Output;

class SystemTest extends PHPUnit_Framework_TestCase
{
    protected static $app;

    public static function setUpBeforeClass()
    {
        self::$app = new System();
    }

    public function testInit()
    {
        $this->assertEquals(System::class, get_class( self::$app ));
    }

    public function testCliControllerLoad()
    {
        System::setArgv([__FILE__, 'Welcome@getIndex']);

        self::$app->run();

        $output = Output::get();
        Output::clean();

        $this->assertEquals('controller load', $output);

        System::$argv = NULL;
    }

    public function testDefaultLoad()
    {
        self::$app->run();

        $output = Output::get();
        Output::clean();

        $this->assertEquals('controller load', $output);
    }

    public function testControllerLoad()
    {
        self::$app->run('Welcome@getIndex');

		$output = Output::get();
        Output::clean();

		$this->assertEquals('controller load', $output);
	}

    public function testJsonControllerLoad()
    {
        self::$app->run('Json@getIndex');

        $output = Output::get();
        Output::clean();

        $this->assertEquals('{"status":"ok"}', $output);
    }

    public function testBadController()
    {
        self::$app->run('Welcome@getIndex2');

        $output = Output::get();
        Output::clean();

        $this->assertEquals('Kit\Exception\HttpNotFoundException', substr($output, 0, 35));
    }
}