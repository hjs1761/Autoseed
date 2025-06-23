# 인플루언서 솔루션 - 마이그레이션 시스템

이 디렉토리는 인플루언서 솔루션의 데이터베이스 스키마를 관리하는 마이그레이션 파일들을 포함합니다.

## 마이그레이션이란?

마이그레이션은 데이터베이스 스키마를 버전 관리하고 변경 사항을 추적하는 시스템입니다. 이를 통해:

1. 팀원 간 데이터베이스 구조를 일관되게 유지할 수 있습니다.
2. 개발, 테스트, 프로덕션 환경 간의 일관성을 유지할 수 있습니다.
3. 데이터베이스 변경 이력을 추적하고 필요 시 이전 버전으로 롤백할 수 있습니다.

## 마이그레이션 파일 구조

마이그레이션 파일은 다음과 같은 명명 규칙을 따릅니다:

```
YYYY_MM_DD_XXXXXX_설명.php
```

예: `2023_01_01_000001_create_users_table.php`

각 파일은 다음과 같은 구조를 가집니다:

```php
<?php
class CreateUsersTable {
    // 마이그레이션 실행 (테이블 생성, 수정 등)
    public function up(PDO $db) {
        // SQL 쿼리 실행
        return true; // 성공 시 true 반환
    }
    
    // 마이그레이션 롤백 (변경 사항 되돌리기)
    public function down(PDO $db) {
        // SQL 쿼리 실행
        return true; // 성공 시 true 반환
    }
}
```

## 마이그레이션 명령어

마이그레이션 관리를 위한 명령어는 다음과 같습니다:

### 마이그레이션 실행
모든 미실행 마이그레이션을 실행합니다.

```bash
php migrations/migrate.php
```

### 특정 환경에서 실행
특정 환경(development, production 등)에서 마이그레이션을 실행합니다.

```bash
php migrations/migrate.php --env=production
```

### 마이그레이션 상태 확인
실행된 마이그레이션과 대기 중인 마이그레이션을 확인합니다.

```bash
php migrations/migrate.php --status
```

### 마이그레이션 롤백
마지막 배치의 마이그레이션을 롤백합니다.

```bash
php migrations/migrate.php --rollback
```

### 특정 배치까지 롤백
특정 배치 번호까지의 마이그레이션을 롤백합니다.

```bash
php migrations/migrate.php --rollback=3
```

### 새 마이그레이션 파일 생성
새 마이그레이션 파일을 생성합니다.

```bash
php migrations/migrate.php --create=테이블명
```

## 마이그레이션 작성 가이드라인

새 마이그레이션을 작성할 때 다음 가이드라인을 따라주세요:

1. **하나의 마이그레이션은 하나의 변경사항만 포함해야 합니다**
   - 좋은 예: `create_users_table`, `add_verified_to_users`
   - 나쁜 예: `update_multiple_tables`

2. **마이그레이션은 항상 롤백 가능해야 합니다**
   - `up()` 메서드에서 테이블을 생성했다면, `down()` 메서드에서는 해당 테이블을 삭제해야 합니다.
   - 컬럼을 추가했다면, 롤백에서는 해당 컬럼을 제거해야 합니다.

3. **마이그레이션은 독립적이어야 합니다**
   - 다른 마이그레이션에 의존성이 있는 경우, 주석으로 명시해주세요.

4. **중요한 데이터 변경 시 백업을 고려하세요**
   - 컬럼 삭제나 테이블 드롭 등 데이터 손실이 발생할 수 있는 경우, 백업 테이블을 만드는 것이 좋습니다.

## 자주 사용하는 마이그레이션 예시

### 테이블 생성
```php
$sql = "
    CREATE TABLE IF NOT EXISTS `users` (
        `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        `name` VARCHAR(100) NOT NULL,
        `email` VARCHAR(255) NOT NULL,
        `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY `email` (`email`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
";
```

### 컬럼 추가
```php
$sql = "
    ALTER TABLE `users` 
    ADD COLUMN `is_active` TINYINT(1) DEFAULT 1 AFTER `email`;
";
```

### 컬럼 수정
```php
$sql = "
    ALTER TABLE `users` 
    MODIFY COLUMN `name` VARCHAR(150) NOT NULL;
";
```

### 컬럼 삭제
```php
$sql = "
    ALTER TABLE `users` 
    DROP COLUMN `is_active`;
";
```

### 인덱스 추가
```php
$sql = "
    ALTER TABLE `users` 
    ADD INDEX `idx_name` (`name`);
";
```

### 인덱스 삭제
```php
$sql = "
    ALTER TABLE `users` 
    DROP INDEX `idx_name`;
";
``` 