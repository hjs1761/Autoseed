<?php
/**
 * API 호출 테스트 예제
 * 
 * 이 파일은 API 호출 및 응답 처리를 모의(mock)로 시뮬레이션하는 예제를 제공합니다.
 * 실제 API 서버 없이 API 호출과 응답 처리 로직을 테스트할 수 있습니다.
 */

/**
 * API 응답을 모의로 생성하는 함수
 * 
 * @param string $endpoint API 엔드포인트 경로
 * @param string $method HTTP 메서드(GET, POST 등)
 * @param array|null $data 요청 데이터(POST 요청 시)
 * @return array 모의 API 응답
 */
function mockApiResponse($endpoint, $method = 'GET', $data = null) {
    echo "API 호출: $method $endpoint\n";
    
    // 인플루언서 목록 조회 API
    if ($endpoint === '/api/influencers' && $method === 'GET') {
        return [
            'success' => true,
            'data' => [
                'influencers' => [
                    [
                        'id' => 1,
                        'name' => '홍길동',
                        'handle' => 'honggildong',
                        'follower_count' => 50000,
                        'platform_id' => 1
                    ],
                    [
                        'id' => 2,
                        'name' => '김철수',
                        'handle' => 'kimcheolsu',
                        'follower_count' => 75000,
                        'platform_id' => 2
                    ]
                ],
                'pagination' => [
                    'total' => 2,
                    'per_page' => 10,
                    'current_page' => 1,
                    'last_page' => 1
                ]
            ]
        ];
    }
    
    // 인플루언서 상세 조회 API
    if (preg_match('#^/api/influencers/(\d+)$#', $endpoint, $matches) && $method === 'GET') {
        $id = $matches[1];
        return [
            'success' => true,
            'data' => [
                'influencer' => [
                    'id' => $id,
                    'name' => '홍길동',
                    'handle' => 'honggildong',
                    'bio' => '인플루언서 소개글입니다.',
                    'follower_count' => 50000,
                    'engagement_rate' => 3.5,
                    'platform_id' => 1,
                    'created_at' => '2023-05-28 12:00:00',
                    'updated_at' => '2023-05-28 12:00:00'
                ]
            ]
        ];
    }
    
    // 인플루언서 생성 API
    if ($endpoint === '/api/influencers' && $method === 'POST') {
        // 필수 필드 검증
        if (empty($data['name']) || empty($data['handle'])) {
            return [
                'success' => false,
                'message' => '유효성 검사 실패',
                'errors' => [
                    'name' => empty($data['name']) ? '이름은 필수입니다.' : null,
                    'handle' => empty($data['handle']) ? '핸들은 필수입니다.' : null
                ]
            ];
        }
        
        return [
            'success' => true,
            'message' => '인플루언서가 성공적으로 생성되었습니다.',
            'data' => [
                'id' => 3,
                'name' => $data['name'],
                'handle' => $data['handle']
            ]
        ];
    }
    
    // 기본 응답
    return [
        'success' => false,
        'message' => '지원하지 않는 API 엔드포인트'
    ];
}

// 테스트 시나리오 시작

/**
 * 테스트 1: 인플루언서 목록 조회
 * GET /api/influencers 요청을 시뮬레이션합니다.
 */
echo "=== 인플루언서 목록 조회 예제 ===\n";
$response = mockApiResponse('/api/influencers');
echo "성공 여부: " . ($response['success'] ? '성공' : '실패') . "\n";
echo "인플루언서 수: " . count($response['data']['influencers']) . "\n";

/**
 * 테스트 2: 인플루언서 상세 정보 조회
 * GET /api/influencers/{id} 요청을 시뮬레이션합니다.
 */
echo "\n=== 인플루언서 상세 조회 예제 ===\n";
$response = mockApiResponse('/api/influencers/1');
echo "성공 여부: " . ($response['success'] ? '성공' : '실패') . "\n";
echo "인플루언서 이름: " . $response['data']['influencer']['name'] . "\n";
echo "팔로워 수: " . number_format($response['data']['influencer']['follower_count']) . "\n";

/**
 * 테스트 3: 인플루언서 생성 (성공 케이스)
 * 유효한 데이터로 POST /api/influencers 요청을 시뮬레이션합니다.
 */
echo "\n=== 인플루언서 생성 예제 (성공) ===\n";
$newInfluencer = [
    'name' => '새 인플루언서',
    'handle' => 'newinfluencer',
    'platform_id' => 1
];
$response = mockApiResponse('/api/influencers', 'POST', $newInfluencer);
echo "성공 여부: " . ($response['success'] ? '성공' : '실패') . "\n";
if ($response['success']) {
    echo "생성된 ID: " . $response['data']['id'] . "\n";
}

/**
 * 테스트 4: 인플루언서 생성 (실패 케이스)
 * 유효하지 않은 데이터로 POST /api/influencers 요청을 시뮬레이션합니다.
 */
echo "\n=== 인플루언서 생성 예제 (실패) ===\n";
$invalidInfluencer = [
    'platform_id' => 1
];
$response = mockApiResponse('/api/influencers', 'POST', $invalidInfluencer);
echo "성공 여부: " . ($response['success'] ? '성공' : '실패') . "\n";
if (!$response['success']) {
    echo "오류 메시지: " . $response['message'] . "\n";
    foreach ($response['errors'] as $field => $error) {
        if ($error) echo "- $field: $error\n";
    }
} 