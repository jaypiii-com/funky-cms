<?php
# JayPiii 2025 - FunkyCMS

require __DIR__ . '/../vendor/autoload.php';

use FunkyCMS\Core\CollectionManager;
use FunkyCMS\Core\ViewHelper;

$users = new CollectionManager(collection: 'users');
$users2 = new CollectionManager(collection: 'users');

// $user = $users->all();
$user = $users
    ->find(key: 'gender', value: 'weiblich', mode: ['exact'])
    ->sort(keys: ['age'])
    ->all();

$user2 = $users2
    ->find(key: 'gender', value: 'männlich', mode: ['exact'])
    ->sort(keys: ['age' => 'desc'])
    ->all();


// $user2 = $users2
//     ->find(key: 'age', value: 0, mode: ['check', '>='])
//     ->sort(keys: ['age' => 'desc'])
//     ->all();




if ($user) {
    echo "<h2>jünger als 42:</h2>";
    echo ViewHelper::arrayToTable($user, ['name','agse','is_cool','gender','occupation']);

    echo "<h2>älter als 42:</h2>";
    echo ViewHelper::arrayToTable($user2, ['id:Nummer','name','age','is_cool','gender','occupation']);
} else {
    echo "User not found: ";
}
