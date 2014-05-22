<?php namespace Ionut\Frod\Storage;

use \Exception;

class Local implements StorageInterface {

	/**
	 * Frod folder, located in public folder(config.public_folder).
	 * In this folder Frod package will save cached assets.
	 *
	 * @var string
	 */
	public $folder = 'frod';

	/**
	 * For testing purposes.
	 *
	 * @var array
	 */
	public $saved  = array();



	public function __construct(){
		if(!defined('FROD_BOOSTRAP_INCLUDED')){
			include __DIR__.'/../bootstrap.php';
		}

		if(!file_exists($this->getFolder())){
			mkdir($this->getFolder());
		}
	}

	/**
	 * Save file on local storage.
	 *
	 * @param  string $name
	 * @param  string $contents
	 * @return string
	 */
	public function save($name, $contents)
	{
		if(defined('CLEAR_LOCAL_STORAGE')){
			$this->saved[] = $name;
		}

		if($this->exists($name)){
			$this->delete($name);
		}

		$checking = file_put_contents($file = $this->getFile($name), $contents);
		if($checking === false){
			error_log("FROD ERROR: File $file is not writable.");
			exit("File $this->folder/$name(full path in error log) is not writable, please give proper permissions for frod folder.");
		}
		return $checking;
	}

	/**
	 * Generate local public url.
	 *
	 * @param  string $name
	 * @return string
	 */
	public function getPublicUrl($name)
	{
		return url($this->folder.'/'.$name);
	}

	/**
	 * Check if filename exists in local storage.
	 *
	 * @param  string $name
	 * @return bool
	 */
	public function exists($name)
	{
		return file_exists($this->getFile($name));
	}

	/**
	 * Delete all files from frod folder.
	 *
	 * @return void
	 */
	public function deleteAll()
	{
		$files = glob(public_path($this->folder.'/*'));
		foreach($files as $file){
			unlink($file);
		}
	}

	/**
	 * Delete filename from local.
	 *
	 * @param  string $name
	 * @return string
	 */
	public function delete($name)
	{
		if($this->exists($name)){
			return unlink($this->getFile($name));
		} else{
			return false;
		}
	}

	/**
	 * Generate local public url.
	 *
	 * @param  string $name
	 * @return string
	 */
	public function getFile($name)
	{
		return $this->getFolder().'/'.$name;
	}

	public function getFolder()
	{
		return public_path($this->folder);
	}

	/**
	 * For testing purposes.
	 *
	 * @return void
	 */
	public function __destruct()
	{
		if(defined('CLEAR_LOCAL_STORAGE')){
			foreach($this->saved as $name){
				$this->delete($name);
			}
		}
	}
}

