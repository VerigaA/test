<?php

/**
 * Класс для обработки запросов к API.
 */
class UserHandler
{
    /**
     * Объект PDO для подключения к базе данных.
     *
     * @var PDO
     */
    private $pdo;

    /**
     * Конструктор класса.
     *
     * @param PDO $pdo Объект PDO для подключения к базе данных.
     */
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Создание пользователя.
     *
     * @param array $data Данные нового пользователя.
     *
     * @return array Ответ с результатом операции.
     */
    public function create(array $data): array
    {
        $response = [
            'success' => false,
            'result' => [],
        ];

        try {
            $stmt = $this->pdo->prepare("INSERT INTO users (full_name, role, efficiency) VALUES (:full_name, :role, :efficiency)");
            $stmt->execute([
                ':full_name' => $data['full_name'],
                ':role' => $data['role'],
                ':efficiency' => $data['efficiency'],
            ]);
            $response['success'] = true;
            $response['result'] = [
                'id' => $this->pdo->lastInsertId(),
            ];
        } catch (PDOException $e) {
            $response['result']['error'] = $e->getMessage();
        }

        return $response;
    }

    /**
     * Получение пользователя.
     *
     * @param int|null $userId ID пользователя.
     * @param string|null $role Роль пользователя.
     *
     * @return array Ответ с результатом операции.
     */
    public function get(int $userId = null, string $role = null): array
    {
        $response = [
            'success' => false,
            'result' => [],
        ];

        try {
            if ($userId) {
                $stmt = $this->pdo->prepare("SELECT * FROM users WHERE id = :id");
                $stmt->execute([':id' => $userId]);
                $user = $stmt->fetch();
                if ($user) {
                    $response['success'] = true;
                    $response['result']['users'] = [$user];
                } else {
                    $response['result']['error'] = 'User not found';
                }
            } else {
                if ($role) {
                    $stmt = $this->pdo->prepare("SELECT * FROM users WHERE role = :role");
                    $stmt->execute([':role' => $role]);
                } else {
                    $stmt = $this->pdo->prepare("SELECT * FROM users");
                    $stmt->execute();
                }
                $response['success'] = true;
                $response['result']['users'] = $stmt->fetchAll();
            }
        } catch (PDOException $e) {
            $response['result']['error'] = $e->getMessage();
        }

        return $response;
    }

    /**
     * Обновление пользователя.
     *
     * @param int $userId ID пользователя.
     * @param array $data Новые данные пользователя.
     *
     * @return array Ответ с результатом операции.
     */
    public function update(int $userId, array $data): array
    {
        $response = [
            'success' => false,
            'result' => [],
        ];

        try {
            $stmt = $this->pdo->prepare("UPDATE users SET full_name = :full_name, role = :role WHERE id = :id");
            $stmt->execute([
                ':full_name' => $data['full_name'],
                ':role' => $data['role'],
                ':id' => $userId,
            ]);
            $stmt = $this->pdo->prepare("SELECT * FROM users WHERE id = :id");
            $stmt->execute([':id' => $userId]);
            $user = $stmt->fetch();
            if ($user) {
                $response['success'] = true;
                $response['result'] = $user;
            } else {
                $response['result']['error'] = 'User not found';
            }
        } catch (PDOException $e) {
            $response['result']['error'] = $e->getMessage();
        }

        return $response;
    }

    /**
     * Удаление пользователя.
     *
     * @param int|null $userId ID пользователя.
     *
     * @return array Ответ с результатом операции.
     */
    public function delete(int $userId = null): array
    {
        $response = [
            'success' => false,
            'result' => [],
        ];

        try {
            if ($userId) {
                // Проверяем, существует ли пользователь
                $stmt = $this->pdo->prepare("SELECT * FROM users WHERE id = :id");
                $stmt->execute([':id' => $userId]);
                if (!$stmt->fetch()) {
                    $response['result']['error'] = 'User not found';
                    return $response;
                }

                $stmt = $this->pdo->prepare("DELETE FROM users WHERE id = :id");
                $stmt->execute([':id' => $userId]);
                $response['success'] = true;
            } else {
                $stmt = $this->pdo->prepare("TRUNCATE TABLE users");
                $stmt->execute();
                $response['success'] = true;
            }
        } catch (PDOException $e) {
            $response['result']['error'] = $e->getMessage();
        }

        return $response;
    }

    /**
     * Отправка HTTP-запроса.
     *
     * @param string $method Метод HTTP-запроса.
     * @param string $url URL запроса.
     * @param array $data Данные запроса.
     * @param array $headers Заголовки запроса.
     *
     * @return array Ответ с результатом запроса.
     */
    public function sendRequest(string $method, string $url, array $data = [], array $headers = []): array
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

        if (!empty($data)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            $headers[] = 'Content-Type: application/json';
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return [
            'http_code' => $httpCode,
            'response' => json_decode($response, true)
        ];
    }
}