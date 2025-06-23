<?php

// use PDO;
// use PDOException;

return (function () {
    $driver   = 'pgsql';
    $host     = 'localhost';
    $port     = '5432';
    $database = 'AUTOSEED';  // 새로 생성한 데이터베이스
    $username = 'postgres';  // 기본 사용자
    $password = '0217';      // 설정된 비밀번호

    $dsn = "{$driver}:host={$host};port={$port};dbname={$database}";

    try {
        $pdo = new PDO($dsn, $username, $password, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, 
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        return $pdo;
    } catch (PDOException $e) {
        // 예외 처리
        echo "DB Connection failed: " . $e->getMessage();
        exit;
    }
})();
