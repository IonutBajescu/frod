<?php namespace Ionut\Frod\Facades;

class Frod extends Facade{

	public static function getSingletonInstance()
	{
		return \Ionut\Frod\Frod::singleton();
	}
}

