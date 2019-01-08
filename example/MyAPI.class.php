<?php

require __DIR__.'/../vendor/autoload.php';
require __DIR__.'/../src/API.class.php';

use Blacknell\RestApiService\API as API;

class MyAPI extends API
{

	// first all the protected functions which are exposed as REST API endpoints

	/**
	 * @return array
	 *
	 * /home/v1/daylight/littlehampton/yesterday maps to api.php?request=daylight/littlehampton/yesterday
	 * daylight   /littlehampton/yesterday
	 * endpoint   /verb         /args[0]   /args[1]
	 */
	protected function daylight()
	{

		if ($this->method == 'GET') {

			try {

				$query = '';
				if (isset($this->args[0]) && $this->args[0] != '') {
					$query = $this->args[0];
				}

				switch ($this->verb) {
					case 'littlehampton':
						$latitude = 50.807861;
						$longitude = -0.544835;
						break;
					default:
						throw(new OutOfBoundsException('Unknown verb (location)', 400));
						break;
				}

				switch ($query) {
					case  'yesterday':
						{
							$now = new DateTime();
							$yesterday = new DateTime();
							$yesterday->sub(new DateInterval('P1D'));

							$sunrise = date_sunrise($yesterday->getTimeStamp(), SUNFUNCS_RET_TIMESTAMP, $latitude, $longitude, 90.83333, $yesterday->getOffset() / 3600);
							$sunset = date_sunset($yesterday->getTimeStamp(), SUNFUNCS_RET_TIMESTAMP, $latitude, $longitude, 90.83333, $yesterday->getOffset() / 3600);

							$start = new DateTime();
							$start->setTimeStamp($sunrise);
							$finish = new DateTime();
							$finish->setTimeStamp($sunset);

							$obj = [
								'description' => 'Between sunrise and sunset yesterday',
								'location' => [
									'latitude' => $latitude,
									'longitude' => $longitude,
									],
								'now'         => $now,
								'sunrise'       => $start,
								'sunset'      => $finish,
							];

							break;
						}
					default:
						{
							throw(new OutOfBoundsException('Unknown args[0] (when)',400));
							break;
						}
				}

				return array("result" => $obj, "code" => 200);

			}
			catch (OutOfBoundsException $e) {
				$this->logger->error($e->getMessage(), $this->toString());
				$this->logger->error($e->getTraceAsString());

				return array("result" => ['error' => $e->getMessage(), 'code' => $e->getCode()], "code" => $e->getCode());
			}
			catch (Exception $e) {
				$this->logger->error($e->getMessage(), $this->toString());
				$this->logger->error($e->getTraceAsString());

				return array("result" => ['error' => $e->getMessage(), 'code' => 400], "code" => 400);
			}
		}
		else {
			header("Allow: GET");

			return array("result" => ['error' => "Only accepts GET requests", 'code' => 405], "code" => 405);
		}
	}

	//
	//
	// now any private helper functions
	//
	//

}

?>