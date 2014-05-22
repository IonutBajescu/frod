<?php

if(!function_exists('frod_config')){
	function frod_config($index)
	{
		static $config;
		if(!$config) $config = new Ionut\Frod\Config();
		return $config->get($index);
	}
}

if(!function_exists('frodLink')){
	function frodLink($htmlElement)
	{
		preg_match('/(src|link)=(.*)/', $htmlElement, $matches);
		return $matches[2];
	}
}

if(!function_exists('pathUpLevels')){
	/**
	 *
	 *
	 * @return string
	 */
	function pathUpLevels($path, $levels)
	{
		for ($i=1; $i < $levels; $i++) {
			$path = dirname($path);
		}
		return $path;
	}
}

if(!function_exists('clearStorages')){
	/**
	 * Clear cached assets from storage.
	 *
	 * @return void
	 */
	function clearStorages()
	{
		$local = new \Ionut\Frod\Storage\Local;
	    $local->deleteAll();
	}
}

if(!function_exists('camel_case')){
	/**
	 * Translates a string with underscores
	 * into camel case (e.g. first_name -> firstName)
	 *
	 * @param string $str String in underscore format
	 * @param bool $capitalise_first_char If true, capitalise the first char in $str
	 * @return string $str translated into camel caps
	 */
	function camel_case($str, $capitalise_first_char = false) {
	  if($capitalise_first_char) {
	    $str[0] = strtoupper($str[0]);
	  }
	  $func = create_function('$c', 'return strtoupper($c[1]);');
	  return preg_replace_callback('/_([a-z])/', $func, $str);
	}
}


if(!function_exists('snake_case')){
	/**
	 * Translates a camel case string into a string with
	 * underscores (e.g. firstName -> first_name)
	 *
	 * @param string $str String in camel case format
	 * @return string $str Translated into underscore format
	 */
	function snake_case($str) {
	  $str[0] = strtolower($str[0]);
	  $func = create_function('$c', 'return "_" . strtolower($c[1]);');
	  return preg_replace_callback('/([A-Z])/', $func, $str);
	}
}

if(!function_exists('array_except')){
	/**
	 * Get all of the given array except for a specified array of items.
	 * Helper from Laravel.
	 *
	 * @param  array  $array
	 * @param  array  $keys
	 * @return array
	 */
	function array_except($array, $keys)
	{
		return array_diff_key($array, array_flip((array) $keys));
	}
}

if(!function_exists('var_debug')){
	/**
	 * Short debug function. Infinite arguments with variabiles for debug.
	 * Last parameter can be a instance of DebugConfig.
	 *
	 * @param  mixed $var,... unlimited Var for debug.
	 * @param  DebugConfig $config OPTIONAL
	 * @return string
	 */
	function var_debug(){
		static $callNumber = 0;

		$callNumber++;

		$output = '';
		$vars = func_get_args();
		if( $vars[count($vars)-1] instanceOf DebugConfig ){
			$config = $vars[count($vars)-1];
			unset($vars[count($vars)-1]);
		} else{
			$config = new DebugConfig();
		}

		foreach($vars as $k => $var){
			$output .= '<pre>'.var_export($var, true).'</pre>';
		}

		if($config->withClear){
			while(@ob_end_clean()){};
			ob_start();
		}

		$error = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
		if($config->withEcho){
			echo $output;

			echo '<button onclick="el=document.getElementById(\'backtrace'.$callNumber.'\'); el.style.display = (el.style.display != \'none\' ? \'none\' : \'\' );">Backtrace</button><div id="backtrace'.$callNumber.'" style="display:none"><pre>'.var_export($error, true).'</pre> <hr/></div>';
			echo ' <hr/>';
		}


		if($config->exitAfterDebug) exit;


		return $output;
	}
}

if(!class_exists('DebugConfig')){
	// Add this class on last arguments of var debug and debug don't show output
	class DebugConfig{
		public $withEcho = true;
		public $withClear = true;
		public $exitAfterDebug = true;

		public function __construct($vars = array()){
			foreach($vars as $var => $value){
				$this->$var = $value;
			}
		}
	};
}

if (!function_exists('vd')) {
	function vd(){
		return call_user_func_array('var_debug', func_get_args());
	}
}

if(!function_exists('dd')){
	function dd(){
		return call_user_func_array('var_debug', func_get_args());
	}
}

if(!function_exists('public_path')){
	/**
	 * Generate public(where we have assets.) path.
	 *
	 * @param  string $appends String to append to path.
	 * @return string
	 */
	function public_path($appends = ''){
		return pathUpLevels(__DIR__, 7).'/'.$appends;
	}
}

if (!function_exists('base_url')) {
    function base_url($atRoot=FALSE, $atCore=FALSE, $parse=FALSE){
        if (isset($_SERVER['HTTP_HOST'])) {
            $http = isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off' ? 'https' : 'http';
            $hostname = $_SERVER['HTTP_HOST'];
            $dir =  str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']);

            $core = preg_split('@/@', str_replace($_SERVER['DOCUMENT_ROOT'], '', realpath(dirname(__FILE__))), NULL, PREG_SPLIT_NO_EMPTY);
            $core = $core[0];

            $tmplt = $atRoot ? ($atCore ? "%s://%s/%s/" : "%s://%s/") : ($atCore ? "%s://%s/%s/" : "%s://%s%s");
            $end = $atRoot ? ($atCore ? $core : $hostname) : ($atCore ? $core : $dir);
            $base_url = sprintf( $tmplt, $http, $hostname, $end );
        }
        else $base_url = 'http://localhost/';

        if ($parse) {
            $base_url = parse_url($base_url);
            if (isset($base_url['path'])) if ($base_url['path'] == '/') $base_url['path'] = '';
        }

        return $base_url;
    }
}


if(!function_exists('url')){
	/**
	 * Generate public url.
	 *
	 * @param  string $appends String to append to path.
	 * @return string
	 */
	function url($appends){
		return base_url().$appends;
	}
}


?>