<?php
/**
 * Copyright (c) 2019. Paul Blacknell https://github.com/blacknell
 */

namespace Blacknell\RestApiService;

use PHPUnit\Framework\TestCase;

require __DIR__.'/../StubAPI.php';
use MyStubAPI;

class RestAPITest extends TestCase
{

	public function testProcessAPI()
	{
		$_REQUEST['request']='stubGet';
		$_SERVER['REQUEST_METHOD']='GET';

		$api = new MyStubAPI($_REQUEST['request']);
		$actualResult=$api->processAPI();

		$expectedResult = '"{\"answer\":5}"';

		$this->assertJsonStringEqualsJsonString('"{\"answer\":5}"', json_encode($actualResult));
	}
}
