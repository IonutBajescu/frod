<?php namespace Ionut\Frod;

class PackagesProvider implements PackagesProviderInterface {
	use FileUtilities;

	public $file = null;
	public $cacheFolder = null;

	/**
	 * Create new PackagesProvider instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->file = __DIR__.'/../../../data/packages.json';
		$this->packages = $this->parsePackages();
	}

	/**
	 * Add package
	 *
	 * @param  string $name
	 * @param  float  $version
	 * @param  array  $sources
	 * @return void
	 */
	public function addPackage($name, $version, array $sources)
	{
		$this->packages->$name = (object)[
			'version' => $version,
			'src' => $sources
		];
	}

	/**
	 * Parse packages file.
	 *
	 * @return object
	 */
	public function parsePackages()
	{
		return json_decode( file_get_contents($this->file) );
	}

	/**
	 * Generate src of a package, and prioritize preffered extension.
	 *
	 * @param  string name
	 * @param  string preferredExt
	 * @return string
	 */
	public function src($name, $preferredExt = null)
	{
		if(!$this->exists($name)){
			return false;
		}

		$package = $this->get($name);

		$index = 0;
		if($preferredExt != null && count($package->src) > 0){
			foreach($package->src as $k => $source){
				if($this->fileExt($source) == $preferredExt){
					$index = $k;
				}
			}
		}

		return $package->src[$index];
	}

	/**
	 * For a moment package version is directly implemented in package name.
	 * So, if you need jquery:1.2 $this->get just search package with name "jquery:1.2".
	 *
	 * @param  string $name
	 * @return object
	 */
	public function get($name)
	{
		return isset($this->packages->$name) ? $this->packages->$name : false;
	}

	/**
	 * Check if a package exists.
	 *
	 * @param  string $package
	 * @return object
	 */
	public function exists($package)
	{
		return $this->get($package);
	}

}
