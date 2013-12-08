<?php

namespace Directus\ClosureCache;

use Zend\Cache\Storage\StorageInterface;

/**
 * This class is used internally by Directus\ClosureCache\Cache. A single cacheable operation.
 */
class Operation
{

    /**
     * @var Zend\Cache\Storage\StorageInterface
     */
    protected $storage;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var callable
     */
    protected $callable;

    /**
     * @param StorageInterface $storage
     * @param string           $name
     * @param callable         $callable
     */
    public function __construct(StorageInterface $storage, $name, $callable) {
        if(!is_callable($callable)) {
            throw new \InvalidArgumentException("Parameter must be callable.");
        }
        $this->storage = $storage;
        $this->name = $name;
        $this->callable = $callable;
    }

    /**
     * @param  null|array $arguments Must be serializable.
     * @return bool
     */
    public function expire($arguments = null) {
        $cacheKey = $this->makeCacheKey($this->name, $arguments);
        return $this->storage->removeItem($cacheKey);
    }

    /**
     * @param  null|array $arguments Must be serializable.
     * @return mixed
     */
    public function getValue($arguments = null) {
        $cacheKey = $this->makeCacheKey($this->name, $arguments);
        if(!$this->storage->hasItem($cacheKey)) {
            $this->warm($arguments);
        }
        return $this->storage->getItem($cacheKey);
    }

    /**
     * @param  null|array $arguments Must be serializable.
     * @return void
     */
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
     * @param  string $name
     * @param  null|array $arguments Must be serializable.
     * @return mixed
     */
    protected function makeCacheKey($name, $arguments = null) {
        $keyParts = array($name);
        if(is_array($arguments)) {
            $keyParts[] = $arguments;
        }
        return serialize($keyParts);
    }

}