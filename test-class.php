<?php

error_reporting(E_ALL);
ini_set('display_errors', 'On');

require_once 'UserHandler.php';
require_once 'db.php';

// Тестовые данные
$testData = [
    [
        'full_name' => 'John Doe',
        'role' => 'Developer',
        'efficiency' => 85
    ],
    [
        'full_name' => 'Jane Smith',
        'role' => 'Designer',
        'efficiency' => 92
    ],
];

// Создание обработчика
$userHandler = new UserHandler(db());

/**
 * Тестирование класса UserHandler.
 */
function testClass(): void
{
    global $userHandler, $testData;

    // Тестирование функции create
    echo "Тестирование create:\r\n<br/>";
    foreach ($testData as $data) {
        $response = $userHandler->create($data);
        if ($response['success']) {
            echo "  Создан пользователь с ID: {$response['result']['id']}\r\n<br/>";
        } else {
            echo "  Ошибка создания пользователя: {$response['result']['error']}\r\n<br/>";
        }
    }

    // Тестирование функции get
    echo "\r\n<br/>Тестирование get:\r\n<br/>";
    // Получение всех пользователей
    $response = $userHandler->get();
    if ($response['success']) {
        echo "  Получены все пользователи:\r\n<br/>";
        foreach ($response['result']['users'] as $user) {
            echo "    {$user['full_name']} ({$user['role']})\r\n<br/>";
        }
    } else {
        echo "  Ошибка получения пользователей: {$response['result']['error']}\r\n<br/>";
    }

    // Получение пользователя по ID
    $userId = 1; // ID созданного ранее пользователя
    $response = $userHandler->get($userId);
    if ($response['success']) {
        echo "  Получен пользователь с ID {$userId}:\r\n<br/>";
        echo "    {$response['result']['users'][0]['full_name']} ({$response['result']['users'][0]['role']})\r\n<br/>";
    } else {
        echo "  Ошибка получения пользователя: {$response['result']['error']}\r\n<br/>";
    }

    // Получение пользователей по роли
    $role = 'Developer';
    $response = $userHandler->get(null, $role);
    if ($response['success']) {
        echo "  Получены пользователи с ролью '{$role}':\r\n<br/>";
        foreach ($response['result']['users'] as $user) {
            echo "    {$user['full_name']} ({$user['role']})\r\n<br/>";
        }
    } else {
        echo "  Ошибка получения пользователей: {$response['result']['error']}\r\n<br/>";
    }

    // Тестирование функции update
    echo "\r\n<br/>Тестирование update:\r\n<br/>";
    $userId = 1;
    $updateData = [
        'full_name' => 'John Smith',
        'role' => 'Senior Developer'
    ];
    $response = $userHandler->update($userId, $updateData);
    if ($response['success']) {
        echo "  Обновлен пользователь с ID {$userId}:\r\n<br/>";
        echo "    {$response['result']['full_name']} ({$response['result']['role']})\r\n<br/>";
    } else {
        echo "  Ошибка обновления пользователя: {$response['result']['error']}\r\n<br/>";
    }

    // Тестирование функции delete
    echo "\r\n<br/>Тестирование delete:\r\n<br/>";
    // Удаление пользователя по ID
    $userId = 1;
    $response = $userHandler->delete($userId);
    if ($response['success']) {
        echo "  Удален пользователь с ID {$userId}\r\n<br/>";
    } else {
        echo "  Ошибка удаления пользователя: {$response['result']['error']}\r\n<br/>";
    }

    // Удаление всех пользователей
    $response = $userHandler->delete();
    if ($response['success']) {
        echo "  Удалены все пользователи\r\n<br/>";
    } else {
        echo "  Ошибка удаления пользователей: {$response['result']['error']}\r\n<br/>";
    }

    // Тестирование get после удаления всех пользователей
    $response = $userHandler->get();
    if ($response['success'] && empty($response['result']['users'])) {
        echo "  Проверка: список пользователей пуст после удаления\r\n<br/>";
    } else {
        echo "  Ошибка: список пользователей не пуст после удаления\r\n<br/>";
    }

    // Тестирование get пользователя с несуществующим ID
    $userId = 999;
    $response = $userHandler->get($userId);
    if (!$response['success'] && isset($response['result']['error'])) {
        echo "  Проверка: получено сообщение об ошибке при получении несуществующего пользователя\r\n<br/>";
        echo "    Сообщение: {$response['result']['error']}\r\n<br/>";
    } else {
        echo "  Ошибка: не получено сообщение об ошибке при получении несуществующего пользователя\r\n<br/>";
    }

    // Тестирование update несуществующего пользователя
    $userId = 999;
    $updateData = [
        'full_name' => 'John Smith',
        'role' => 'Senior Developer'
    ];
    $response = $userHandler->update($userId, $updateData);
    if (!$response['success'] && isset($response['result']['error'])) {
        echo "  Проверка: получено сообщение об ошибке при обновлении несуществующего пользователя\r\n<br/>";
        echo "    Сообщение: {$response['result']['error']}\r\n<br/>";
    } else {
        echo "  Ошибка: не получено сообщение об ошибке при обновлении несуществующего пользователя\r\n<br/>";
    }

    // Тестирование delete несуществующего пользователя
    $userId = 999;
    $response = $userHandler->delete($userId);
    if (!$response['success'] && isset($response['result']['error'])) {
        echo "  Проверка: получено сообщение об ошибке при удалении несуществующего пользователя\r\n<br/>";
        echo "    Сообщение: {$response['result']['error']}\r\n<br/>";
    } else {
        echo "  Ошибка: не получено сообщение об ошибке при удалении несуществующего пользователя\r\n<br/>";
    }
}

testClass();