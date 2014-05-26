<?php

use \Mockery as m;
use Ionut\Frod\Frod;

class FrodBufferTest extends UnitTestCase {


	public function testBuffer()
	{
		$input1 = <<<JS
div#header {
  border: 1px solid #842210;
}
JS;
		$input2 = <<<JS
div#header2 {
  border: 2em solid #842210;
}
JS;
		$sources = ['1.css', '2.css', '3.js'];
		$this->put($sources[0], $input1);
		$this->put($sources[1], $input2);
		$this->put($sources[2], 'bar');

		$frod = Frod::factory();

		$frod->bufferStart();
			echo $frod->cssHtmlElement($sources[0]);
			echo $frod->cssHtmlElement($sources[1]);
			echo $frod->jsHtmlElement($sources[2]);
			echo $frod->packages('jquery:2.0.3', 'bootstrap');
		$result = $frod->bufferStop(['combine']);

		$links = $frod->htmlToLinks($result);
		$this->assertCount(2, $links);

		foreach($links as $link){
			$contents = file_get_contents($link);
			if(preg_match('/\.css$/', $link)){
				$this->assertContains($input1, $contents);
				$this->assertContains($input2, $contents);
			}

			if(preg_match('/\.js$/', $link)){
				$this->assertContains('jQuery v2.0.3', $contents);
			}
		}
	}

	public function testBufferWithMovable()
	{
		$frod = Frod::factory();

		$frod->bufferStart();
			echo $frod->packages('jquery:2.0.3', 'bootstrap');
		$result = $frod->bufferStop(['combine', 'movable']);

		$this->assertNotEmpty($result->js);
		$this->assertNotEmpty($result->css);
	}


	### helpers
	public function put($file, $contents){
		file_put_contents($file, $contents);
		register_shutdown_function(function() use($file){
			$this->clear($file);
		});
	}

	public function clear(){
		foreach (func_get_args() as $path) {
			if(file_exists($path)) unlink($path);
		}
	}


	protected function tearDown()
	{
	    \Mockery::close();
	}

}



