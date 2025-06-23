<?php
// modal_orderDetail.php
?>
<!-- 모달 백드롭 -->
<div id="modalBackdrop_orderDetail" class="modal-backdrop"></div>

<!-- 주문 상세 모달 -->
<div id="modal_orderDetail" class="modal" role="dialog" aria-modal="true">
    <div class="modal-content" style="max-width:800px;">
        <div class="modal-header d-flex align-items-center">
            <h2 class="h5 mb-0"><i class="fas fa-shopping-bag text-primary me-2"></i> <span id="orderDetailTitle">주문 상세 정보</span></h2>
            <button type="button" class="btn-close" onclick="closeOrderDetailModal();"></button>
        </div>

        <div class="modal-body">
            <div class="order-detail-content">
                <!-- 주문 기본 정보 -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>주문 기본 정보</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <strong>주문번호:</strong> <span id="orderDetailId"></span>
                            </div>
                            <div class="col-md-6 mb-2">
                                <strong>주문일시:</strong> <span id="orderDetailDate"></span>
                            </div>
                            <div class="col-md-6 mb-2">
                                <strong>결제방법:</strong> <span id="orderDetailPaymentMethod"></span>
                            </div>
                            <div class="col-md-6 mb-2">
                                <strong>결제금액:</strong> <span id="orderDetailAmount"></span>
                            </div>
                            <div class="col-md-6 mb-2">
                                <strong>결제상태:</strong> <span id="orderDetailPaymentStatus"></span>
                            </div>
                            <div class="col-md-6 mb-2">
                                <strong>배송상태:</strong> <span id="orderDetailShippingStatus"></span>
                            </div>
                            <div class="col-md-6 mb-2">
                                <strong>회원ID:</strong> <span id="orderDetailMemberId"></span>
                            </div>
                            <div class="col-md-6 mb-2">
                                <strong>이메일:</strong> <span id="orderDetailEmail"></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 주문 품목 정보 -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-list me-2"></i>주문 품목 정보</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>상품명</th>
                                        <th>옵션</th>
                                        <th>수량</th>
                                        <th>가격</th>
                                        <th>상태</th>
                                    </tr>
                                </thead>
                                <tbody id="orderDetailItems">
                                    <!-- 품목 정보가 여기에 동적으로 추가됩니다 -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal-footer">
            <div class="d-flex justify-content-between w-100">
                <div>
                    <?php if (isAdmin()): ?>
                    <button type="button" class="btn btn-warning" onclick="openOrderStatusModal()">
                        <i class="fas fa-sync-alt me-1"></i> 주문상태 변경
                    </button>
                    <?php endif; ?>
                </div>
                <div>
                    <button type="button" class="btn btn-secondary" onclick="closeOrderDetailModal();">
                        닫기
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* 주문 상세 모달 스타일 */
#orderDetailItems tr td {
    vertical-align: middle;
}

/* 상태값별 색상 */
.status-prepare {
    color: #fff;
    background-color: var(--bs-primary);
}
.status-prepareproduct {
    color: #fff;
    background-color: var(--bs-info);
}
.status-hold {
    color: #fff;
    background-color: var(--bs-warning);
}
.status-unhold {
    color: #fff;
    background-color: var(--bs-success);
}

/* 반응형 미디어쿼리 */
@media (max-width: 768px) {
    #modal_orderDetail .card-body {
        padding: 0.75rem;
    }
    
    #modal_orderDetail .table th, 
    #modal_orderDetail .table td {
        padding: 0.5rem;
        font-size: 0.9rem;
    }
}

@media (max-width: 576px) {
    #modal_orderDetail .row > div {
        margin-bottom: 0.5rem;
    }
    
    #modal_orderDetail .table th, 
    #modal_orderDetail .table td {
        padding: 0.35rem;
        font-size: 0.85rem;
    }
}
</style>

<script charset="utf-8" content="text/javascript;charset=utf-8">
// 전역 변수
let currentOrderId = null;

// 주문 상세 모달 열기
function showOrderDetail(omSeq) {
    $('#loading').show();
    
    // 현재 선택된 주문 ID 저장
    currentOrderId = omSeq;
    
    $.ajax({
        url: `/api/order/${omSeq}`,
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.status === 'ok' && response.data) {
                populateOrderDetailModal(response.data);
                openModal('modal_orderDetail', 'modalBackdrop_orderDetail');
            } else {
                $.alert('데이터를 불러올 수 없습니다.', null, '실패', 'error');
            }
        },
        error: function(xhr) {
            const errorMsg = xhr.responseJSON?.message || '요청 처리 중 오류가 발생했습니다.';
            $.alert(errorMsg, null, '실패', 'error');
        },
        complete: function() {
            $('#loading').hide();
        }
    });
}

// 주문 상세 정보 채우기
function populateOrderDetailModal(data) {
    const order = data.order;
    const items = data.items;
    
    // 주문 기본 정보 표시
    $('#orderDetailId').text(order.order_id);
    $('#orderDetailDate').text(formatDateTime(order.order_date));
    $('#orderDetailPaymentMethod').text(order.payment_method_name || order.payment_method);
    $('#orderDetailAmount').text(formatCurrency(order.payment_amount) + ' ' + order.currency);
    
    // 결제 상태 표시
    let paymentStatus = '미결제';
    if (order.paid === 'T') {
        paymentStatus = '<span class="badge bg-success">결제완료</span>';
    } else if (order.paid === 'F') {
        paymentStatus = '<span class="badge bg-danger">미결제</span>';
    }
    
    // 취소 상태 표시
    if (order.canceled === 'T') {
        paymentStatus = '<span class="badge bg-secondary">취소됨</span>';
    }
    $('#orderDetailPaymentStatus').html(paymentStatus);
    
    // 배송 상태 표시
    let shippingStatusHtml = getShippingStatusHtml(order.shipping_status);
    $('#orderDetailShippingStatus').html(shippingStatusHtml);
    
    // 회원 정보 표시
    $('#orderDetailMemberId').text(order.member_id || '비회원');
    $('#orderDetailEmail').text(order.member_email || '-');
    
    // 주문 품목 정보 표시
    let itemsHtml = '';
    if (items && items.length > 0) {
        items.forEach(function(item) {
            itemsHtml += `
                <tr>
                    <td>${item.product_name || item.product_name_default}</td>
                    <td>${item.option_value || '-'}</td>
                    <td>${item.quantity}</td>
                    <td>${formatCurrency(item.payment_amount)} ${order.currency}</td>
                    <td>${getOrderItemStatusHtml(item.order_status)}</td>
                </tr>
            `;
        });
    } else {
        itemsHtml = '<tr><td colspan="5" class="text-center">주문 품목 정보가 없습니다.</td></tr>';
    }
    $('#orderDetailItems').html(itemsHtml);
}

// 배송 상태 HTML 생성
function getShippingStatusHtml(status) {
    let html = '-';
    
    switch(status) {
        case 'prepare':
            html = '<span class="badge status-prepare">배송준비중</span>';
            break;
        case 'prepareproduct':
            html = '<span class="badge status-prepareproduct">상품준비중</span>';
            break;
        case 'hold':
            html = '<span class="badge status-hold">배송보류</span>';
            break;
        case 'unhold':
            html = '<span class="badge status-unhold">배송보류해제</span>';
            break;
        default:
            html = '<span class="badge bg-secondary">알수없음</span>';
    }
    
    return html;
}

// 주문 품목 상태 HTML 생성
function getOrderItemStatusHtml(status) {
    let html = '-';
    
    switch(status) {
        case 'N1':
            html = '<span class="badge bg-primary">신규주문</span>';
            break;
        case 'N2':
            html = '<span class="badge bg-info">상품준비중</span>';
            break;
        case 'N3':
            html = '<span class="badge bg-success">배송준비중</span>';
            break;
        case 'N4':
            html = '<span class="badge bg-success">배송중</span>';
            break;
        case 'N5':
            html = '<span class="badge bg-primary">배송완료</span>';
            break;
        case 'C1':
        case 'C2':
        case 'C3':
            html = '<span class="badge bg-danger">취소</span>';
            break;
        case 'R1':
        case 'R2':
        case 'R3':
            html = '<span class="badge bg-warning">반품</span>';
            break;
        case 'E1':
        case 'E2':
        case 'E3':
            html = '<span class="badge bg-warning">교환</span>';
            break;
        default:
            html = '<span class="badge bg-secondary">기타</span>';
    }
    
    return html;
}

// 통화 형식 변환
function formatCurrency(amount) {
    if (!amount) return '0';
    
    // 문자열로 변환하고 천 단위 콤마 추가
    return Number(amount).toLocaleString();
}

// 주문 상세 모달 닫기
function closeOrderDetailModal() {
    closeModal('modal_orderDetail', 'modalBackdrop_orderDetail');
    currentOrderId = null;
}

// 주문 상태 변경 모달 열기
function openOrderStatusModal() {
    if (currentOrderId) {
        openModal('modal_orderStatus', 'modalBackdrop_orderStatus');
    } else {
        $.alert('주문 정보를 찾을 수 없습니다.', null, '오류', 'error');
    }
}
</script> 