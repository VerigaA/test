<?php

// Подключаем файл с настройками и подключением к базе данных
require_once 'db.php';
require_once 'UserHandler.php';

// Создание обработчика
$userHandler = new UserHandler(db());

// Обработка запроса
$method = $_SERVER['REQUEST_METHOD'];
$path = $_SERVER['REQUEST_URI'];
$params = [];
if (strpos($path, '?') !== false) {
    list($path, $query) = explode('?', $path, 2);
    parse_str($query, $params);
}

$body = json_decode(file_get_contents('php://input'), true);

$response = [
    'success' => false,
    'result' => [],
];

try {
    switch ($method) {
        case 'POST':
            if ($path === '/create') {
                $response = $userHandler->create($body);
            }
            break;

        case 'GET':
            if (preg_match('/^\/get(?:\/(\d+))?$/', $path, $matches)) {
                $userId = $matches[1] ?? null;
                $role = $_GET['role'] ?? null;
                if ($userId) {
                    $response = $userHandler->get($userId);
                } else {
                    $response = $userHandler->get(null, $role);
                }
            }
            break;

        case 'PATCH':
            if (preg_match('/^\/update\/(\d+)$/', $path, $matches)) {
                $userId = $matches[1];
                $response = $userHandler->update($userId, $body);
            }
            break;

        case 'DELETE':
            if (preg_match('/^\/delete\/(\d+)$/', $path, $matches)) {
                $userId = $matches[1];
                $response = $userHandler->delete($userId);
            } else if ($path === '/delete') {
                $response = $userHandler->delete();
            }
            break;
    }
} catch (PDOException $e) {
    $response['result']['error'] = $e->getMessage();
}

// Отправка ответа
header('Content-type: application/json');
echo json_encode($response);