<?php
// modal_send_notification.php
?>
<!-- 모달 백드롭 -->
<div id="modalBackdrop_notification" class="modal-backdrop"></div>

<!-- 알림 전송 모달 -->
<div id="modal_sendNotification" class="modal" role="dialog" aria-modal="true">
    <div class="modal-content" style="max-width:600px;">
        <div class="modal-header d-flex align-items-center">
            <h2 class="h5 mb-0"><i class="fas fa-bell text-primary me-2"></i> 공동구매 참여자 알림 전송</h2>
            <button type="button" class="btn-close" onclick="closeModal('modal_sendNotification', 'modalBackdrop_notification')"></button>
        </div>

        <div class="modal-body">
            <form id="notificationForm">
                <input type="hidden" id="targetEventId" name="eventId" value="">
                
                <!-- 알림 유형 선택 -->
                <div class="mb-3">
                    <label class="form-label fw-bold">알림 유형</label>
                    <div class="d-flex flex-wrap gap-2">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="notificationType" id="notifyAll" value="all" checked>
                            <label class="form-check-label" for="notifyAll">
                                전체 참여자
                            </label>
                        </div>
                    </div>
                </div>
                
                <!-- 알림 채널 선택 -->
                <div class="mb-3">
                    <label class="form-label fw-bold">알림 채널</label>
                    <div class="d-flex flex-wrap gap-2">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="notificationChannel[]" id="channelEmail" value="email" checked>
                            <label class="form-check-label" for="channelEmail">
                                이메일
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="notificationChannel[]" id="channelSms" value="sms" checked>
                            <label class="form-check-label" for="channelSms">
                                SMS
                            </label>
                        </div>
                    </div>
                </div>
                
                <!-- 알림 제목 -->
                <div class="mb-3">
                    <label for="notificationTitle" class="form-label fw-bold">알림 제목</label>
                    <input type="text" class="form-control" id="notificationTitle" name="title" placeholder="알림 제목을 입력하세요">
                </div>
                
                <!-- 알림 내용 -->
                <div class="mb-3">
                    <label for="notificationContent" class="form-label fw-bold">알림 내용</label>
                    <textarea class="form-control" id="notificationContent" name="content" rows="5" placeholder="알림 내용을 입력하세요"></textarea>
                </div>
                
                <!-- 예약 발송 옵션 -->
                <div class="mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="scheduleNotification" name="isScheduled" value="1">
                        <label class="form-check-label" for="scheduleNotification">
                            예약 발송
                        </label>
                    </div>
                    <div id="scheduleDateTimeWrapper" class="mt-2 d-none">
                        <input type="datetime-local" class="form-control" id="scheduleDateTime" name="scheduledTime">
                    </div>
                </div>
            </form>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn btn-primary me-2" onclick="submitNotification()">
                <i class="fas fa-paper-plane me-1"></i> 알림 전송
            </button>
            <button type="button" class="btn btn-secondary" onclick="closeModal('modal_sendNotification', 'modalBackdrop_notification')">
                <i class="fas fa-times me-1"></i> 취소
            </button>
        </div>
    </div>
</div>

<script charset="utf-8" content="text/javascript;charset=utf-8">
// 예약 발송 체크박스 이벤트
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('scheduleNotification').addEventListener('change', function() {
        const dateTimeWrapper = document.getElementById('scheduleDateTimeWrapper');
        if (this.checked) {
            dateTimeWrapper.classList.remove('d-none');
        } else {
            dateTimeWrapper.classList.add('d-none');
        }
    });
});

// 알림 전송 폼 제출
function submitNotification() {
    // 폼 데이터 수집
    const form = document.getElementById('notificationForm');
    const formData = new FormData(form);
    
    // 폼 유효성 검사
    const title = formData.get('title');
    const content = formData.get('content');
    const channels = formData.getAll('notificationChannel[]');
    
    if (!title) {
        $.alert('알림 제목을 입력해주세요.');
        return;
    }
    
    if (!content) {
        $.alert('알림 내용을 입력해주세요.');
        return;
    }
    
    if (channels.length === 0) {
        $.alert('적어도 하나의 알림 채널을 선택해주세요.');
        return;
    }
    
    // 예약 발송인 경우 날짜 검사
    if (formData.get('isScheduled') === '1') {
        const scheduleTime = formData.get('scheduledTime');
        if (!scheduleTime) {
            $.alert('예약 발송 시간을 선택해주세요.');
            return;
        }
    }
    
    // AJAX로 알림 전송 처리
    console.log('알림 전송 데이터:', Object.fromEntries(formData));
    
    // AJAX 요청
    $('#loading').show();
    
    $.ajax({
        url: '/api/group_buying/notification',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            $.alert('알림이 성공적으로 전송되었습니다.', function() {
                closeModal('modal_sendNotification', 'modalBackdrop_notification');
                document.getElementById('notificationForm').reset();
            });
        },
        error: function(xhr) {
            console.error(xhr);
            $.alert('알림 전송 중 오류가 발생했습니다.');
        },
        complete: function() {
            $('#loading').hide();
        }
    });
}
</script> 