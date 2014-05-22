<?php namespace Ionut\Frod\Facades;

class Facade{
	public static $instance;

	public static function getInstance()
	{
		throw new \Exception('Facade MUST implement static getInstance() method.');
	}

	public static function getSingletonInstance(){
		throw new \Exception('Facade MUST implement static getInstance() method.');
	}
		

	public static function __callStatic($method, $arguments)
	{

		return call_user_func_array([static::getSingletonInstance(), $method], $arguments);
	}
}

