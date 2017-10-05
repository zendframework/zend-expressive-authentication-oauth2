<?php
// change the permission of private.key to 0600
chmod(__DIR__ . '/Pdo/TestAsset/private.key', 0600);

require __DIR__ . '/../vendor/autoload.php';
