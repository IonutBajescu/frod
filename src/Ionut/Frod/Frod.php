<?php
/**
 * Frod Package, Frontend Packages Management - Just for happy programmers.
 *
 * @author Ionut Bajescu
 */
namespace Ionut\Frod;

class Frod {
	use FrodCss, FrodJs, FrodCombiner;
	use FileUtilities, StorageAdapter;

	/**
	 * all extensions MUST have a function with hes name
	 *
	 * @var array
	 */
	public $allowedPackageExtensions = ['css', 'js', 'less', 'scss', 'sass', 'minifyCss', 'minifyJs'];

	/**
	 * If you want to use singleton, in this variabile we save instance.
	 *
	 * @var \Ionut\Frod\Frod
	 */
	public static $instance;

	public $temporarCacheOff = false;
	public $temporarCacheOn  = false;
	public $cacheOn          = true;


	/**
	 * Create new Frod instance.
	 *
	 * @param Storage\StorageInterface   $storage
	 * @param PackagesProviderInterface  $packagesProvider
	 * @param \lessc                     $lessc
	 * @param \scssc                     $scssc
	 * @return void
	 */
	public function __construct(Storage\StorageInterface $storage, PackagesProviderInterface $packagesProvider, \lessc $lessc, \scssc $scssc)
	{
		$this->provider = $packagesProvider;
		$this->storage  = $storage;
		$this->lessc    = $lessc;
		$this->scssc 	= $scssc;

		if(!defined('FROD_BOOSTRAP_INCLUDED')){
			include 'bootstrap.php';
		}
	}

	/**
	 * Generate dinamic functions
	 *
	 * @param  string $method
	 * @param  array  $args
	 * @return mixed
	 */
	public function __call($method, $args){

		if($this->methodIsChaining($method)){
			$methods = explode('_', snake_case($method));
			return call_user_func_array([$this, 'chaining'], [$args,$methods]);
		}

		if(preg_match("/(.*)Link$/", $method, $matches)){
			$htmlElement = call_user_func_array([$this, $matches[1]], $args);
			return $this->htmlToLink($htmlElement);

		} elseif(preg_match("/(.*)Links$/", $method, $matches)){
			$htmlElement = call_user_func_array([$this, $matches[1]], $args);
			return $this->htmlToLinks($htmlElement);

		} else{
			throw new \Exception("Method $method don't exists.");
		}

	}


	/**
	 * Disable/Enable cache, serve files directly from cdn.
	 *
	 * @param  bool $cache
	 * @return void
	 */
	public function setCache($cache)
	{
		$this->cacheOn = $cache;
	}


	/**
	 * Turn temporar(one-time) off local cache, use dirrecly cdn.
	 *
	 * @return \Ionut\Frod\Frod
	 */
	public function withoutCache()
	{
		$this->temporarCacheOff = true;
		return $this;
	}

	/**
	 * Turn temporar(one-time) on local cache.
	 *
	 * @return \Ionut\Frod\Frod
	 */
	public function withCache()
	{
		$this->temporarCacheOff = true;
		return $this;
	}

	public function checkCacheOn()
	{
		if($this->temporarCacheOff){
			$this->temporarCacheOff = false;
			return false;
		}

		if($this->temporarCacheOn){
			$this->temporarCacheOn = false;
			return true;
		}

		return $this->cacheOn;
	}

	/**
	 * Check if method is chaing.
	 * Example chaining methods:
	 * 	   combineMovable
	 * 	   minifyCombine
	 *     minifyCombineMovable
	 *
	 * @param  string $method
	 * @return bool
	 */
	public function methodIsChaining($method)
	{
		$chainingCanditates = ['minify', 'combine', 'movable', 'less', 'sass'];
		if(preg_match('/('.implode('|', $chainingCanditates).')/i', $method, $matches)){
			$tempCandidates = array_except($chainingCanditates, array_search($matches[1], $chainingCanditates));
			if(preg_match('/('.implode('|', $tempCandidates).')/i', $method, $matches)){
				return true;
			}
		}

		return false;
	}

	/**
	 * Allow methods chaning without classicaly "return $this".
	 *
	 * Example chaining methods:
	 * 	   combineMovable
	 * 	   minifyCombine
	 *     minifyCombineMovable
	 *
	 * @param  array $packages
	 * @param  string ...$methods
	 * @return string
	 */
	public function chaining($packages, $methods)
	{
		foreach($methods as $method){
			$packages = call_user_func_array([$this, $method.'Links'], $packages);
		}

		$output = '';
		foreach($packages as $src){
			$output .= $this->htmlElement($src);
		}

		return $output;
	}

	/**
	 * Autoload all dependency for fast use.
	 *
	 * @return \Ionut\Frod\Frod
	 */
	static public function factory()
	{
		$lessc 	  = new \lessc;
		$scssc    = new \scssc;
		$storage  = new Storage\Local();
		$packages = new PackagesProvider();
		$frod     = new self($storage, $packages, $lessc, $scssc);
		return $frod;
	}

	/**
	 * Singleton for \Ionut\Frod\Frod
	 *
	 * @return \Ionut\Frod\Frod
	 */
	public static function singleton()
    {
        return isset(static::$instance)
            ? static::$instance
            : static::$instance = static::factory();
    }

	/**
	 * Compile a source, with given $compiler.
	 *
	 * @param string    $src
	 * @param callable  $compiler
	 * @param string    $compilerResult Extension for compiled file.
	 */
	public function compileFile($src, callable $compiler, $compilerResult)
	{

		$srcFilename      = $this->storageGetFileName($src); // file from compile

		$compiledFilename = $srcFilename.'.'.$compilerResult; // target file

		$fileChanged      = $this->srcHasChanged($srcFilename, $compiledFilename);

		// if local file, eg css/test.scss is modified then we update
		if(!$this->storage->exists($compiledFilename) || $fileChanged ){

			$http_src = $this->src($src);
			$path     = $this->storageGetFile($http_src);

			$compiled = $compiler($http_src);
			$this->storage->save($compiledFilename, $compiled);
		}

		return $this->storage->getPublicUrl($compiledFilename);
	}


	/**
	 * Minify sources. This function have ability to determine
	 * correct function for minify helped by extension.
	 *
	 * @param  string ...$name
	 * @return string
	 */
	public function minify()
	{
		$links = array();
		foreach(func_get_args() as $name){
			$sources = $this->preparePackageSources($name);

			foreach($sources as $source){
				list($src, $ext) = $source;
				$links[] .= $this->{$ext.'Link'}($src);
			}
		}

		$output = '';
		foreach($links as $k => $link){
			$ext = $this->fileExt($link);
			$output .= $this->{camel_case('minify_'.$ext)}($link);
		}

		return $output;
	}

	/**
	 * Return separated css sources and js sources from packages.
	 *
	 * @param  string ...$package Package name. Unlimited arguments.
	 * @return object
	 */
	public function movable()
	{
		$packagesHtml = call_user_func_array([$this, 'packages'], func_get_args());

		return $this->separateCssAndJs($packagesHtml);
	}

	/**
	 * Separate js and css from html.
	 *
	 * @param  string packagesHtml
	 * @return object
	 */
	public function separateCssAndJs($packagesHtml)
	{
		preg_match_all('#<script(.*?)</script>#', $packagesHtml, $matches);
		foreach($matches[0] as $script){
			$packagesHtml = str_replace($script, '', $packagesHtml);
		}

		list($css, $js) = [$packagesHtml, implode('', $matches[0])];
		return (object)compact('css', 'js');
	}


	/**
	 * Convert packages names into html.
	 *
	 * @param  string ...$name
	 * @return string
	 */
	public function packages()
	{
		$output = '';

		foreach(func_get_args() as $packageName){
			$output .= $this->package($packageName);
		}

		return $output;
	}

	/**
	 * Convert package name into html.
	 *
	 * @param  string $name
	 * @return string
	 */
	public function package($name)
	{
		$output = '';

		$sources = $this->preparePackageSources($name);

		foreach($sources as $source){
			list($src, $ext) = $source;
			$output .= $this->$ext($src);
		}

		return $output;
	}

	/**
	 * Return package sources.
	 *
	 * @param  string $name
	 * @return array
	 */
	public function preparePackageSources($name)
	{
		$sources = array();

		$package = $this->provider->get($name);
		if($package == false){
			// if $package is false that mean src is passed into $name argument
			// we allow that for make functions more fast to use
			$package = new \stdClass;
			$package->src = [$name];
		}

		foreach($package->src as $src){
			$ext = $this->fileExt($src);
			if(in_array($ext, $this->allowedPackageExtensions)){
				$sources[] = [$src, $ext];
			} else{
				throw new \Exception("Package file extension '$ext' from '$src' not allowed.");
			}
		}

		return $sources;
	}


	/**
	 * Convert package $packageOrSrc in src, if $packageOrSrc is
	 * src we return $packageOrSrc value.
	 * If $storage is true all src is proxyed by a local file.
	 *
	 * @param  string $packageOrSrc
	 * @param  string $preferredExt
	 * @param  string $storage
	 * @return string Package src or $packageOrSrc param
	 */
	public function src($packageOrSrc, $preferredExt = null)
	{

		// if $packageOrSrc is a package we replace $packageOrSrc with package src
		// if not this mean $packageOrSrc is a url, and we keep initial value
		$src = $this->provider->src($packageOrSrc, $preferredExt) ?: $packageOrSrc;

		$storageFilename  = $this->storageGetFileName($src);
		$storageOutOfDate = $this->srcHasChanged($packageOrSrc, $src);

		$cache = $this->checkCacheOn();
		if( $cache ){
			// if src is not in storage we save src in storage
			if( !$this->storage->exists($storageFilename) || $storageOutOfDate ){
				$src_contents = file_get_contents($src);
				$this->storage->save($storageFilename, $src_contents);
			}
		}

		return $cache ? $this->storageGet($src) : $src;
	}

	/**
	 * Check if src has changed in relation to a storage filename.
	 *
	 * @see FileUtilities
	 * @param  string $src
	 * @param  string $targetFileName
	 * @return bool
	 */
	public function srcHasChanged($src, $targetFileName)
	{
		$fileIsLocal = !preg_match('#http://#', $src) && !preg_match('#https://#', $src);
		if($fileIsLocal){
			return $this->fileChanged($src, $this->storage->getFile($targetFileName));
		} else{
			// if file not is local we don't know if he changed
			return false;
		}
	}

	/**
	 * Return html for src.
	 *
	 * @param  string $src
	 * @return string|false
	 */
	public function readSource($src)
	{
		return file_get_contents($src);
	}

	/**
	 * Format an url for make him SSL-compatible.
	 *
	 * @param  string $url
	 * @return string
	 */
	public function formatHtmlUrl($url)
	{
		$pre = !empty($_SERVER['HTTPS']) ? 'https' : 'http';
		return str_replace('http://', $pre.'://', $url);
	}

	/**
	 * Transform html to link.
	 * <string src="frod.js"></script>     		become frod.js
	 * <link rel="stylesheet" href="frod.css">  become frod.css
	 *
	 * @param  string $html
	 * @return string
	 */
	public function htmlToLink($html)
	{
		$links = $this->htmlToLinks($html);
		return $links[0];
	}

	/**
	 * Transform html to links.
	 * <string src="frod.js"></script> <link rel="stylesheet" href="frod.css">
	 * become ['frod.js', 'frod.css'].
	 *
	 * @param  string $html
	 * @return string
	 */
	public function htmlToLinks($html)
	{
		preg_match_all('/(src|href)="([^"]*)"/', $html, $matches);
		if(!isset($matches[2])) return $html;
		return $matches[2];
	}

	/**
	 * Transform src in html. In opposition with htmlToLink
	 *
	 * @param  string $src
	 * @return string
	 */
	public function htmlElement($src)
	{
		$method = $this->fileExt($src) == 'js' ? 'jsHtmlElement' : 'cssHtmlElement';
		return $this->$method($src);
	}

	/**
	 * Add alias for package.
	 *
	 * @param  string $from
	 * @param  string $in
	 * @return void
	 */
	public function alias($from, $in)
	{
		$this->provider->addPackage($from, 0, (array)$in);
	}

	/**
	 * Add aliases for package.
	 *
	 * @param  array $aliases Format: [$from => $in]
	 * @return void
	 */
	public function aliases($aliases)
	{
		foreach($aliases as $k => $v){
			call_user_func_array([$this, 'alias'], [$k, $v]);
		}
	}
}

