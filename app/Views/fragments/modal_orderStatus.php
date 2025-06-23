<?php
// modal_orderStatus.php
?>
<!-- 모달 백드롭 -->
<div id="modalBackdrop_orderStatus" class="modal-backdrop"></div>

<!-- 주문 상태 변경 모달 -->
<div id="modal_orderStatus" class="modal" role="dialog" aria-modal="true">
    <div class="modal-content" style="max-width:500px;">
        <div class="modal-header">
            <h2 class="h5 mb-0"><i class="fas fa-sync-alt text-warning me-2"></i> 주문 상태 변경</h2>
            <button type="button" class="btn-close" onclick="closeOrderStatusModal()"></button>
        </div>

        <div class="modal-body">
            <form id="orderStatusForm">
                <div class="mb-3">
                    <label for="orderStatus" class="form-label">변경할 상태</label>
                    <select class="form-select" id="orderStatus" name="status" required>
                        <option value="">-- 상태 선택 --</option>
                        <option value="prepare">배송준비중</option>
                        <option value="prepareproduct">상품준비중</option>
                        <option value="hold">배송보류</option>
                        <option value="unhold">배송보류해제</option>
                    </select>
                </div>
                
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    주의: 상태 변경 시 카페24 쇼핑몰에도 즉시 반영됩니다.
                </div>
            </form>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn btn-primary" onclick="updateOrderStatus()">
                <i class="fas fa-save me-1"></i> 상태 변경
            </button>
            <button type="button" class="btn btn-secondary" onclick="closeOrderStatusModal()">
                <i class="fas fa-times me-1"></i> 취소
            </button>
        </div>
    </div>
</div>

<script charset="utf-8" content="text/javascript;charset=utf-8">
// 주문 상태 변경 모달 닫기
function closeOrderStatusModal() {
    closeModal('modal_orderStatus', 'modalBackdrop_orderStatus');
    $('#orderStatus').val('');
}

// 주문 상태 업데이트
function updateOrderStatus() {
    const status = $('#orderStatus').val();
    
    if (!status) {
        $.alert('변경할 상태를 선택해주세요.', function() {
            $('#orderStatus').focus();
        }, '알림', 'warning');
        return;
    }
    
    if (!currentOrderId) {
        $.alert('주문 정보를 찾을 수 없습니다.', null, '오류', 'error');
        return;
    }
    
    // 확인 메시지
    $.confirm('주문 상태를 변경하시겠습니까?', function() {
        $('#loading').show();
        
        $.ajax({
            url: `/api/order/status/${currentOrderId}`,
            type: 'PUT',
            contentType: 'application/json',
            data: JSON.stringify({ status: status }),
            success: function(response) {
                $.alert('주문 상태가 변경되었습니다.', function() {
                    // 주문 상세 정보 다시 로드
                    showOrderDetail(currentOrderId);
                    // 주문 상태 변경 모달 닫기
                    closeOrderStatusModal();
                    // 주문 목록 새로고침
                    if (typeof loadOrderData === 'function') {
                        loadOrderData();
                    }
                });
            },
            error: function(xhr) {
                const errorMsg = xhr.responseJSON?.message || '요청 처리 중 오류가 발생했습니다.';
                $.alert(errorMsg, null, '실패', 'error');
            },
            complete: function() {
                $('#loading').hide();
            }
        });
    });
}
</script> 