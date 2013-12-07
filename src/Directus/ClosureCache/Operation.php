<?php

namespace Directus\ClosureCache;

use Zend\Cache\Storage\StorageInterface;

class Operation
{
    protected $storage;
    protected $name;
    protected $callable;

    public function __construct(StorageInterface $storage, $name, $callable) {
        if(!is_callable($callable)) {
            throw new \InvalidArgumentException("Parameter must be callable.");
        }
        $this->storage = $storage;
        $this->name = $name;
        $this->callable = $callable;
    }

    public function expire($arguments = null) {
        $cacheKey = $this->makeCacheKey($this->name, $arguments);
        return $this->storage->removeItem($cacheKey);
    }

    public function getValue($arguments = null) {
        $cacheKey = $this->makeCacheKey($this->name, $arguments);
        if(!$this->storage->hasItem($cacheKey)) {
            $this->warm($arguments);
        }
        return $this->storage->getItem($cacheKey);
    }

    public function warm($arguments = null) {
        $callable = $this->callable;
        if(is_array($arguments)) {
            $value = call_user_func_array($callable, $arguments);
        } else {
            $value = $callable();
        }
        $cacheKey = $this->makeCacheKey($this->name, $arguments);
        $this->storage->addItem($cacheKey, $value);
    }

    /**
     * @todo  how to warn if any parameters are not serializable
     */
    protected function makeCacheKey($name, $arguments = null) {
        $keyParts = array($name);
        if(is_array($arguments)) {
            $keyParts[] = $arguments;
        }
        return serialize($keyParts);
    }

}