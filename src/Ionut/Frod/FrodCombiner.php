<?php namespace Ionut\Frod;

trait FrodCombiner {

	/**
	 * Combine multiple packages in a short number of files.
	 *
	 * If you have 20 css packages, why you need 20 css files?! One file is enough.
	 * But, we dont have time to combine manually. And this function save your time.
	 *
	 * @param string ...$package Unlimited numbers, package name or src
	 */
	public function combine()
	{

		// first, we prepare the $fileTypes array with all src files
		// in a short parsable format, [ 'css' => ['css/baz.css'] ]
		$fileTypes = array();
		foreach(func_get_args() as $package) {
			$sources = $this->preparePackageSources($package);

			foreach($sources as $source){
				list($src, $ext) = $source;
				$fileTypes[$ext][] = $src;
			}
		}

		// step 2, we determine file names for each file type
		// and if a file is updated, we delete cached file for this
		// if cached file is deleted we on step 3 recombine them
		$filenames = array();
		foreach($fileTypes as $ext => $files) {
			$filenames[$ext] = 'combined.'.md5(implode('|', $files)).'.'.$ext;

			foreach($files as $src) {

				if($this->srcHasChanged($src, $filenames[$ext])){
					$this->storage->delete($filenames[$ext]);
				}
			}
		}


		// step 3, combine files that do not exist
		foreach($fileTypes as $ext => $files) {
			if( $this->storage->exists($filenames[$ext]) ){
				// cached file($filenames[$ext]) already exists for this extension.
				// that mean this cached file pass step 2 and dont need combined
				continue;
			}

			$combined = '';
			foreach($files as $file) {
				$combined .= $this->readSource($file);
			}

			$this->storage->save($filenames[$ext], $combined);
		}

		// final step, create output with "cached" filenames
		// all extensions have a specially function for them
		$output = '';
		foreach($fileTypes as $ext => $files) {
			$link = $this->storage->getPublicUrl($filenames[$ext]);
			$output .= $this->$ext( $link );
		}

		return $output;
	}

}


