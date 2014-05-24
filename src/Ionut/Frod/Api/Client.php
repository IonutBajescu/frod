<?php namespace Ionut\Frod\Api;

class Client{

	/**
	 * Servers where we make requests.
	 *
	 * @var array
	 */
	public $servers = array('http://server.frod.ionut-bajescu.com');

	/**
	 * API Version.
	 *
	 * @var string
	 */
	public $version = 'v1';


	/**
	 * Add server once(just for this request) to api client.
	 *
	 * @param string $host
	 */
	public function addServer($host)
	{
		$this->servers[] = $host;
	}

	/**
     * Make a request to server.
	 *
	 * @param string $method
	 * @param string $resource
	 * @param array  $params
	 */
	public function request($method, $resource, $params = array())
	{
		$responses = array();

		$paramsString = http_build_query($params);
		foreach($this->servers as $server)
		{
			$ch = curl_init();
			curl_setopt_array($ch, array(
				CURLOPT_URL            => $server.'/'.$this->version.'/'.$resource,
				CURLOPT_HEADER         => false,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_POSTFIELDS     => $paramsString,
				CURLOPT_CUSTOMREQUEST  => $method,
				CURLOPT_HTTPHEADER     => ['X-HTTP-Method-Override: '.$method]
				));
			$response = curl_exec($ch);
			if($response === null){
				throw new Exception("We can't connect to $server ", 1);
			}

			$response = json_decode($response);

			$responses[] = $response;
		}
		return $responses;
	}


	/**
     * Make a request to server and combine all responses
     * into a single response.
     * If server tell we to stop we return last response.
	 *
	 * @param string $method
	 * @param string $resource
	 * @param array  $params
	 */
	public function combineRequest()
	{
		$responses   = call_user_func_array([$this, 'request'], func_get_args());
		$responseSum = array();
		foreach($responses as $response){
			$responseSum = (array)$response+$responseSum;
		}
		return (object)$responseSum;
	}

	/**
	 * Create new Client instance and resolve dependencies.
	 *
	 * @return Ionut\Frod\Api\Client
	 */
	public static function factory()
	{
		return new self;
	}
}
