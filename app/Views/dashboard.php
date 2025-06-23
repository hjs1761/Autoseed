<?php
// dashboard.php
?>
<main class="main flex-fill animate-fadeIn" id="main">
    <div class="container-fluid py-4">
        <div class="row mb-4">
            <div class="col-lg-12">
                <h1 class="page-title">대시보드</h1>
                <p class="text-muted">인플루언서 데이터 관리 현황을 확인할 수 있습니다.</p>
            </div>
        </div>

        <!-- 통계 카드 섹션 -->
        <div class="row mb-4">
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-1">총 인플루언서</h6>
                                <h3 class="mb-0"><?php echo number_format($stats['total_influencers'] ?? 0); ?></h3>
                            </div>
                            <div class="icon-circle bg-primary text-white">
                                <i class="bi bi-people-fill"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-1">플랫폼 수</h6>
                                <h3 class="mb-0"><?php echo number_format($stats['total_platforms'] ?? 0); ?></h3>
                            </div>
                            <div class="icon-circle bg-success text-white">
                                <i class="bi bi-globe"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-1">카테고리 수</h6>
                                <h3 class="mb-0"><?php echo number_format($stats['total_categories'] ?? 0); ?></h3>
                            </div>
                            <div class="icon-circle bg-info text-white">
                                <i class="bi bi-tags-fill"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-1">오늘 추가된 인플루언서</h6>
                                <h3 class="mb-0"><?php echo number_format($stats['today_added'] ?? 0); ?></h3>
                            </div>
                            <div class="icon-circle bg-warning text-white">
                                <i class="bi bi-graph-up"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 그래프 및 차트 섹션 -->
        <div class="row mb-4">
            <div class="col-lg-8 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-white py-3">
                        <h5 class="card-title m-0">플랫폼별 인플루언서 분포</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="platformChart" height="300"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-white py-3">
                        <h5 class="card-title m-0">카테고리별 인플루언서 분포</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="categoryChart" height="300"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- 최근 추가된 인플루언서 -->
        <div class="row">
            <div class="col-lg-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                        <h5 class="card-title m-0">최근 추가된 인플루언서</h5>
                        <a href="/influencers" class="btn btn-sm btn-primary">모두 보기</a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>이름</th>
                                        <th>핸들</th>
                                        <th>플랫폼</th>
                                        <th>팔로워</th>
                                        <th>참여율</th>
                                        <th>등록일</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($recentInfluencers)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center">등록된 인플루언서가 없습니다.</td>
                                    </tr>
                                    <?php else: ?>
                                        <?php foreach ($recentInfluencers as $influencer): ?>
                                        <tr>
                                            <td>
                                                <a href="/influencers/<?php echo $influencer['id']; ?>">
                                                    <?php echo htmlspecialchars($influencer['name']); ?>
                                                </a>
                                            </td>
                                            <td><?php echo htmlspecialchars($influencer['handle']); ?></td>
                                            <td><?php echo htmlspecialchars($influencer['platform_name'] ?? '없음'); ?></td>
                                            <td><?php echo number_format($influencer['follower_count']); ?></td>
                                            <td><?php echo number_format($influencer['engagement_rate'], 2); ?>%</td>
                                            <td><?php echo date('Y-m-d', strtotime($influencer['created_at'])); ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- 캘린더 뷰 모달 프래그먼트 포함 -->
<?php require_once 'fragments/modal_groupBuyingCalendarView.php'; ?>

<!-- 알림 전송 모달 프래그먼트 포함 -->
<?php require_once 'fragments/modal_sendNotification.php'; ?>

<!-- 중요 공지사항 모달 프래그먼트 포함 -->
<?php require_once 'fragments/modal_popupNotice.php'; ?>

<script charset="utf-8" content="text/javascript;charset=utf-8">
// 차트 초기화
let salesChart;

document.addEventListener('DOMContentLoaded', function() {
    // 차트 초기화
    initSalesChart();
    
    // 팝업 공지사항 초기화
    initPopupNotices();
});

// 팝업 공지사항 초기화 함수
function initPopupNotices() {
    <?php if (isset($popupNotices) && count($popupNotices) > 0): ?>
    // 서버에서 가져온 팝업 공지사항 데이터 설정
    popupNotices = <?= json_encode($popupNotices) ?>;
    
    // 공지사항이 있으면 표시
    if (popupNotices.length > 0) {
        setTimeout(function() {
            showNotice(0);
        }, 100);
    }
    <?php endif; ?>
}

function initExpiringTodayGroupBuyings() {
    $.ajax({
        url: '/api/dashboard/expiring-today',
        type: 'GET',
        success: function(response) {
            console.log(response);
        },
        error: function(error) {
            console.error('오늘 마감 공동구매 조회 중 오류:', error);
        }
    });
    $('#loading').show();
    $.ajax({
        url: 'api/group_buying',
        type: 'POST',
        dataType: 'json',
        contentType: 'application/json',
        data: JSON.stringify(formData),
        success: function(response) {
            $.alert('등록되었습니다.', function() {
                // 테이블 새로고침 및 폼 초기화
                loadGroupBuyingData();
                resetForm();
                closeModal('modal_groupBuyingInfo', 'modalBackdrop_groupBuyingInfo');
            });
        },
        error: function(e) {
            if(e.responseJSON && e.responseJSON.message) {
                $.alert(e.responseJSON.message, null, '실패', 'error');    
            } else {
                console.error(e);
                $.alert('데이터를 저장하는 중 에러가 발생했습니다.', null, '실패', 'error');
            }
        },
        complete: function() {
            $('#loading').hide();
        }
    });
}

// 차트 초기화 함수
function initSalesChart() {
    const ctx = document.getElementById('salesChart').getContext('2d');
    
    salesChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?=json_encode($weeklySalesStatistics['labels'])?>,
            datasets: [{
                label: '매출액',
                data: <?=json_encode($weeklySalesStatistics['data'])?>,
                borderColor: 'rgba(0, 161, 157, 1)',
                backgroundColor: 'rgba(0, 161, 157, 0.1)',
                fill: true,
                tension: 0.4,
                borderWidth: 2,
                pointBackgroundColor: 'rgba(0, 161, 157, 1)',
                pointRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            if (context.parsed.y !== null) {
                                label += new Intl.NumberFormat('ko-KR', { 
                                    style: 'currency', 
                                    currency: 'KRW',
                                    maximumFractionDigits: 0
                                }).format(context.parsed.y);
                            }
                            return label;
                        }
                    }
                }
            },
            layout: {
                padding: {
                    top: 5,
                    right: 15,
                    bottom: 10,
                    left: 10
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    min: 0,
                    // max: 10000000, // 최대값을 명시적으로 설정하여 높이 제한
                    ticks: {
                        callback: function(value) {
                            return new Intl.NumberFormat('ko-KR', { 
                                style: 'currency', 
                                currency: 'KRW',
                                notation: 'compact',
                                compactDisplay: 'short',
                                maximumFractionDigits: 0
                            }).format(value);
                        }
                    }
                }
            }
        }
    });
}

// 차트 업데이트 함수
function updateChart(period) {
    // 클릭된 버튼 active 상태로 변경
    const buttons = document.querySelectorAll('.card-header .btn-group-sm .btn');
    buttons.forEach(btn => {
        btn.classList.remove('active');
        if ((period === 'weekly' && btn.textContent.trim() === '주간') ||
            (period === 'monthly' && btn.textContent.trim() === '월간') ||
            (period === 'yearly' && btn.textContent.trim() === '연간')) {
            btn.classList.add('active');
        }
    });
    
    // 기간별 차트 데이터 변경 (예시)
    let labels, data;
    
    if (period === 'weekly') {
        labels = <?=json_encode($weeklySalesStatistics['labels'])?>;
        data = <?=json_encode($weeklySalesStatistics['data'])?>;
    } else if (period === 'monthly') {
        labels = <?=json_encode($monthlySalesStatistics['labels'])?>;
        data = <?=json_encode($monthlySalesStatistics['data'])?>;
    } else if (period === 'yearly') {
        labels = <?=json_encode($yearlySalesStatistics['labels'])?>;
        data = <?=json_encode($yearlySalesStatistics['data'])?>;
    }
    
    // 차트 데이터 업데이트
    salesChart.data.labels = labels;
    salesChart.data.datasets[0].data = data;
    salesChart.update();
}

// 캘린더 뷰 모달 열기
function openCalendarView() {
    openModal('modal_groupBuyingCalendarView', 'modalBackdrop_calendar');
}

// 알림 전송 모달 열기
function sendNotification(eventId) {
    document.getElementById('targetEventId').value = eventId;
    openModal('modal_sendNotification', 'modalBackdrop_notification');
}

document.addEventListener('DOMContentLoaded', function() {
    // 플랫폼 차트 데이터 (API에서 가져오거나 서버사이드에서 전달)
    const platformData = {
        labels: ['Instagram', 'YouTube', 'TikTok', 'Twitter', '기타'],
        datasets: [{
            data: [45, 25, 15, 10, 5],
            backgroundColor: [
                '#4e73df',
                '#e74a3b',
                '#36b9cc',
                '#1cc88a',
                '#f6c23e'
            ],
            hoverBackgroundColor: [
                '#2e59d9',
                '#e02d1b',
                '#2c9faf',
                '#17a673',
                '#f4b619'
            ],
            borderWidth: 1
        }]
    };

    // 카테고리 차트 데이터
    const categoryData = {
        labels: ['뷰티', '패션', '여행', '푸드', '라이프스타일', '기타'],
        datasets: [{
            data: [30, 20, 15, 15, 10, 10],
            backgroundColor: [
                '#4e73df',
                '#1cc88a',
                '#36b9cc',
                '#e74a3b',
                '#f6c23e',
                '#858796'
            ],
            hoverBackgroundColor: [
                '#2e59d9',
                '#17a673',
                '#2c9faf',
                '#e02d1b',
                '#f4b619',
                '#6e707e'
            ],
            borderWidth: 1
        }]
    };

    // 플랫폼 차트 생성
    const platformCtx = document.getElementById('platformChart').getContext('2d');
    new Chart(platformCtx, {
        type: 'bar',
        data: platformData,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: '인플루언서 수'
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });

    // 카테고리 차트 생성
    const categoryCtx = document.getElementById('categoryChart').getContext('2d');
    new Chart(categoryCtx, {
        type: 'doughnut',
        data: categoryData,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '70%',
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
});
</script>