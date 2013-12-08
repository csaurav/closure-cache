<?php

namespace Directus\ClosureCache;

use Directus\ClosureCache\Exception\CacheOperationNameAlreadyDefinedException;
use Directus\ClosureCache\Exception\CacheOperationNameUndefinedException;
use Zend\Cache\StorageFactory;

class Cache
{

    /**
     * @var array
     */
    protected $operations = array();

    /**
     * @var Zend\Cache\Storage\StorageInterface
     */
    protected $storage;

    /**
     * @param Array $options The options array accepted by Zend\Cache\StorageFactory::factory
     * See: http://framework.zend.com/manual/2.0/en/modules/zend.cache.storage.adapter.html#quick-start
     */
    public function __construct(Array $options) {
        $this->storage = StorageFactory::factory($options);
    }

    /**
     * Cache the result of the $callable parameter under the provided name.
     * @param  string $name
     * @param  callable $callable
     * @return mixed
     */
    public function cache($name, $callable) {
        $Operation = new Operation($this->storage, $name, $callable);
        return $Operation->getValue();
    }

    /**
     * Define a cache operation. If the callable accepts arguments, they will be passed via the Cache#warm method.
     * @param  string $name
     * @param  callable $callable
     * @return void
     */
    public function define($name, $callable) {
        if(isset($this->operations[$name])) {
            throw new CacheOperationNameAlreadyDefinedException("Cache operation with name $name already exists.");
        }
        $this->operations[$name] = new Operation($this->storage, $name, $callable);
    }

    /**
     * Expire the cache entry within the equivalent cache key, optionally with arguments.
     * @param  string $name
     * @param  null|array $arguments Must be serializable.
     * @return bool
     */
    public function expire($name, $arguments = null) {
        $cacheKey = self::makeCacheKey($name, $arguments);
        return $this->storage->removeItem($cacheKey);
    }

    /**
     * @return array Array of names of currently defined operations.
     */
    public function getNames() {
        return array_keys($this->operations);
    }

    /**
     * @return Zend\Cache\Storage\StorageInterface
     */
    public function getStorage() {
        return $this->storage;
    }

    /**
     * Remove an operation definition by name.
     * @param  string $name The name of the operation to be removed.
     * @return void
     */
    public function undefine($name) {
        if(!isset($this->operations[$name])) {
            throw new CacheOperationNameUndefinedException("Cache operation with name $name is undefined.");
        }
        unset($this->operations[$name]);
    }

    /**
     * Run a pre-defined cache operation by name. Optionally include arguments if the operation definition accepts
     * them.
     * @param  string $name
     * @param  null|array $arguments Must be serializable.
     * @return mixed The result of the operation, optionally with arguments.
     */
    public function warm($name, $arguments = null) {
        if(!isset($this->operations[$name])) {
            throw new CacheOperationNameUndefinedException("Cache operation with name $name is undefined.");
        }
        return $this->operations[$name]->getValue($arguments);
    }

    /**
     * @todo  how to warn if any parameters are not serializable
     * @param  string $name
     * @param  null|array $arguments Must be serializable.
     * @return mixed
     */
    public static function makeCacheKey($name, $arguments = null) {
        $keyParts = array($name);
        if(is_array($arguments)) {
            $keyParts[] = $arguments;
        }
        return serialize($keyParts);
    }


}