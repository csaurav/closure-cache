# Directus ClosureCache

[![Build Status](https://travis-ci.org/freen/closure-cache.png)](https://travis-ci.org/freen/closure-cache)

Directus `ClosureCache` is a simple way to cache slow runtime operations without much additional code or code re-organization. It implements a Slim-style method of naming and defining cached operations, either on-the-fly or in advance, and a one-line interface for warming pre-defined operations.

`ClosureCache` is a thin wrapper for the [`Zend\Cache`](http://framework.zend.com/manual/2.0/en/modules/zend.cache.storage.adapter.html) module, which interfaces with numerous cache engines. It was built as a component of Directus6.

## Examples

### Initialize
The constructor accepts the same argument accepted by [`Zend\Cache\StorageFactory::factory`](http://framework.zend.com/manual/2.0/en/modules/zend.cache.storage.adapter.html#quick-start): an array of options which identify the cache engine.

#### Memcached configuration
```php
use Directus\ClosureCache\Cache;

$Cache = new Cache(array(
    'adapter' => 'memcached',
    'options' => array(
        'servers' => array(
            array('127.0.0.1', 11211)
        )
    )
));
```

#### Filesystem configuration
```php
use Directus\ClosureCache\Cache;

$Cache = new Cache(array(
    'adapter' => 'filesystem',
    'options' => array(
        'cache_dir' => $cacheDir,
        'dir_level' => 2
    )
));
```

### Cache on-the-fly
Cache slow operations on-the-fly this way:

```php
list($resultA, $resultB) = $Cache->cache('slowOperationsAandB', function() {
	// ... running complex DB queries
	// ... fetching from lazy API
	return array($resultA, $resultB);
});
```

The function will only execute if the cache entry named `slowOperationsAandB` is undefined within cache storage.

### Pre-defined, cached operations
To implement cache-warming and arguments for your cached operations, arrange them in advance using "definitions":

Here is a snippet from a hypothetical `cacheDefinitions.php`:

```php
$Cache->define('siteMetadata', function() {
	// ... run a million database queries
	return $dataSet;
});

$Cache->define('userMetadata', function($userId) {
	// ... run a few database queries which take a $userId parameter
	return $userSpecificDataSet;
});
```

Now you can retrieve/warm these definitions from the rest of your application:

```php
$siteMetadata = $Cache->warm('siteMetadata');

$user1 = $Cache->warm('userMetadata', array(1));
$user2 = $Cache->warm('userMetadata', array(2));
```

As before, the logic contained by the cache definitions will execute only if the corresponding entry (with or without argument modifiers) is undefined within the cache engine.

### Cache expiration

Expire cache entries (defined above) at any time this way:

```php
// Defined on the fly
$Cache->expire('slowOperationsAandB');

// Pre-defined
$Cache->expire('siteMetadata');

// Pre-defined w/ arguments
$Cache->expire('userMetadata', array(1));
$Cache->expire('userMetadata', array(2));
```
