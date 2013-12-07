# Directus ClosureCache

Directus `ClosureCache` is a simple way to cache slow runtime operations without much additional code or code re-organization. It implements a Slim-style method of naming and defining cached operations, either on-the-fly or in advance, and a one-line interface for warming pre-defined operations.

`ClosureCache` is a thin wrapper for the `Zend\Cache` module, which interfaces with numerous cache engines. It was built as a component of Directus6.