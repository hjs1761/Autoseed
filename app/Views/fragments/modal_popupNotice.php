<?php
// modal_popupNotice.php
?>
<!-- 모달 백드롭 -->
<div id="modalBackdrop_popupNotice" class="modal-backdrop"></div>

<!-- 팝업 공지사항 모달 -->
<div id="modal_popupNotice" class="modal" role="dialog" aria-modal="true">
    <div class="modal-content notice-modal-content">
        <div class="modal-header d-flex align-items-center">
            <h2 class="h5 mb-0"><i class="fas fa-bell text-primary me-2"></i> <span id="popupNoticeTitle">공지사항</span></h2>
            <button type="button" class="btn-close" onclick="closeNoticeModal();"></button>
        </div>

        <div class="modal-body">
            <div class="notice-content">
                <div id="popupNoticeContent" class="mb-3"></div>
            </div>
        </div>

        <div class="modal-footer">
            <div class="d-flex justify-content-between w-100">
                <form id="noticeHideForm" style="display:none;">
                    <input type="hidden" name="bn_seq" value="">
                    <div class="form-check form-check-inline mb-0">
                        <input type="checkbox" class="form-check-input" id="neverShowAgain" name="never_show_again" value="1">
                        <label class="form-check-label" for="neverShowAgain">다시 보지 않기</label>
                    </div>
                </form>
                <div class="ms-auto">
                    <button type="button" class="btn btn-outline-secondary" onclick="closeNoticeModal();">
                        닫기
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.notice-modal-content {
    max-width: 600px;
    min-width: 300px;
    width: 100%;
}

#popupNoticeContent {
    overflow-y: auto;
    word-break: break-word;
}

#popupNoticeContent img {
    max-width: 100%;
    height: auto;
}

@media (max-width: 768px) {
    .notice-modal-content {
        max-width: 95%;
        width: 95%;
        margin: 0 auto;
    }
    
    #modal_popupNotice .modal-body {
        max-height: 50vh;
        overflow-y: auto;
    }
}

@media (max-width: 576px) {
    .notice-modal-content {
        max-width: 100%;
        min-width: 250px;
        width: 100%;
    }
    
    #modal_popupNotice .modal-header h2 {
        font-size: 1rem;
    }
    
    #modal_popupNotice .modal-body {
        max-height: 60vh;
    }
    
    #modal_popupNotice .form-check-label {
        font-size: 0.9rem;
    }
}
</style>

<script charset="utf-8" content="text/javascript;charset=utf-8">
// 공지사항 모달 관련 전역 변수
let currentNoticeIndex = 0;
let popupNotices = [];
let isAutoPopup = false; // 자동 팝업 여부를 나타내는 플래그

// 모달 닫기 함수
function closeNoticeModal() {
    // 자동 팝업이고 다시 보지 않기 체크박스 상태가 체크된 경우에만 처리
    const neverShowAgain = $("#neverShowAgain").is(":checked");
    
    // 다시 보지 않기가 체크되었고 현재 공지사항이 있는 경우
    if (isAutoPopup && neverShowAgain && popupNotices.length > 0 && currentNoticeIndex < popupNotices.length) {
        const noticeId = popupNotices[currentNoticeIndex].bn_seq;
        
        // 다시 보지 않기 API 호출
        $.ajax({
            url: "/api/notice/hide/" + noticeId,
            type: "PUT",
            contentType: "application/json",
            success: function(response) {
                console.log("공지사항 숨김 처리 완료:", noticeId);
            },
            error: function(xhr, status, error) {
                console.error("공지사항 숨김 처리 실패:", error);
            }
        });
    }
    
    // 모달 닫기
    closeModal('modal_popupNotice', 'modalBackdrop_popupNotice');
    
    // 자동 팝업일 때만 다음 공지사항 표시
    if (isAutoPopup) {
        currentNoticeIndex++;
        if (currentNoticeIndex < popupNotices.length) {
            setTimeout(function() {
                showNotice(currentNoticeIndex);
            }, 100);
        }
    }
}

// 공지사항 표시 함수 (인덱스 기반) - 대시보드 자동 팝업용
function showNotice(index) {
    if (!popupNotices.length || index >= popupNotices.length) {
        return;
    }
    
    // 자동 팝업 플래그 설정
    isAutoPopup = true;
    
    // 현재 인덱스 업데이트
    currentNoticeIndex = index;
    
    const notice = popupNotices[index];
    
    // 모달 내용 업데이트
    $("#popupNoticeTitle").text(notice.bn_title);
    
    // 날짜를 datetime 형식으로 표시
    const noticeDate = new Date(notice.reg_dt);
    const formattedDate = noticeDate.toLocaleString('ko-KR', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit',
        hour12: false
    });
    $("#popupNoticeContent").html(`<p class="text-end text-muted mb-2">${formattedDate}</p>${notice.bn_content}`);
    
    // 현재 공지사항 ID를 hidden input에 설정
    $("#noticeHideForm input[name='bn_seq']").val(notice.bn_seq);
    
    // 다시 보지 않기 체크박스 초기화
    $("#neverShowAgain").prop("checked", false);
    
    // 자동 팝업일 때만 다시보지않기 폼 표시
    $("#noticeHideForm").show();
    
    // 모달이 닫혀있다면 열기
    if (!$("#modal_popupNotice").hasClass("show")) {
        openModal('modal_popupNotice', 'modalBackdrop_popupNotice');
    }
    
    // 내용에 따라 모달 높이 조정
    adjustModalHeight();
}

// ID로 공지사항 표시 함수 (AJAX 기반) - 수동 조회용
function showNoticeById(noticeId) {
    $.ajax({
        url: "/api/notice/" + noticeId,
        type: "GET",
        dataType: "json",
        success: function(response) {
            // 자동 팝업 플래그 해제
            isAutoPopup = false;
            
            let data = response.data;
            // 모달 내용 업데이트
            $("#popupNoticeTitle").text(data.bn_title);
            
            // 날짜를 datetime 형식으로 표시
            const noticeDate = new Date(data.reg_dt);
            const formattedDate = noticeDate.toLocaleString('ko-KR', {
                year: 'numeric',
                month: '2-digit',
                day: '2-digit',
                hour: '2-digit',
                minute: '2-digit',
                hour12: false
            });
            
            $("#popupNoticeContent").html(`<p class="text-end text-muted mb-2">${formattedDate}</p>${data.bn_content}`);
            
            // 현재 공지사항 ID를 hidden input에 설정
            $("#noticeHideForm input[name='bn_seq']").val(data.bn_seq);
            
            // 수동 조회 시 다시보지않기 폼 숨김
            $("#noticeHideForm").hide();
            
            // 모달 열기
            openModal('modal_popupNotice', 'modalBackdrop_popupNotice');
            
            // 내용에 따라 모달 높이 조정
            adjustModalHeight();
        },
        error: function(xhr, status, error) {
            console.error("공지사항을 가져오는 중 오류가 발생했습니다:", error);
            alert("공지사항을 불러올 수 없습니다.");
        }
    });
}

// 모달 높이 조정 함수
function adjustModalHeight() {
    // 모달 내용에 따라 스크롤 여부 결정
    const modalBody = $("#modal_popupNotice .modal-body");
    const contentHeight = $("#popupNoticeContent").height();
    
    // 윈도우 높이의 80%를 초과하는 경우 스크롤 적용
    const maxHeight = window.innerHeight * 0.8;
    
    if (contentHeight > maxHeight) {
        modalBody.css({
            'max-height': maxHeight + 'px',
            'overflow-y': 'auto'
        });
    } else {
        modalBody.css({
            'max-height': 'none',
            'overflow-y': 'visible'
        });
    }
    
    // 이미지가 로드된 후 다시 높이 조정
    $("#popupNoticeContent img").on('load', function() {
        adjustModalHeight();
    });
}

// 창 크기 변경 시 모달 높이 재조정
$(window).on('resize', function() {
    if ($("#modal_popupNotice").hasClass("show")) {
        adjustModalHeight();
    }
});

// 페이지 로드 시 공지사항 데이터 가져오기
$(document).ready(function() {
    
});
</script>