<?php namespace Ionut\Frod;

trait StorageAdapter{

	/**
	 * Generate public local url for an src.
	 *
	 * @param  string  $src
	 * @param  string  $prefix
	 * @return string
	 */
	public function storageGet($src, $prefix = '')
	{
		$filename = $this->storageGetFileName($src, $prefix);
		return $this->storage->getPublicUrl($filename);
	}

	/**
	 * Generate local path for an src;
	 *
	 * @param  string  $src
	 * @return string
	 */
	public function storageGetFile($src)
	{
		return $this->storage->getFile($this->storageGetFileName($src));
	}

	/**
	 * Get unqiue filename for an src.
	 *
	 * @param  string  $src
	 * @param  string  $prefix
	 * @return string
	 */
	public function storageGetFileName($src, $prefix = '')
	{
		return $prefix.md5($src).'-'.basename($src);
	}
}

