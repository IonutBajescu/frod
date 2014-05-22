<?php

class Frod extends Ionut\Frod\Facades\Facade{

	public static function getSingletonInstance()
	{
		return \Ionut\Frod\Frod::singleton();
	}
}

