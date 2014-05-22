<?php namespace Ionut\Frod;


trait FrodCss {

	/**
	 * Generate css element and Compile LESS source to css.
	 * When changes are made compled cache is refreshed.
	 *
	 * @param string $src
	 * @return string html element
	 */
	public function less($src)
	{
		$frod = $this;
		$compiler = function($path) use($frod){
			return $frod->lessc->compile(file_get_contents($path));
		};
		$publicUrl = $this->compileFile($src, $compiler, 'css');

		return $this->cssHtmlElement($publicUrl);
	}

	/**
	 * Generate css element and Compile SCSS/SASS source to css.
	 * When changes are made compled cache is refreshed.
	 *
	 * @param string $src
	 * @return string css html element
	 */
	public function scss($src)
	{
		$frod = $this;
		$compiler = function($path) use($frod){
			return $frod->scssc->compile(file_get_contents($path));
		};

		$publicUrl = $this->compileFile($src, $compiler, 'css');

		return $this->cssHtmlElement($publicUrl);
	}

	/**
	 * alias for scss
	 *
	 * @param  string $src
	 * @return string css html element
	 */
	public function sass()
	{
		return call_user_func_array([$this, 'scss'], func_get_args());
	}


	/**
	 * Create css element with $src or package src.
	 *
	 * @param  string $src
	 * @param  string $localCache
	 * @return string html element
	 */
	public function css($src, $localCache = true)
	{
		$publicUrl = $this->src($src, 'css', $localCache);

		return $this->cssHtmlElement($publicUrl);
	}

	/**
	 * Minify css files.
	 * When changes are made compled cache is refreshed.
	 *
	 * @param  string $src
	 * @return string
	 */
	public function minifyCss($src)
	{
		$compiler = function($path){
			$css = file_get_contents($path);

			// Remove comments
			$css = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css);

			// Remove space after colons
			$css = str_replace(': ', ':', $css);

			// Remove whitespace
			$css = str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '), '', $css);

			return $css;
		};

		$publicUrl = $this->compileFile($src, $compiler, 'min.css');

		return $this->cssHtmlElement($publicUrl);
	}

	/**
	 * Convert $src in dom element.
	 *
	 * @param  string $url
	 * @return string
	 */
	public function cssHtmlElement($url)
	{
		return '<link rel="stylesheet" type="text/css" href="'.$this->formatHtmlUrl($url).'">';
	}
}

