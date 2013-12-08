<?php

use Directus\ClosureCache\Cache;

class CacheTest extends \PHPUnit_Framework_TestCase
{

    public function testCacheConstructorCreatesCacheStorage() {
        $Cache = new Cache(array('adapter' => 'memory'));
        $storage = $Cache->getStorage();
        $this->assertContains('Zend\\Cache\\Storage\\StorageInterface', class_implements($storage));
    }

    public function testOnTheFlyCacheYieldsCallableResults() {
        $Cache = new Cache(array('adapter' => 'memory'));
        $onTheFlyValue = $Cache->cache('slowOperation', function() {
            return 'la' . 'la' . 'la';
        });
        $this->assertEquals('lalala', $onTheFlyValue);

        $onTheFlyValue2 = $Cache->cache('slowOperation', function() {
            return "This shouldn't run because the key should be already defined.";
        });
        $this->assertEquals('lalala', $onTheFlyValue2);

        $storage = $Cache->getStorage();
        $this->assertTrue($storage->hasItem(Cache::makeCacheKey('slowOperation')));
    }

    /**
     * @expectedException Directus\ClosureCache\Exception\CacheOperationNameAlreadyDefinedException
     */
    public function testDefineStoresCallableOnce() {
        $Cache = new Cache(array('adapter' => 'memory'));
        $Cache->define('slowOperation', function() {
            return 'ra' . 'ra' . 'ra';
        });
        $Cache->define('slowOperation', function() {
            return 'no' . 'no' . 'no';
        });
    }

    public function testUndefineRemovesOperation() {
        $Cache = new Cache(array('adapter' => 'memory'));
        $Cache->define('slowOperation', function() {
            return 'so' . 'so' . 'so';
        });
        $Cache->undefine('slowOperation');
        $names = $Cache->getNames();
        $this->assertFalse(in_array('slowOperation', $names));
    }

    /**
     * @expectedException Directus\ClosureCache\Exception\CacheOperationNameUndefinedException
     */
    public function testUndefineFailsIfUndefined() {
        $Cache = new Cache(array('adapter' => 'memory'));
        $Cache->undefine('undefinedSlowOperation');
    }

    /**
     * @expectedException Directus\ClosureCache\Exception\CacheOperationNameUndefinedException
     */
    public function testUndefineSucceedsOnlyOnce() {
        $Cache = new Cache(array('adapter' => 'memory'));
        $Cache->cache('slowOperation', function() {
            return 'la' . 'la' . 'la';
        });
        $Cache->undefine('slowOperation');
        $Cache->undefine('slowOperation');
    }

    /**
     * @expectedException Directus\ClosureCache\Exception\CacheOperationNameUndefinedException
     */
    public function testWarmErrorsForNonexistentCacheOperations() {
        $Cache = new Cache(array('adapter' => 'memory'));
        $Cache->define('slowOperation', function() {
            return 'ra' . 'ra' . 'ra';
        });
        $Cache->warm('undefinedSlowOperation');
    }

    public function testWarmAccommodatesMultipleValuesForVaryingArguments() {
        $Cache = new Cache(array('adapter' => 'memory'));
        $Cache->define('slowDynamicOperation', function($ra) {
            return 'la' . $ra . 'la' . $ra;
        });
        $variantYa = $Cache->warm('slowDynamicOperation', array('ya'));
        $variantNa = $Cache->warm('slowDynamicOperation', array('na'));
        $this->assertEquals('layalaya', $variantYa);
        $this->assertEquals('lanalana', $variantNa);
    }

    public function testExpiredOperationMustRunAgain() {
        $runCount = 0;
        $Cache = new Cache(array('adapter' => 'memory'));
        $Cache->define('slowOperation', function() use (&$runCount) {
            $runCount++;
            return 'rara';
        });
        $Cache->warm('slowOperation');
        $Cache->expire('slowOperation');
        $Cache->warm('slowOperation');
        $this->assertEquals(2, $runCount);
    }

    public function testExpireExpiresCacheFromStorageEvenWithoutOperationDefinition() {
        $Cache = new Cache(array('adapter' => 'memory'));
        $Cache->cache('slowOperation', function() {
            return 'value';
        });
        $storage = $Cache->getStorage();
        $this->assertTrue($storage->hasItem(Cache::makeCacheKey('slowOperation')));
        $Cache->expire('slowOperation');
        $this->assertFalse($storage->hasItem(Cache::makeCacheKey('slowOperation')));
    }

    public function testExpireWithCallableArgumentsOnlyExpiresCacheWithSpecificArguments() {
        $executedWithArgumentsCount = array();

        $Cache = new Cache(array('adapter' => 'memory'));
        $Cache->define('slowDynamicOperation', function($arg = null) use (&$executedWithArgumentsCount) {
            $idx = serialize(array($arg));
            if(!isset($executedWithArgumentsCount[$idx])) {
                $executedWithArgumentsCount[$idx] = 0;
            }
            $executedWithArgumentsCount[$idx]++;
            return "dskljhelwih";
        });

        $Cache->warm('slowDynamicOperation', array(null) );
        $Cache->warm('slowDynamicOperation', array('a') );
        $Cache->warm('slowDynamicOperation', array(1) );

        $result = $Cache->expire('slowDynamicOperation', array('a') );

        $this->assertTrue($result);

        $Cache->warm('slowDynamicOperation', array(null) ); // This should come from cache.
        $Cache->warm('slowDynamicOperation', array('a') ); // This should run again.
        $Cache->warm('slowDynamicOperation', array(1) );  // This should come from cache.

        $this->assertEquals(1, $executedWithArgumentsCount[ serialize( array(null) ) ] );
        $this->assertEquals(2, $executedWithArgumentsCount[ serialize( array('a')  ) ] );
        $this->assertEquals(1, $executedWithArgumentsCount[ serialize( array(1)    ) ] );
    }

}