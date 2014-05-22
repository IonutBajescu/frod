<?php

trait UnitTestCaseComponents{

	public function hello()
	{
		// here we have functions for UnitTestCase
	}
}

if($loader->loadClass('Illuminate\Foundation\Testing\TestCase')){

	class UnitTestCase extends Illuminate\Foundation\Testing\TestCase{
		use UnitTestCaseComponents;
		// dont touch this, touch UnitTestCaseComponents

		/**
		 * Creates the application.
		 *
		 * @return \Symfony\Component\HttpKernel\HttpKernelInterface
		 */
		public function createApplication()
		{
			$unitTesting = true;

			$testEnvironment = 'testing';

			return require __DIR__.'/../../../../bootstrap/start.php';
		}
	}

} else{

	class UnitTestCase extends PHPUnit_Framework_TestCase{
		use UnitTestCaseComponents;
		// dont touch this, touch UnitTestCaseComponents
	}

}
