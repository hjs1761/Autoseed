<?php

require_once 'config/database.php';

try {
    $pdo = require 'config/database.php';
    echo "PostgreSQL 데이터베이스 연결 성공!\n";
    
    // PostgreSQL 버전 확인
    $version = $pdo->query('SELECT version()')->fetchColumn();
    echo "PostgreSQL 버전: " . $version . "\n";
    
} catch (PDOException $e) {
    echo "연결 실패: " . $e->getMessage() . "\n";
} 