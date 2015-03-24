<?php

class SystemTest extends PHPUnit_Framework_TestCase{
	public function testControllerLoad(){
		$app = new Kit\Core\System();

		$app->run('Welcome@getIndex');

		Kit\Core\Output::end();
		$output = Kit\Core\Output::get();

		$this->assertEquals($output, 'controller load');
	}
}