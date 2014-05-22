<?php namespace Ionut\Frod;

trait FrodJs {

	/**
	 * Generate JS html element for an package.
	 *
	 * @param string $src
	 * @param string $localCache True if you want to save local cached copy.
	 */
	public function js($src, $localCache = true)
	{
		$src = $this->src($src, 'js', $localCache);

		return $this->jsHtmlElement($src);
	}

	/**
	 * Minfy js source from path.
	 *
	 * @param string $src
	 */
	public function minifyJs($src)
	{
		$compiler = function($path){
			$js = file_get_contents($path);
			return \JsMin\Minify::minify($js);
		};

		$publicUrl = $this->compileFile($src, $compiler, 'min.js');
		return $this->jsHtmlElement($publicUrl);
	}

	/**
 	 * Generate an js element.
	 *
	 * @param string $url URL
	 */
	public function jsHtmlElement($url)
	{
		return '<script src="'.$this->formatHtmlUrl($url).'"></script>';
	}
}
