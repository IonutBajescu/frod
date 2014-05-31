<?php


define('CLEAR_LOCAL_STORAGE', '');

$loader = include(dirname(dirname(dirname(__DIR__))).'/autoload.php');

include __DIR__.'/UnitTestCase.php';

function base_path()
{
	return pathUpLevels(__DIR__, 5);
}

// this function NOT return URL, in CLI Mode we can't
// determine application URL. This function return path.
function url($path)
{
	$segments = array_reverse(explode('/', __DIR__));

	return base_path().'/'.$path;
}

function public_path($path = '')
{
	return base_path().'/'.$path;
}
