<?php

use \Mockery as m;
use Ionut\Frod\Frod;

class FrodTest extends UnitTestCase {


	public function testAliasing()
	{
		$frod = Frod::factory();

		$frod->alias('frod', 'best front package management tool.');
		$this->assertEquals($frod->withoutCache()->src('frod'), 'best front package management tool.');

		$frod->aliases(['custom-style' => 'css/style.css', 'custom-script' => 'js/script.js']);
		$this->assertEquals($frod->withoutCache()->src('custom-style'), 'css/style.css');
		$this->assertEquals($frod->withoutCache()->src('custom-script'), 'js/script.js');
	}

	public function testMethodsChaining()
	{
		$frod = Frod::factory();

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
		$sources = ['1.css', '2.css'];
		$sources = array_map(function($v){ return public_path().'/phpunit'.microtime(true).$v; }, $sources);
		$this->put($sources[0], $input1);
		$this->put($sources[1], $input2);

		$expected = <<<EXPECTED
div#header {border:1px solid #842210;}div#header2 {border:2em solid #842210;}
EXPECTED;


		$result = $frod->htmlToLink($frod->combineMinify($sources[0], $sources[1]));
		$result = file_get_contents($result);

		$this->assertEquals($expected, $result);
	}

	public function testHtmlToLink()
	{
		$frod = Frod::factory();

		$link = $frod->htmlToLink('<string src="frod.js"></script>');
		$this->assertEquals($link, 'frod.js');

		$link = $frod->htmlToLink('<link rel="stylesheet" href="frod.css">');
		$this->assertEquals($link, 'frod.css');
	}


	public function testSrcStorage()
	{
		$frod = Frod::factory();

		$this->assertNotEquals($frod->src('jquery'), $frod->withoutCache()->src('jquery'));
	}

	public function testCustomCompiler()
	{
		$frod = Frod::factory();
		$compiler = function($path){
			return 'compiled';
		};

		$src = public_path().'/phpunit'.time();
		$this->put($src, 'Hello, i\'m a test.');

		$resultFile = $frod->compileFile($src, $compiler, 'phpunit');
		$result = file_get_contents($resultFile);

		$this->assertEquals($result, 'compiled');
	}



	public function assertFileResultOuput($input, $method, $output)
	{
		$result = $this->fileResult($input, $method, $output);

		$this->assertEquals(trim($output), trim($result));
	}

	public function fileResult($content, $method)
	{
		$frod = Frod::factory();

		$src = public_path().'/phpunit'.microtime(true);
		$this->put($src, $content);

		$resultFile = $frod->{$method}($src);
		$result = file_get_contents($resultFile);
		return $result;
	}

	public function testDifferentPackagesVersion()
	{
		$frod = Frod::factory();

		$this->assertNotEquals($frod->js('jquery:2.1.1'), $frod->js('jquery:2.0.3'));
	}

	public function testPreferentialSource()
	{
		$frod = Frod::factory();

		$cssLink = $frod->cssLink('bootstrap:3.1.1');
		$this->assertStringEndsWith('.css', $cssLink);

		$jsLink = $frod->jsLink('bootstrap:3.1.1');
		$this->assertStringEndsWith('.js', $jsLink);
	}

	public function testCombineSources()
	{
		$jsSource1 = public_path().'/'.'phpunit1.js';
		$jsSource2 = public_path().'/'.'phpunit2.js';
		$source1   = 'file source 1';
		$source2   = 'file source 2';
		$this->put($jsSource1, $source1);
		$this->put($jsSource2, $source2);

		$frod = Frod::factory();
		$combinedFile = $frod->combineLinks($jsSource1, $jsSource2);

		$combinedResult = file_get_contents($combinedFile[0]);

		$this->assertContains($source1, $combinedResult);
		$this->assertContains($source2, $combinedResult);
	}

	public function testFacade()
	{
		$src = \Ionut\Frod\Facades\Frod::src('jquery');
		$this->assertContains('jquery', $src);
	}

	public function testMultiplePackages()
	{
		$frod = Frod::factory();
		$result = $frod->packages('jquery', 'bootstrap');

		$this->assertContains('jquery', $result);
		$this->assertContains('bootstrap', $result);
	}

	public function testMovable()
	{
		$frod = Frod::factory();
		$result = $frod->movable('bootstrap');

		$this->assertContains('.css', $result->css);
		$this->assertContains('.js', $result->js);
	}

	public function testSeparateWithoutJs()
	{

		$frod   = Frod::factory();
		$js     = $frod->jsHtmlElement('example/script.js');
		$result = $frod->separateCssAndJs($js);
		$this->assertEquals($js, $result->js);
		$this->assertEmpty($result->css);
	}


	### Js
	public function testBasicJs()
	{

		$frod = Frod::factory();

		$result = $frod->jsHtmlElement('http://frod.com');
		$this->assertEquals('<script src="http://frod.com"></script>', $result);

		$result = $frod->withoutCache()->js('bootstrap:3.0.3');
		$this->assertEquals($frod->jsHtmlElement('http://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.0.3/js/bootstrap.min.js'), $result);
	}

	public function testMinifyJs()
	{
		$frod = Frod::factory();

		$input = <<<JS
/**
 * Exampel function
 */
function oneExample()
{
	var s = $('#s1').text();
	return s;
}
JS;

		$output = <<<EXPECTED
function oneExample()
{var s=$('#s1').text();return s;}
EXPECTED;

		$this->assertFileResultOuput($input, 'minifyJsLink', $output);
	}



	### CSS
	public function testBasicCss()
	{
		$frod = Frod::factory();

		$result = $frod->cssHtmlElement('http://frod.com');
		$this->assertEquals('<link rel="stylesheet" type="text/css" href="http://frod.com">', $result);

		$result = $frod->withoutCache()->css('bootstrap:3.0.3');
		$this->assertEquals($frod->cssHtmlElement('http://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.0.3/css/bootstrap.min.css'), $result);
	}

	public function testMinifyCss()
	{
		$frod = Frod::factory();

		$input = <<<JS
div#header {
  border: 1px solid #842210;
}
JS;

		$output = <<<EXPECTED
div#header {border:1px solid #842210;}
EXPECTED;

		$this->assertFileResultOuput($input, 'minifyCssLink', $output);
	}

	public function testLess()
	{
		$input = <<<LESS
// Declaring variables
@border-width: 1px;
@red: #842210;

// Using variables
div#header {
    border: @border-width solid @red;
}
LESS;
		$output = <<<EXPECTED
div#header {
  border: 1px solid #842210;
}
EXPECTED;

		$this->assertFileResultOuput($input, 'lessLink', $output);
	}

	public function testSass()
	{
		$input = <<<'SASS'
$text-color: #ff0005;

.frod{
  color: $text-color;
  background: lighten($text-color, 40%);
}
SASS;
		$output = <<<EXPECTED
.frod {
  color: #ff0005;
  background: #ffcccd; }
EXPECTED;

		$this->assertFileResultOuput($input, 'sassLink', $output);
	}

	public function testScss()
	{
			$input = <<<'SCSS'
$text-color: #ff0005;

.frod{
  color: $text-color;
  background: lighten($text-color, 40%);
}
SCSS;
		$output = <<<EXPECTED
.frod {
  color: #ff0005;
  background: #ffcccd; }
EXPECTED;

		$this->assertFileResultOuput($input, 'scssLink', $output);
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



