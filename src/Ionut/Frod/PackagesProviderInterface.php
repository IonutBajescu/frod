<?php namespace Ionut\Frod;

interface PackagesProviderInterface{

	public function src($packageName);

	public function addPackage($name, $version, array $sources);
}

