<?php
// modal_groupBuyingCalendarView.php
?>
<!-- 모달 백드롭 -->
<div id="modalBackdrop_calendar" class="modal-backdrop"></div>

<!-- 캘린더 뷰 모달 -->
<div id="modal_groupBuyingCalendarView" class="modal" role="dialog" aria-modal="true">
    <div class="modal-content" style="max-width:1200px;">
        <div class="modal-header d-flex align-items-center">
            <h2 class="h5 mb-0"><i class="fas fa-calendar-alt text-primary me-2"></i> 공동구매 일정 캘린더</h2>
            <button type="button" class="btn-close" onclick="closeModal('modal_groupBuyingCalendarView', 'modalBackdrop_calendar')"></button>
        </div>

        <div class="modal-body">
            <!-- 필터 영역 -->
            <div class="mb-3 d-flex justify-content-between">
                <div class="btn-group">
                    <button type="button" class="btn btn-outline-primary active" onclick="changeCalendarView('weekly')">주간 보기</button>
                    <button type="button" class="btn btn-outline-primary" onclick="changeCalendarView('monthly')">월간 보기</button>
                </div>
                <div class="input-group" style="width: 300px;">
                    <input type="text" class="form-control" id="calendarDateSearch" placeholder="날짜 검색">
                    <button class="btn btn-outline-primary" type="button">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>

            <!-- 캘린더 테이블 -->
            <div class="table-responsive">
                <table class="table table-bordered table-hover text-center">
                    <thead>
                        <tr class="bg-light">
                            <th width="19%">날짜</th>
                            <th width="35%">상품명</th>
                            <th width="10%">상태</th>
                            <th width="12%">목표 수량</th>
                            <th width="10%">달성</th>
                            <th width="14%">남은 시간</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="table-row-enhanced">
                            <td>2025-04-01 ~ 2025-04-10</td>
                            <td class="text-start">
                                <div class="d-flex align-items-center">
                                    <div class="product-icon me-2 bg-light rounded p-2 shadow-sm">
                                        <i class="fas fa-box text-primary"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0 font-bold">유기농 블루베리 500g</h6>
                                        <small class="text-muted">#GP001</small>
                                    </div>
                                </div>
                            </td>
                            <td><span class="badge badge-success">진행 중</span></td>
                            <td>100 건</td>
                            <td>65 건</td>
                            <td>D-3</td>
                        </tr>
                        <tr class="table-row-enhanced">
                            <td>2025-04-05 ~ 2025-04-12</td>
                            <td class="text-start">
                                <div class="d-flex align-items-center">
                                    <div class="product-icon me-2 bg-light rounded p-2 shadow-sm">
                                        <i class="fas fa-box text-primary"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0 font-bold">천연 아로마 오일 50ml</h6>
                                        <small class="text-muted">#GP002</small>
                                    </div>
                                </div>
                            </td>
                            <td><span class="badge badge-danger">마감 임박</span></td>
                            <td>80 건</td>
                            <td>78 건</td>
                            <td>D-1</td>
                        </tr>
                        <tr class="table-row-enhanced">
                            <td>2025-04-15 ~ 2025-04-20</td>
                            <td class="text-start">
                                <div class="d-flex align-items-center">
                                    <div class="product-icon me-2 bg-light rounded p-2 shadow-sm">
                                        <i class="fas fa-box text-primary"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0 font-bold">수제 초콜릿 세트</h6>
                                        <small class="text-muted">#GP003</small>
                                    </div>
                                </div>
                            </td>
                            <td><span class="badge badge-secondary">예정</span></td>
                            <td>60 건</td>
                            <td>0 건</td>
                            <td>--</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeModal('modal_groupBuyingCalendarView', 'modalBackdrop_calendar')">
                닫기
            </button>
        </div>
    </div>
</div>

<script charset="utf-8" content="text/javascript;charset=utf-8">
// 캘린더 뷰 변경 (주간/월간)
function changeCalendarView(viewType) {
    // 버튼 상태 변경
    const buttons = document.querySelectorAll('#modal_groupBuyingCalendarView .btn-group .btn');
    buttons.forEach(btn => {
        btn.classList.remove('active');
        if ((viewType === 'weekly' && btn.textContent.trim() === '주간 보기') ||
            (viewType === 'monthly' && btn.textContent.trim() === '월간 보기')) {
            btn.classList.add('active');
        }
    });
    
    // 여기에 캘린더 뷰 변경 로직 추가
    console.log('캘린더 뷰 변경:', viewType);
}
</script> 