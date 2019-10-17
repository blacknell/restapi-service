<?php
/**
 * Copyright (c) 2019. Paul Blacknell https://github.com/blacknell
 */

namespace Blacknell\RestApiService;

use PHPUnit\Framework\TestCase;
use Monolog\Logger;
use Monolog\Handler\NoopHandler;

require __DIR__ . '/../StubAPI.php';

use MyStubAPI;

class RestAPITest extends TestCase
{

    public function testProcessGet()
    {
        $log = new Logger('restapi-test');
        $logHandler = new NoopHandler();
        $log->pushHandler($logHandler);

        $_REQUEST['request'] = 'stubGet/ggg/hhh';
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $api = new MyStubAPI($_REQUEST['request'], $log);
        $actualResult = $api->processAPI();
        $expectedResult = '"{\"answer\":5}"';
        $this->assertJsonStringEqualsJsonString($expectedResult, json_encode($actualResult));
    }

    public function testProcessPost()
    {
        $_REQUEST['request'] = 'stubPost';
        $_SERVER['REQUEST_METHOD'] = 'POST';

        $_POST = json_encode(['arg1' => 0]);
        $api = new MyStubAPI($_REQUEST['request'], null);
        $actualResult = $api->processAPI();
        $expectedResult = json_encode(['answer' => 5]);
        $this->assertJsonStringEqualsJsonString(json_encode($expectedResult), json_encode($actualResult));

        $_POST = json_encode(['arg1' => 1]);
        $api = new MyStubAPI($_REQUEST['request'], null);
        $actualResult = $api->processAPI();
        $expectedResult = json_encode(['error' => 'Exception', 'code' => 400]);
        $this->assertJsonStringEqualsJsonString(json_encode($expectedResult), json_encode($actualResult));

        $_POST = json_encode(['arg1' => 2]);
        $api = new MyStubAPI($_REQUEST['request'], null);
        $actualResult = $api->processAPI();
        $expectedResult = json_encode(['error' => 'OutOfBoundsException', 'code' => 400]);
        $this->assertJsonStringEqualsJsonString(json_encode($expectedResult), json_encode($actualResult));

    }

    public function testProcessPut()
    {
        $_REQUEST['request'] = 'stubPut';
        $_SERVER['REQUEST_METHOD'] = 'PUT';

        $api = new MyStubAPI($_REQUEST['request'], null);
        $actualResult = $api->processAPI();
        $expectedResult = json_encode(['answer' => 5]);
        $this->assertJsonStringEqualsJsonString(json_encode($expectedResult), json_encode($actualResult));

    }

    public function testProcessXHTTP()
    {
        $_REQUEST['request'] = 'stubDelete';
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['HTTP_X_HTTP_METHOD'] = 'DELETE';

        $api = new MyStubAPI($_REQUEST['request'], null);
        $actualResult = $api->processAPI();
        $expectedResult = json_encode(['answer' => 5]);
        $this->assertJsonStringEqualsJsonString(json_encode($expectedResult), json_encode($actualResult));

        $_REQUEST['request'] = 'stubPut';
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['HTTP_X_HTTP_METHOD'] = 'PUT';

        $api = new MyStubAPI($_REQUEST['request'], null);
        $actualResult = $api->processAPI();
        $expectedResult = json_encode(['answer' => 5]);
        $this->assertJsonStringEqualsJsonString(json_encode($expectedResult), json_encode($actualResult));

        $this->expectException("RuntimeException");
        $this->expectExceptionMessage("Unexpected Header");
        $this->expectExceptionCode(400);

        $_REQUEST['request'] = 'stubPut';
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['HTTP_X_HTTP_METHOD'] = 'NOSUCH';

        $api = new MyStubAPI($_REQUEST['request'], null);
        $actualResult = $api->processAPI();
        $expectedResult = json_encode(['answer' => 5]);
        $this->assertJsonStringEqualsJsonString(json_encode($expectedResult), json_encode($actualResult));

    }

    public function testProcessAuthenticated()
    {
        $_REQUEST['request'] = 'stubGet';
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $this->expectException("RuntimeException");
        $this->expectExceptionMessage("Unauthorized");
        $this->expectExceptionCode(401);

        $api = new \MyStubAPIAuthenticated($_REQUEST['request'],null);
    }

    public function testProcessBadMethod()
    {
        $_REQUEST['request'] = 'stubGet';
        $_SERVER['REQUEST_METHOD'] = 'PUTPUT';

        $this->expectException("RuntimeException");
        $this->expectExceptionMessage("Method Not Allowed");
        $this->expectExceptionCode(405);

        $api = new MyStubAPI($_REQUEST['request']);
        $actualResult = $api->processAPI();
    }

    public function testProcessBadEndpoint()
    {
        $_REQUEST['request'] = 'stubNosuch';
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['HTTP_X_HTTP_METHOD'] = 'DELETE';

        $api = new MyStubAPI($_REQUEST['request'], null);
        $actualResult = $api->processAPI();
        $expectedResult = json_encode(['error' => 'No endpoint', 'code' => 404]);
        $this->assertJsonStringEqualsJsonString(json_encode($expectedResult), json_encode($actualResult));

    }



}
