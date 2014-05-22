<?php namespace Ionut\Frod\Facades;

class Client extends Facade{

	public static function getSingletonInstance()
	{
		return isset(static::$instance)
		    ? static::$instance
		    : static::$instance = \Ionut\Frod\Api\Client::factory();
	}
}

