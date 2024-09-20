<?php

/**
 * Настройка и подключение к базе данных.
 *
 * @return PDO Объект PDO для подключения к базе данных.
 */
function db(): PDO
{
	// Подключение к базе данных
	$dsn = 'mysql:host=185.177.216.77;dbname=MMrhxThy';
	$username = 'USBGPz';
	$password = 'fLvnYLFcsJHqlNsI';

    try {
        $pdo = new PDO($dsn, $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        die("Ошибка подключения к базе данных: " . $e->getMessage());
    }

    // Возвращаем объект PDO для использования в других файлах
    return $pdo;
}