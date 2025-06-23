<?php
/**
 * 인플루언서 솔루션 마이그레이션 관리 도구
 * 
 * 이 파일은 데이터베이스 마이그레이션을 관리하는 명령행 도구입니다.
 * 
 * 사용법:
 * - 마이그레이션 실행: php migrations/migrate.php
 * - 특정 환경에서 실행: php migrations/migrate.php --env=production
 * - 롤백 실행: php migrations/migrate.php --rollback
 * - 특정 배치까지 롤백: php migrations/migrate.php --rollback=3
 * - 마이그레이션 상태 확인: php migrations/migrate.php --status
 * - 마이그레이션 파일 생성: php migrations/migrate.php --create=테이블명
 * - 도움말 표시: php migrations/migrate.php --help
 * 
 * 마이그레이션 파일 작성 규칙:
 * 1. 파일명은 'YYYY_MM_DD_XXXXXX_설명.php' 형식으로 작성 (예: 2023_01_01_000001_create_users_table.php)
 * 2. 클래스명은 파일명의 스네이크 케이스를 파스칼 케이스로 변경 (예: CreateUsersTable)
 * 3. up()과 down() 메서드를 반드시 구현해야 함
 * 4. 메서드는 성공 시 true, 실패 시 false 반환
 */

// Composer 오토로더 로드
require_once __DIR__ . '/../vendor/autoload.php';

// 명령행 인자 파싱
$options = getopt('', ['env::', 'rollback::', 'status', 'create::', 'help']);

// 도움말 표시
if (isset($options['help'])) {
    echo "인플루언서 솔루션 마이그레이션 관리 도구\n\n";
    echo "사용법:\n";
    echo "  php migrations/migrate.php [옵션]\n\n";
    echo "옵션:\n";
    echo "  --env=환경명        실행 환경 지정 (development, production 등)\n";
    echo "  --rollback[=배치]   마이그레이션 롤백 (배치 번호 지정 가능)\n";
    echo "  --status            마이그레이션 상태 확인\n";
    echo "  --create=테이블명   새 마이그레이션 파일 생성\n";
    echo "  --help              이 도움말 표시\n";
    exit(0);
}

// 환경 설정
$env = $options['env'] ?? 'development';
echo "환경: {$env}\n";

// .env 파일 로드
try {
    $dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
    $dotenv->load();
} catch (\Exception $e) {
    echo "경고: .env 파일을 로드할 수 없습니다. 기본값을 사용합니다.\n";
}

/**
 * 데이터베이스 연결 생성
 * 
 * @param string $withDb 데이터베이스 이름 지정 (빈 문자열이면 DB 선택 없음)
 * @return PDO 데이터베이스 연결 객체
 */
function createDbConnection($withDb = '') {
    $host = $_ENV['DB_HOST'] ?? 'localhost';
    $port = $_ENV['DB_PORT'] ?? '3306';
    $user = $_ENV['DB_USERNAME'] ?? 'root';
    $pass = $_ENV['DB_PASSWORD'] ?? '';
    $charset = 'utf8mb4';
    
    $dsn = "mysql:host={$host};port={$port};charset={$charset}";
    if (!empty($withDb)) {
        $dsn .= ";dbname={$withDb}";
    }
    
    return new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
}

/**
 * 마이그레이션 이력 테이블 생성
 * 
 * @param PDO $db 데이터베이스 연결 객체
 * @return void
 */
function createMigrationTable($db) {
    $sql = "
        CREATE TABLE IF NOT EXISTS `migrations` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `migration` VARCHAR(255) NOT NULL,
            `batch` INT NOT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    
    $db->exec($sql);
    echo "마이그레이션 이력 테이블을 확인했습니다.\n";
}

/**
 * 마이그레이션 상태 확인
 * 
 * @param PDO $db 데이터베이스 연결 객체
 * @return void
 */
function checkStatus($db) {
    // 마이그레이션 이력 테이블 확인
    createMigrationTable($db);
    
    // 모든 마이그레이션 파일 가져오기
    $migrationFiles = glob(__DIR__ . '/*.php');
    $migrationFiles = array_filter($migrationFiles, function($file) {
        return basename($file) !== 'migrate.php';
    });
    sort($migrationFiles);
    
    // 이미 실행된 마이그레이션 가져오기
    $sql = "SELECT migration, batch FROM migrations ORDER BY id";
    $stmt = $db->query($sql);
    $ranMigrations = [];
    
    while ($row = $stmt->fetch()) {
        $ranMigrations[$row['migration']] = $row['batch'];
    }
    
    // 상태 표시
    echo "\n마이그레이션 상태:\n";
    echo str_repeat('-', 80) . "\n";
    echo sprintf("%-50s %-15s %s\n", "마이그레이션", "배치", "상태");
    echo str_repeat('-', 80) . "\n";
    
    foreach ($migrationFiles as $file) {
        $migration = basename($file, '.php');
        $status = isset($ranMigrations[$migration]) ? "실행됨" : "대기중";
        $batch = isset($ranMigrations[$migration]) ? $ranMigrations[$migration] : "-";
        
        echo sprintf("%-50s %-15s %s\n", $migration, $batch, $status);
    }
    
    echo str_repeat('-', 80) . "\n\n";
}

/**
 * 마이그레이션 파일 생성
 * 
 * @param string $name 테이블 또는 작업 이름
 * @return void
 */
function createMigration($name) {
    // 파일명 생성
    $date = date('Y_m_d');
    $time = date('His');
    $filename = "{$date}_{$time}_{$name}.php";
    $path = __DIR__ . "/{$filename}";
    
    // 클래스명 생성
    $className = str_replace(' ', '', ucwords(str_replace('_', ' ', $name)));
    
    // 템플릿 생성
    $template = <<<PHP
<?php
/**
 * {$className} 마이그레이션
 * 
 * 이 파일은 {$name}에 대한 마이그레이션입니다.
 * 여기에 마이그레이션에 대한 설명을 작성하세요.
 * 
 * 작성일: {$date}
 */

class {$className} {
    /**
     * 마이그레이션 실행
     * 
     * @param PDO \$db 데이터베이스 연결 객체
     * @return bool 성공 여부
     */
    public function up(PDO \$db) {
        \$sql = "
            -- 여기에 SQL 쿼리 작성
        ";
        
        \$db->exec(\$sql);
        return true;
    }
    
    /**
     * 마이그레이션 롤백
     * 
     * @param PDO \$db 데이터베이스 연결 객체
     * @return bool 성공 여부
     */
    public function down(PDO \$db) {
        \$sql = "
            -- 여기에 롤백 SQL 쿼리 작성
        ";
        
        \$db->exec(\$sql);
        return true;
    }
}
PHP;
    
    // 파일 저장
    file_put_contents($path, $template);
    echo "마이그레이션 파일이 생성되었습니다: {$filename}\n";
}

/**
 * 마이그레이션 실행
 * 
 * @param PDO $db 데이터베이스 연결 객체
 * @return void
 */
function runMigrations($db) {
    // 마이그레이션 이력 테이블 확인
    createMigrationTable($db);
    
    // 이미 실행된 마이그레이션 가져오기
    $sql = "SELECT migration FROM migrations";
    $stmt = $db->query($sql);
    $ranMigrations = [];
    
    while ($row = $stmt->fetch()) {
        $ranMigrations[] = $row['migration'];
    }
    
    // 마지막 배치 번호 가져오기
    $sql = "SELECT MAX(batch) as last_batch FROM migrations";
    $stmt = $db->query($sql);
    $lastBatch = $stmt->fetch();
    $batch = intval($lastBatch['last_batch'] ?? 0) + 1;
    
    // 모든 마이그레이션 파일 가져오기
    $migrationFiles = glob(__DIR__ . '/*.php');
    $migrationFiles = array_filter($migrationFiles, function($file) {
        return basename($file) !== 'migrate.php';
    });
    sort($migrationFiles);
    
    // 트랜잭션 시작
    $db->beginTransaction();
    
    try {
        $migrationsRun = 0;
        
        foreach ($migrationFiles as $file) {
            $migration = basename($file, '.php');
            
            // 이미 실행된 마이그레이션 스킵
            if (in_array($migration, $ranMigrations)) {
                continue;
            }
            
            // 클래스 로드 및 인스턴스 생성
            require_once $file;
            $className = getClassNameFromFile($file);
            $instance = new $className();
            
            // 마이그레이션 실행
            echo "마이그레이션 실행 중: {$migration}... ";
            if ($instance->up($db)) {
                // 이력 저장
                $sql = "INSERT INTO migrations (migration, batch) VALUES (?, ?)";
                $stmt = $db->prepare($sql);
                $stmt->execute([$migration, $batch]);
                
                echo "완료\n";
                $migrationsRun++;
            } else {
                echo "실패\n";
                throw new Exception("마이그레이션 {$migration} 실행 실패");
            }
        }
        
        // 트랜잭션 커밋
        $db->commit();
        
        if ($migrationsRun > 0) {
            echo "총 {$migrationsRun}개의 마이그레이션이 실행되었습니다.\n";
        } else {
            echo "실행할 마이그레이션이 없습니다.\n";
        }
    } catch (Exception $e) {
        // 오류 발생 시 롤백
        $db->rollBack();
        die("마이그레이션 오류: " . $e->getMessage() . "\n");
    }
}

/**
 * 마이그레이션 롤백
 * 
 * @param PDO $db 데이터베이스 연결 객체
 * @param int|null $targetBatch 롤백할 배치 번호 (null이면 마지막 배치)
 * @return void
 */
function rollbackMigrations($db, $targetBatch = null) {
    // 마이그레이션 이력 테이블 확인
    createMigrationTable($db);
    
    // 롤백할 마이그레이션 가져오기
    if ($targetBatch !== null) {
        $sql = "SELECT * FROM migrations WHERE batch >= ? ORDER BY id DESC";
        $stmt = $db->prepare($sql);
        $stmt->execute([$targetBatch]);
    } else {
        $sql = "SELECT MAX(batch) as last_batch FROM migrations";
        $stmt = $db->query($sql);
        $lastBatch = $stmt->fetch();
        
        if (empty($lastBatch['last_batch'])) {
            echo "롤백할 마이그레이션이 없습니다.\n";
            return;
        }
        
        $sql = "SELECT * FROM migrations WHERE batch = ? ORDER BY id DESC";
        $stmt = $db->prepare($sql);
        $stmt->execute([$lastBatch['last_batch']]);
    }
    
    $migrations = $stmt->fetchAll();
    
    if (empty($migrations)) {
        echo "롤백할 마이그레이션이 없습니다.\n";
        return;
    }
    
    // 트랜잭션 시작
    $db->beginTransaction();
    
    try {
        $migrationsRolledBack = 0;
        
        foreach ($migrations as $migration) {
            $file = __DIR__ . '/' . $migration['migration'] . '.php';
            
            if (!file_exists($file)) {
                echo "경고: 마이그레이션 파일을 찾을 수 없습니다: {$migration['migration']}.php\n";
                continue;
            }
            
            // 클래스 로드 및 인스턴스 생성
            require_once $file;
            $className = getClassNameFromFile($file);
            $instance = new $className();
            
            // 롤백 실행
            echo "마이그레이션 롤백 중: {$migration['migration']}... ";
            if ($instance->down($db)) {
                // 이력 삭제
                $sql = "DELETE FROM migrations WHERE id = ?";
                $stmt = $db->prepare($sql);
                $stmt->execute([$migration['id']]);
                
                echo "완료\n";
                $migrationsRolledBack++;
            } else {
                echo "실패\n";
                throw new Exception("마이그레이션 {$migration['migration']} 롤백 실패");
            }
        }
        
        // 트랜잭션 커밋
        $db->commit();
        
        echo "총 {$migrationsRolledBack}개의 마이그레이션이 롤백되었습니다.\n";
    } catch (Exception $e) {
        // 오류 발생 시 롤백
        $db->rollBack();
        die("마이그레이션 오류: " . $e->getMessage() . "\n");
    }
}

/**
 * 파일에서 클래스 이름 추출
 * 
 * @param string $file 파일 경로
 * @return string 클래스 이름
 */
function getClassNameFromFile($file) {
    $content = file_get_contents($file);
    preg_match('/class\s+([a-zA-Z0-9_]+)/', $content, $matches);
    
    if (empty($matches[1])) {
        throw new Exception("파일 {$file}에서 클래스를 찾을 수 없습니다.");
    }
    
    return $matches[1];
}

// 메인 실행 코드
try {
    // 데이터베이스 생성 및 연결
    $pdo = createDbConnection();
    $dbName = $_ENV['DB_DATABASE'] ?? 'influencer_db';
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbName`");
    echo "데이터베이스 '{$dbName}'을(를) 확인했습니다.\n";
    $pdo->exec("USE `$dbName`");
    
    // 명령 처리
    if (isset($options['status'])) {
        // 상태 확인
        checkStatus($pdo);
    } elseif (isset($options['create'])) {
        // 마이그레이션 파일 생성
        createMigration($options['create']);
    } elseif (isset($options['rollback'])) {
        // 롤백 실행
        $targetBatch = $options['rollback'] !== false ? intval($options['rollback']) : null;
        rollbackMigrations($pdo, $targetBatch);
    } else {
        // 기본: 마이그레이션 실행
        runMigrations($pdo);
    }
} catch (PDOException $e) {
    die("데이터베이스 오류: " . $e->getMessage() . "\n");
} catch (Exception $e) {
    die("오류: " . $e->getMessage() . "\n");
} 