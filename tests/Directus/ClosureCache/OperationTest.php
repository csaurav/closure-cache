<?php

use Directus\ClosureCache\Operation;
use Zend\Cache\StorageFactory;

/**
 * Operation class is almost fully covered by the CacheTest tests.
 */
class OperationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException InvalidArgumentException
     */
    public function testCallableParameterMustBeCallable() {
        $storage = StorageFactory::factory(array('adapter' => 'memory'));
        $Operation = new Operation($storage, 'name', 'nonCallableParameter');
    }
}