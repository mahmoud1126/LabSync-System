<?php


require_once __DIR__ . '/../vendor/autoload.php';


function injectMockPdoIntoDatabase(PDO $mockPdo): void
{
    require_once __DIR__ . '/../config/Database.php';

    $ref = new ReflectionClass(Database::class);
    $instanceProp = $ref->getProperty('instance');
    $instanceProp->setAccessible(true);
    $instanceProp->setValue(null, null);
    $fakeDb = $ref->newInstanceWithoutConstructor();

    $pdoProp = $ref->getProperty('pdo');
    $pdoProp->setAccessible(true);
    $pdoProp->setValue($fakeDb, $mockPdo);

    $instanceProp->setValue(null, $fakeDb);
}
