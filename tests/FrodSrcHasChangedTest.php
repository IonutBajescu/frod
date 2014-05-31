<?php

use \Mockery as m;
use Ionut\Frod\Frod;

class FrodSrcHasChangedTest extends UnitTestCase {

	public function testSrcHasChangedWithoutTargetWork()
	{
		list($srcPath, $targetPath) = $this->startCase();

		$this->touch($srcPath);

		$frod = Frod::factory();
		$result = $frod->srcHasChanged(basename($srcPath), basename($targetPath));

		$this->assertEquals($result, true);

		$this->clear($srcPath, $targetPath);
	}

	public function testSrcNotChangedOnUpdateTarget()
	{
		list($srcPath, $targetPath) = $this->startCase();

		$this->touch($srcPath);
		sleep(1);
		$this->touch($targetPath);

		$frod = Frod::factory();
		$result = $frod->srcHasChanged($srcPath, basename($targetPath));

		$this->assertEquals(false, $result);

		$this->clear($srcPath, $targetPath);
	}

	public function testSrcHasChangedOnUpdateSource()
	{
		list($srcPath, $targetPath) = $this->startCase();

		$this->touch($targetPath);
		sleep(1);
		$this->touch($srcPath);

		$frod = Frod::factory();
		$result = $frod->srcHasChanged($srcPath, basename($targetPath));

		$this->assertEquals(true, $result);

		$this->clear($srcPath, $targetPath);
	}

	public function touch()
	{
		foreach (func_get_args() as $path){
			file_put_contents($path, ' ', FILE_APPEND);
		}
	}

	public function clear(){
		foreach (func_get_args() as $path) {
			if(file_exists($path)){
				unlink($path);
			}
		}
	}

	public function startCase(){

		$storage = new Ionut\Frod\Storage\Local;

		$srcPath = public_path().'/phpunit'.time();
		$targetFilename = 'phpUnit'.time();
		$targetPath = $storage->getFile($targetFilename);
		$this->touch($targetPath, $srcPath);
		return array($srcPath, $targetPath);
	}

	public function tearDown()
	{
		if(file_exists(__DIR__.'/temp')){
			rmdir(__DIR__.'/temp');
		}
		if(!file_exists(__DIR__.'/temp')){
			mkdir(__DIR__.'/temp');
		}
	    \Mockery::close();
	}

}



