<?php namespace Ionut\Frod;

trait FileUtilities {

	/**
	 * Get file [last] extension
	 *
	 * @param  string $src
	 * @return string
	 */
	public function fileExt($src)
	{
		preg_match('#\.([a-z]+)$#', $src, $matches);
		return isset($matches[1]) ? $matches[1] : false;
	}

	/**
	 * Check if $file1 was changed.
	 *
	 * @param  string $file1
	 * @param  string $file2
	 * @return bool
	 */
	public function fileChanged($file1, $file2)
	{
		if(!file_exists($file1) || !file_exists($file2)){
			return true;
		}
		$changed = filemtime($file1) > filemtime($file2);

		//If file changed we clear all data.
		if($changed){
			$this->storage->deleteAll();
		}

		return $changed;
	}
}