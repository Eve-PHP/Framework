<?php 
require_once __DIR__ . '/vendor/autoload.php';

// we need to manually set this
$_SERVER['HTTP_HOST']   = null;
// we need to manually set this
$_SERVER['REQUEST_URI'] = null;

// we're going to setup virtual server so
// jobs and models will not loose it's original scope
Eve\Framework\Index::i(__DIR__, 'Eve')
// set default paths
->defaultPaths()
// set default database
->defaultDatabases();

use Eve\Framework\Qdispatch;

Qdispatch::i('localhost', 5672, 'guest', 'guest')->run();

?>