<?php
/**
 * Copyright (c) 2019. Paul Blacknell https://github.com/blacknell
 */

namespace Blacknell\RestApiService;

use Monolog\Logger;
use Monolog\Handler\NullHandler;

use RuntimeException;

abstract class RestAPI
{
	/**
	 * Property: method
	 * The HTTP method this request was made in, either GET, POST, PUT or DELETE
	 */
	protected $method = '';

	/**
	 * Property: endpoint
	 * The Model requested in the URI. eg: /files
	 */
	protected $endpoint = '';

	/**
	 * Property: verb
	 * An optional additional descriptor about the endpoint, used for things that can
	 * not be handled by the basic methods. eg: /files/process
	 */
	protected $verb = '';

	/**
	 * Property: args
	 * Any additional URI components after the endpoint and verb have been removed, in our
	 * case, an integer ID for the resource. eg: /<endpoint>/<verb>/<arg0>/<arg1>
	 * or /<endpoint>/<arg0>
	 */
	protected $args = Array();

	/**
	 * Property: file
	 * Stores the input of the PUT request
	 */
	protected $file = null;

	/**
	 * @var Logger|null created via Monolog\Monolog
	 */
	protected $logger = null;

	/**
	 * API constructor.
	 *
	 * @param             $request
	 * @param \Monolog\Logger|null $logger created via Monolog\Monolog
	 *
	 * @throws RuntimeException
	 */
	public function __construct($request, \Monolog\Logger $logger = null)
	{
		if (isset($logger) && $logger) {
			$this->logger = $logger;
		}
		else {
			$this->logger = new Logger('');
			$logHandler = new NullHandler();
			$this->logger->pushHandler($logHandler);
		}

		@header("Content-Type: application/json");

		$this->args = explode('/', rtrim($request, '/'));
		$this->endpoint = array_shift($this->args);
		if (array_key_exists(0, $this->args) && !is_numeric($this->args[0])) {
			$this->verb = array_shift($this->args);
		}

		$this->method = $_SERVER['REQUEST_METHOD'];
		if ($this->method == 'POST' && array_key_exists('HTTP_X_HTTP_METHOD', $_SERVER)) {
			if ($_SERVER['HTTP_X_HTTP_METHOD'] == 'DELETE') {
				$this->method = 'DELETE';
			}
			else if ($_SERVER['HTTP_X_HTTP_METHOD'] == 'PUT') {
				$this->method = 'PUT';
			}
			else {
				$this->logger->notice("Unexpected Header", $this->toObject());
				throw new RuntimeException("Unexpected Header", 400);
			}
		}

		switch ($this->method) {
			case 'DELETE':
			case 'POST':
				$this->request = $this->cleanInputs(file_get_contents("php://input"));
				break;
			case 'GET':
				$this->request = $this->cleanInputs($_GET);
				break;
			case 'PUT':
				$this->request = $this->cleanInputs($_GET);
				$this->file = file_get_contents("php://input");
				break;
			default:
				$this->logger->notice("Method Not Allowed", $this->toObject());
				throw new RuntimeException("Method Not Allowed", 405);
				break;
		}

		if( !$this->isAuthenticated()) {
			$this->logger->warning("Authentication failed", $this->toObject());
			throw new RuntimeException("Unauthorized", 401);
		}
	}

	/**
	 * Overide this method to consider whether the request is authenticated
	 *
	 * @return bool true if authenticated
	 */
	protected function isAuthenticated()
	{
		return true;
	}

	/**
	 * Returns the request components as an object
	 *
	 * @return array
	 */
	protected function toObject()
	{
		return [
			'method'=> $this->method,
			'endpoint'=> $this->endpoint,
			'verb'=> $this->verb,
			'args'=> $this->args,
			'remote_addr' => isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '',
		];
	}

	/**
	 * @return false|string
	 */
	public function processAPI()
	{
		if ((int) method_exists($this, $this->endpoint) > 0) {
			$result = $this->{$this->endpoint}($this->args);
			$this->logger->debug("API processed", array($this->toObject(), $result));

			return $this->response($result['result'], $result['code']);
		}
		$this->logger->error("No endpoint: $this->endpoint", $this->toObject());

		return $this->response(['error' => "No endpoint", 'code' => 404], 404);
	}

	/**
	 * @param     $data returned from the protected method mapped to the endpoint
	 *                           might include an error
	 * @param int $status
	 *
	 * @return false|string
	 */
	private function response($data, $status = 200)
	{
		@header("HTTP/1.1 " . $status . " " . $this->requestStatus($status));
		if (is_object($data))
			return $data;
		else
			return json_encode($data);
	}

	/**
	 * @param $data recursively clean the data to remove html/php tags
	 *
	 * @return array|string
	 */
	private function cleanInputs($data)
	{
		$cleanInput = Array();
		if (is_array($data)) {
			foreach ($data as $k => $v) {
				$cleanInput[$k] = $this->cleanInputs($v);
			}
		}
		else {
			$cleanInput = trim(strip_tags($data));
		}

		return $cleanInput;
	}

	/**
	 * @param $code
	 *
	 * @return mixed
	 */
	private function requestStatus($code)
	{
		$status = array(
			200 => 'OK',
			201 => 'Created',
			202 => 'Accepted',
			400 => 'Bad Request',
			401 => 'Unauthorized',
			403 => 'Forbidden',
			404 => 'Not Found',
			405 => 'Method Not Allowed',
			500 => 'Internal Server Error',
		);

		return ($status[$code]) ? $status[$code] : $status[500];
	}
}

//warning - do not let any output after this closing brace
?>