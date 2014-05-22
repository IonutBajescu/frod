<?php namespace Ionut\Frod\Storage;

interface StorageInterface {

	public function save($name, $contents);

	public function getPublicUrl($name);

	public function exists($name);

	public function delete($name);

	public function deleteAll();
}
