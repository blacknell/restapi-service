<?php
/**
 * Copyright (c) 2019. Paul Blacknell https://github.com/blacknell
 */

use Blacknell\RestApiService\RestAPI;

class MyStubAPI extends RestAPI
{

    public function __construct($request, \Monolog\Logger $logger = null)
    {
        parent::__construct($request, $logger);
    }

    protected function stubGet()
    {

        if ($this->method == 'GET') {

            try {

                $obj = ['answer' => 5];

                return array("result" => $obj, "code" => 200);

            }
            catch (OutOfBoundsException $e) {
                $this->logger->error($e->getMessage(), $this->toObject());
                $this->logger->error($e->getTraceAsString());

                return array("result" => ['error' => $e->getMessage(), 'code' => $e->getCode()], "code" => $e->getCode());
            }
            catch (Exception $e) {
                $this->logger->error($e->getMessage(), $this->toObject());
                $this->logger->error($e->getTraceAsString());

                return array("result" => ['error' => $e->getMessage(), 'code' => 400], "code" => 400);
            }
        }
        else {
            header("Allow: GET");

            return array("result" => ['error' => "Only accepts GET requests", 'code' => 405], "code" => 405);
        }
    }

    protected function stubPost()
    {

        if ($this->method == 'POST') {

            try {

                $obj = $this->request;

                return array("result" => $obj, "code" => 200);

            }
            catch (OutOfBoundsException $e) {
                $this->logger->error($e->getMessage(), $this->toObject());
                $this->logger->error($e->getTraceAsString());

                return array("result" => ['error' => $e->getMessage(), 'code' => $e->getCode()], "code" => $e->getCode());
            }
            catch (Exception $e) {
                $this->logger->error($e->getMessage(), $this->toObject());
                $this->logger->error($e->getTraceAsString());

                return array("result" => ['error' => $e->getMessage(), 'code' => 400], "code" => 400);
            }
        }
        else {
            header("Allow: GET");

            return array("result" => ['error' => "Only accepts POST requests", 'code' => 405], "code" => 405);
        }
    }

}