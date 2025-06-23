<?php
/**
 * 모의 데이터베이스 조작 예제
 * 
 * 이 파일은 실제 데이터베이스 연결 없이 데이터베이스 관련 기능을 시뮬레이션합니다.
 */

// 필요한 파일 포함
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../include/common.php';

/**
 * MockDB 클래스 - 데이터베이스 작업을 메모리에서 시뮬레이션
 * 
 * 실제 데이터베이스 연결 없이 데이터 조작 작업을 테스트하기 위한 클래스입니다.
 * 데이터는 메모리 내 배열에 저장되며 기본 CRUD 작업과 트랜잭션을 지원합니다.
 */
class MockDB {
    /** @var array 테이블별 데이터를 저장하는 배열 */
    private $data = [];
    
    /** @var int 마지막으로 생성된 ID */
    private $lastInsertId = 0;
    
    /** @var MockLogger|null 로깅을 위한 객체 */
    private $logger;
    
    /**
     * 생성자
     * 
     * @param MockLogger|null $logger 로깅에 사용될 로거 객체
     */
    public function __construct($logger = null) {
        $this->logger = $logger;
        $this->initializeData();
    }
    
    private function initializeData() {
        // 플랫폼 데이터 초기화
        $this->data['platforms'] = [
            1 => [
                'id' => 1,
                'name' => '인스타그램',
                'website' => 'https://www.instagram.com',
                'created_at' => '2023-01-01 00:00:00',
                'updated_at' => '2023-01-01 00:00:00'
            ],
            2 => [
                'id' => 2,
                'name' => '유튜브',
                'website' => 'https://www.youtube.com',
                'created_at' => '2023-01-01 00:00:00',
                'updated_at' => '2023-01-01 00:00:00'
            ]
        ];
        
        // 인플루언서 데이터 초기화
        $this->data['influencers'] = [
            1 => [
                'id' => 1,
                'name' => '홍길동',
                'handle' => 'honggildong',
                'bio' => '한국의 유명 인플루언서',
                'follower_count' => 50000,
                'engagement_rate' => 4.5,
                'platform_id' => 1,
                'created_at' => '2023-01-01 00:00:00',
                'updated_at' => '2023-01-01 00:00:00'
            ],
            2 => [
                'id' => 2,
                'name' => '김철수',
                'handle' => 'kimchulsoo',
                'bio' => '여행 전문 인플루언서',
                'follower_count' => 120000,
                'engagement_rate' => 3.2,
                'platform_id' => 2,
                'created_at' => '2023-01-01 00:00:00',
                'updated_at' => '2023-01-01 00:00:00'
            ]
        ];
        
        // 마지막 ID 설정
        $this->lastInsertId = 2;
    }
    
    /**
     * 데이터 조회
     * 
     * @param string $table 테이블 이름
     * @param array $conditions 조회 조건 (키-값 쌍 또는 비교 연산자 포함)
     * @param array $columns 조회할 컬럼 (기본값: 모든 컬럼)
     * @param array $options 정렬, 제한 등의 추가 옵션
     * @return array 조회 결과 행 배열
     */
    public function select($table, $conditions = [], $columns = ['*'], $options = []) {
        if (!isset($this->data[$table])) {
            return [];
        }
        
        $result = [];
        
        foreach ($this->data[$table] as $row) {
            // 조건 체크
            $match = true;
            foreach ($conditions as $key => $value) {
                // 비교 연산자 처리
                if (strpos($key, ' >') !== false) {
                    $field = trim(str_replace(' >', '', $key));
                    if (!isset($row[$field]) || $row[$field] <= $value) {
                        $match = false;
                        break;
                    }
                } elseif (strpos($key, ' <') !== false) {
                    $field = trim(str_replace(' <', '', $key));
                    if (!isset($row[$field]) || $row[$field] >= $value) {
                        $match = false;
                        break;
                    }
                } else {
                    if (!isset($row[$key]) || $row[$key] != $value) {
                        $match = false;
                        break;
                    }
                }
            }
            
            if ($match) {
                // 컬럼 필터링
                if ($columns[0] === '*') {
                    $result[] = $row;
                } else {
                    $filteredRow = [];
                    foreach ($columns as $column) {
                        if (isset($row[$column])) {
                            $filteredRow[$column] = $row[$column];
                        }
                    }
                    $result[] = $filteredRow;
                }
            }
        }
        
        // 정렬 처리
        if (isset($options['order_by'])) {
            $orderBy = $options['order_by'];
            $field = $orderBy;
            $direction = 'ASC';
            
            if (strpos($orderBy, ' DESC') !== false) {
                $field = str_replace(' DESC', '', $orderBy);
                $direction = 'DESC';
            }
            
            usort($result, function ($a, $b) use ($field, $direction) {
                if (!isset($a[$field]) || !isset($b[$field])) {
                    return 0;
                }
                
                if ($direction === 'ASC') {
                    return $a[$field] <=> $b[$field];
                } else {
                    return $b[$field] <=> $a[$field];
                }
            });
        }
        
        // 제한 처리
        if (isset($options['limit']) && is_numeric($options['limit'])) {
            $result = array_slice($result, 0, $options['limit']);
        }
        
        return $result;
    }
    
    /**
     * 데이터 삽입
     * 
     * @param string $table 테이블 이름
     * @param array $data 삽입할 데이터 (컬럼-값 쌍)
     * @return int 새로 생성된 ID
     */
    public function insert($table, $data) {
        if (!isset($this->data[$table])) {
            $this->data[$table] = [];
        }
        
        $this->lastInsertId++;
        $data['id'] = $this->lastInsertId;
        $this->data[$table][$this->lastInsertId] = $data;
        
        if ($this->logger) {
            $this->logger->info("데이터 삽입: " . json_encode($data));
        }
        
        return $this->lastInsertId;
    }
    
    /**
     * 데이터 수정
     * 
     * @param string $table 테이블 이름
     * @param array $data 수정할 데이터 (컬럼-값 쌍)
     * @param array $conditions 수정 조건 (키-값 쌍)
     * @return bool 수정 성공 여부
     */
    public function update($table, $data, $conditions) {
        if (!isset($this->data[$table])) {
            return false;
        }
        
        $updated = false;
        foreach ($this->data[$table] as $id => $row) {
            $match = true;
            foreach ($conditions as $key => $value) {
                if (!isset($row[$key]) || $row[$key] != $value) {
                    $match = false;
                    break;
                }
            }
            
            if ($match) {
                foreach ($data as $key => $value) {
                    $this->data[$table][$id][$key] = $value;
                }
                $updated = true;
                
                if ($this->logger) {
                    $this->logger->info("데이터 수정: " . json_encode($data));
                }
            }
        }
        
        return $updated;
    }
    
    /**
     * 데이터 삭제
     * 
     * @param string $table 테이블 이름
     * @param array $conditions 삭제 조건 (키-값 쌍)
     * @return bool 삭제 성공 여부
     */
    public function delete($table, $conditions) {
        if (!isset($this->data[$table])) {
            return false;
        }
        
        $deleted = false;
        foreach ($this->data[$table] as $id => $row) {
            $match = true;
            foreach ($conditions as $key => $value) {
                if (!isset($row[$key]) || $row[$key] != $value) {
                    $match = false;
                    break;
                }
            }
            
            if ($match) {
                unset($this->data[$table][$id]);
                $deleted = true;
                
                if ($this->logger) {
                    $this->logger->info("데이터 삭제: " . json_encode($conditions));
                }
            }
        }
        
        return $deleted;
    }
    
    /**
     * 트랜잭션 시작
     * 
     * @return bool 성공 여부
     */
    public function beginTransaction() {
        if ($this->logger) {
            $this->logger->info("트랜잭션 시작");
        }
        return true;
    }
    
    /**
     * 트랜잭션 커밋
     * 
     * @return bool 성공 여부
     */
    public function commit() {
        if ($this->logger) {
            $this->logger->info("트랜잭션 커밋");
        }
        return true;
    }
    
    /**
     * 트랜잭션 롤백
     * 
     * @return bool 성공 여부
     */
    public function rollback() {
        if ($this->logger) {
            $this->logger->info("트랜잭션 롤백");
        }
        return true;
    }
}

/**
 * MockLogger 클래스 - 로깅 작업을 시뮬레이션
 */
class MockLogger {
    /**
     * 정보 로그 기록
     * 
     * @param string $message 로그 메시지
     */
    public function info($message) {
        echo "[INFO] $message\n";
    }
    
    /**
     * 오류 로그 기록
     * 
     * @param string $message 로그 메시지
     */
    public function error($message) {
        echo "[ERROR] $message\n";
    }
}

// 모의 로거 인스턴스 생성
$logger = new MockLogger();

// 모의 DB 인스턴스 생성
$db = new MockDB($logger);

// 데이터 삽입 예제
echo "=== 데이터 삽입 예제 ===\n";

// 플랫폼 데이터 삽입
$platformData = [
    'name' => '틱톡',
    'website' => 'https://www.tiktok.com',
    'created_at' => date('Y-m-d H:i:s'),
    'updated_at' => date('Y-m-d H:i:s')
];

try {
    $platformId = $db->insert('platforms', $platformData);
    echo "플랫폼 데이터 삽입 성공: ID = $platformId\n";
} catch (Exception $e) {
    echo "플랫폼 데이터 삽입 실패: " . $e->getMessage() . "\n";
}

// 인플루언서 데이터 삽입
$influencerData = [
    'name' => '테스트 인플루언서',
    'handle' => 'testinfluencer',
    'bio' => '테스트 인플루언서 소개',
    'follower_count' => 5000,
    'engagement_rate' => 3.5,
    'platform_id' => $platformId,
    'created_at' => date('Y-m-d H:i:s'),
    'updated_at' => date('Y-m-d H:i:s')
];

try {
    $influencerId = $db->insert('influencers', $influencerData);
    echo "인플루언서 데이터 삽입 성공: ID = $influencerId\n";
} catch (Exception $e) {
    echo "인플루언서 데이터 삽입 실패: " . $e->getMessage() . "\n";
}

// 데이터 조회 예제
echo "\n=== 데이터 조회 예제 ===\n";

// 인플루언서 목록 조회
$influencers = $db->select('influencers', [], ['*'], ['limit' => 5]);
echo "인플루언서 목록:\n";
foreach ($influencers as $influencer) {
    echo "- ID: {$influencer['id']}, 이름: {$influencer['name']}, 팔로워: {$influencer['follower_count']}\n";
}

// 조건부 조회
echo "\n특정 조건의 인플루언서 조회:\n";
$popularInfluencers = $db->select(
    'influencers',
    ['follower_count >' => 10000],
    ['id', 'name', 'follower_count'],
    ['order_by' => 'follower_count DESC', 'limit' => 3]
);

foreach ($popularInfluencers as $influencer) {
    echo "- ID: {$influencer['id']}, 이름: {$influencer['name']}, 팔로워: {$influencer['follower_count']}\n";
}

// 데이터 수정 예제
echo "\n=== 데이터 수정 예제 ===\n";
if (isset($influencerId)) {
    $updateData = [
        'follower_count' => 15000,
        'updated_at' => date('Y-m-d H:i:s')
    ];
    
    try {
        $result = $db->update('influencers', $updateData, ['id' => $influencerId]);
        echo "인플루언서 데이터 수정 " . ($result ? '성공' : '실패') . "\n";
        
        // 수정된 데이터 조회
        $updatedInfluencer = $db->select('influencers', ['id' => $influencerId])[0] ?? null;
        if ($updatedInfluencer) {
            echo "수정된 팔로워 수: {$updatedInfluencer['follower_count']}\n";
        }
    } catch (Exception $e) {
        echo "인플루언서 데이터 수정 실패: " . $e->getMessage() . "\n";
    }
}

// 데이터 삭제 예제
echo "\n=== 데이터 삭제 예제 ===\n";
if (isset($influencerId)) {
    try {
        $result = $db->delete('influencers', ['id' => $influencerId]);
        echo "인플루언서 데이터 삭제 " . ($result ? '성공' : '실패') . "\n";
        
        // 삭제 후 데이터 조회
        $deletedInfluencer = $db->select('influencers', ['id' => $influencerId]);
        echo "삭제 후 조회 결과: " . (empty($deletedInfluencer) ? '데이터 없음' : '데이터 존재') . "\n";
    } catch (Exception $e) {
        echo "인플루언서 데이터 삭제 실패: " . $e->getMessage() . "\n";
    }
}

// 트랜잭션 예제
echo "\n=== 트랜잭션 예제 ===\n";
try {
    $db->beginTransaction();
    
    // 플랫폼 추가
    $platformId = $db->insert('platforms', [
        'name' => '네이버 블로그',
        'website' => 'https://blog.naver.com',
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s')
    ]);
    
    // 인플루언서 추가
    $influencerId = $db->insert('influencers', [
        'name' => '네이버 블로거',
        'handle' => 'naverblogger',
        'platform_id' => $platformId,
        'follower_count' => 8000,
        'engagement_rate' => 2.8,
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s')
    ]);
    
    $db->commit();
    echo "트랜잭션 성공\n";
    
    // 트랜잭션 후 데이터 조회
    $newInfluencer = $db->select('influencers', ['id' => $influencerId])[0] ?? null;
    if ($newInfluencer) {
        echo "새로 추가된 인플루언서: {$newInfluencer['name']}, 플랫폼 ID: {$newInfluencer['platform_id']}\n";
    }
} catch (Exception $e) {
    $db->rollback();
    echo "트랜잭션 실패: " . $e->getMessage() . "\n";
}

// 관계 조인 시뮬레이션
echo "\n=== 관계 조인 시뮬레이션 ===\n";
$influencers = $db->select('influencers');
$platforms = $db->select('platforms');

// 인플루언서와 플랫폼 정보 함께 표시
echo "인플루언서 및 플랫폼 정보:\n";
foreach ($influencers as $influencer) {
    $platform = null;
    foreach ($platforms as $p) {
        if ($p['id'] == $influencer['platform_id']) {
            $platform = $p;
            break;
        }
    }
    
    echo "- {$influencer['name']} (팔로워: {$influencer['follower_count']}) - ";
    echo $platform ? "플랫폼: {$platform['name']}" : "플랫폼 정보 없음";
    echo "\n";
} 