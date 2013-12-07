<?php

namespace Directus\ClosureCache;

use Directus\ClosureCache\Exception\CacheOperationNameAlreadyDefinedException;
use Directus\ClosureCache\Exception\CacheOperationNameUndefinedException;
use Zend\Cache\StorageFactory;

class Cache
{

	protected $operations = array();

	protected $storage;

	public function __construct(Array $options) {
		$this->storage = StorageFactory::factory($options);
	}

	public function cache($name, $callable) {
		$Operation = new Operation($this->storage, $name, $callable);
		return $Operation->getValue();
	}

	public function define($name, $callable) {
		if(isset($this->operations[$name])) {
			throw new CacheOperationNameAlreadyDefinedException("Cache operation with name $name already exists.");
		}
		$this->operations[$name] = new Operation($this->storage, $name, $callable);
	}

	public function expire($name, $arguments = null) {
		if(!isset($this->operations[$name])) {
			throw new CacheOperationNameUndefinedException("Cache operation with name $name is undefined.");
		}
		return $this->operations[$name]->expire($arguments);
	}

	public function getNames() {
		return array_keys($this->operations);
	}

	public function getStorage() {
		return $this->storage;
	}

	public function undefine($name) {
		if(!isset($this->operations[$name])) {
			throw new CacheOperationNameUndefinedException("Cache operation with name $name is undefined.");
		}
		unset($this->operations[$name]);
	}

	public function warm($name, $arguments = null) {
		if(!isset($this->operations[$name])) {
			throw new CacheOperationNameUndefinedException("Cache operation with name $name is undefined.");
		}
		return $this->operations[$name]->getValue($arguments);
	}

}