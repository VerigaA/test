<?php

require_once 'db.php';
require_once 'UserHandler.php';

// URL микросервиса
$baseUrl = 'http://rulyou.tursoft.ru';

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
 * Тестирование API.
 */
function testApi(): void
{
    global $userHandler, $baseUrl, $testData;

    // Тестирование функции create
    echo "Тестирование create:\r\n<br/>";
    foreach ($testData as $data) {
        $response = $userHandler->sendRequest('POST', $baseUrl . '/create', $data);
        if ($response['http_code'] == 200 && $response['response']['success']) {
            echo "  Создан пользователь с ID: {$response['response']['result']['id']}\r\n<br/>";
        } else {
            echo "  Ошибка создания пользователя: {$response['http_code']} - {$response['response']['result']['error']}\r\n<br/>";
        }
    }

    // Тестирование функции get
    echo "\r\n<br/>Тестирование get:\r\n<br/>";
    // Получение всех пользователей
    $response = $userHandler->sendRequest('GET', $baseUrl . '/get');
    if ($response['http_code'] == 200 && $response['response']['success']) {
        echo "  Получены все пользователи:\r\n<br/>";
        foreach ($response['response']['result']['users'] as $user) {
            echo "    {$user['full_name']} ({$user['role']})\r\n<br/>";
        }
    } else {
        echo "  Ошибка получения пользователей: {$response['http_code']} - {$response['response']['result']['error']}\r\n<br/>";
    }

    // Получение пользователя по ID
    $userId = 1; // ID созданного ранее пользователя
    $response = $userHandler->sendRequest('GET', $baseUrl . "/get/{$userId}");
    if ($response['http_code'] == 200 && $response['response']['success']) {
        echo "  Получен пользователь с ID {$userId}:\r\n<br/>";
        echo "    {$response['response']['result']['users'][0]['full_name']} ({$response['response']['result']['users'][0]['role']})\r\n<br/>";
    } else {
        echo "  Ошибка получения пользователя: {$response['http_code']} - {$response['response']['result']['error']}\r\n<br/>";
    }

    // Получение пользователей по роли
    $role = 'Developer';
    $response = $userHandler->sendRequest('GET', $baseUrl . "/get?role={$role}");
    if ($response['http_code'] == 200 && $response['response']['success']) {
        echo "  Получены пользователи с ролью '{$role}':\r\n<br/>";
        foreach ($response['response']['result']['users'] as $user) {
            echo "    {$user['full_name']} ({$user['role']})\r\n<br/>";
        }
    } else {
        echo "  Ошибка получения пользователей: {$response['http_code']} - {$response['response']['result']['error']}\r\n<br/>";
    }

    // Тестирование функции update
    echo "\r\n<br/>Тестирование update:\r\n<br/>";
    $userId = 1;
    $updateData = [
        'full_name' => 'John Smith',
        'role' => 'Senior Developer'
    ];
    $response = $userHandler->sendRequest('PATCH', $baseUrl . "/update/{$userId}", $updateData);
    if ($response['http_code'] == 200 && $response['response']['success']) {
        echo "  Обновлен пользователь с ID {$userId}:\r\n<br/>";
        echo "    {$response['response']['result']['full_name']} ({$response['response']['result']['role']})\r\n<br/>";
    } else {
        echo "  Ошибка обновления пользователя: {$response['http_code']} - {$response['response']['result']['error']}\r\n<br/>";
    }

    // Тестирование функции delete
    echo "\r\n<br/>Тестирование delete:\r\n<br/>";
    // Удаление пользователя по ID
    $userId = 1;
    $response = $userHandler->sendRequest('DELETE', $baseUrl . "/delete/{$userId}");
    if ($response['http_code'] == 200 && $response['response']['success']) {
        echo "  Удален пользователь с ID {$userId}\r\n<br/>";
    } else {
        echo "  Ошибка удаления пользователя: {$response['http_code']} - {$response['response']['result']['error']}\r\n<br/>";
    }

    // Удаление всех пользователей
    $response = $userHandler->sendRequest('DELETE', $baseUrl . "/delete");
    if ($response['http_code'] == 200 && $response['response']['success']) {
        echo "  Удалены все пользователи\r\n<br/>";
    } else {
        echo "  Ошибка удаления пользователей: {$response['http_code']} - {$response['response']['result']['error']}\r\n<br/>";
    }

    // Тестирование get после удаления всех пользователей
    $response = $userHandler->sendRequest('GET', $baseUrl . '/get');
    if ($response['http_code'] == 200 && $response['response']['success'] && empty($response['response']['result']['users'])) {
        echo "  Проверка: список пользователей пуст после удаления\r\n<br/>";
    } else {
        echo "  Ошибка: список пользователей не пуст после удаления\r\n<br/>";
    }

    // Тестирование get пользователя с несуществующим ID
    $userId = 999; 
    $response = $userHandler->sendRequest('GET', $baseUrl . "/get/{$userId}");
    if ($response['http_code'] == 200 && !$response['response']['success'] && isset($response['response']['result']['error'])) {
        echo "  Проверка: получено сообщение об ошибке при получении несуществующего пользователя\r\n<br/>";
        echo "    Сообщение: {$response['response']['result']['error']}\r\n<br/>";
    } else {
        echo "  Ошибка: не получено сообщение об ошибке при получении несуществующего пользователя\r\n<br/>";
    }

    // Тестирование update несуществующего пользователя
    $userId = 999;
    $updateData = [
        'full_name' => 'John Smith',
        'role' => 'Senior Developer'
    ];
    $response = $userHandler->sendRequest('PATCH', $baseUrl . "/update/{$userId}", $updateData);
    if ($response['http_code'] == 200 && !$response['response']['success'] && isset($response['response']['result']['error'])) {
        echo "  Проверка: получено сообщение об ошибке при обновлении несуществующего пользователя\r\n<br/>";
        echo "    Сообщение: {$response['response']['result']['error']}\r\n<br/>";
    } else {
        echo "  Ошибка: не получено сообщение об ошибке при обновлении несуществующего пользователя\r\n<br/>";
    }

    // Тестирование delete несуществующего пользователя
    $userId = 999; 
    $response = $userHandler->sendRequest('DELETE', $baseUrl . "/delete/{$userId}");
    if ($response['http_code'] == 200 && !$response['response']['success'] && isset($response['response']['result']['error'])) {
        echo "  Проверка: получено сообщение об ошибке при удалении несуществующего пользователя\r\n<br/>";
        echo "    Сообщение: {$response['response']['result']['error']}\r\n<br/>";
    } else {
        echo "  Ошибка: не получено сообщение об ошибке при удалении несуществующего пользователя\r\n<br/>";
    }
}

testApi();