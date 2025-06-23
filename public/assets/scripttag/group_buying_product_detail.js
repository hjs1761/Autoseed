(function() {
    let scriptTag = document.currentScript;
    let scriptTagParams = new URLSearchParams(scriptTag.src.split('?')[1]);
    
    // 초기화 함수 - 문서 로드 후 이벤트 리스너 설정
    function initShareModal() {
        // 공유 모달 닫기 버튼 이벤트 리스너
        $(document).on('click', '.gb-share-close', function() {
            $('.gb-share-modal').hide();
        });
        
        // 모달 외부 클릭 시 닫기 이벤트 리스너
        $(document).on('click', '.gb-share-modal', function(e) {
            if ($(e.target).hasClass('gb-share-modal')) {
                $('.gb-share-modal').hide();
            }
        });
        
        // 공유하기 버튼 이벤트 리스너
        $(document).on('click', '#gb-share-button', window.openShareModal);
        
        // SNS 공유 버튼 이벤트 리스너
        $(document).on('click', '#gb-kakao-share', window.shareToKakao);
        $(document).on('click', '#gb-facebook-share', window.shareToFacebook);
        $(document).on('click', '#gb-twitter-share', window.shareToTwitter);
        $(document).on('click', '#gb-link-share', window.copyShareLink);
    }
    
    // 전역 변수에 할당하여 외부에서 접근 가능하게 함
    window.openShareModal = function() {
        $('.gb-share-modal').css('display', 'flex');
    };
    
    window.shareToKakao = function() {
        const target = location.href.split('#')[0];
        const title  = document.title || '공동구매 이벤트';
        const img    =
            document.querySelector('meta[property="og:image"]')?.content || '';

        const orgPrice = $('.gb-original-price').text().replace(/[^0-9]/g, '');
        const eventPrice = $('.gb-discount-price').text().replace(/[^0-9]/g, '');
        const discountRate = $('.gb-discount-rate').text().replace(/[^0-9]/g, '');

        const q = new URLSearchParams({ u: target, t: title, i: img, rp: orgPrice, dp: eventPrice, dr: discountRate }).toString();

        /* 이미 열려 있으면 재사용, 없으면 새로 연다 ──────────────── */
        const features = 'width=640,height=750,resizable=yes,scrollbars=yes';
        const popup    = window.open(
            '',                  // 빈 창 먼저
            'kakao-link-popup',  // SDK 내부 이름과 동일!
            features
        );

        /* 실제 게이트웨이 페이지로 이동 */
        if (popup) {           // 팝업 차단되지 않음
            popup.location.href = `https://app.weaverloft.com/group_buying/assets/html/kakao-share.html?${q}`;
            popup.focus();
        } else {               // 팝업이 막힌 경우: 마지막 폴백
            alert('브라우저 팝업이 차단되었습니다. URL이 복사됩니다.');
            navigator.clipboard.writeText(target);
        }
    };
    
    window.shareToFacebook = function() {
        const currentUrl = encodeURIComponent(window.location.href);
        window.open('https://www.facebook.com/sharer/sharer.php?u=' + currentUrl, 'group_buying_facebook', 'width=626,height=436');
    };
    
    window.shareToTwitter = function() {
        const currentUrl = encodeURIComponent(window.location.href);
        const text = encodeURIComponent('공동구매 특별 가격으로 구매하세요! ' + document.title);
        window.open('https://twitter.com/intent/tweet?text=' + text + '&url=' + currentUrl, 'group_buying_x', 'width=626,height=436');
    };
    
    window.copyShareLink = function() {
        const currentUrl = window.location.href;
        
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(currentUrl)
                .then(() => window.showCopyAlert())
                .catch(err => {
                    window.fallbackCopyTextToClipboard(currentUrl);
                });
        } else {
            window.fallbackCopyTextToClipboard(currentUrl);
        }
    };
    
    window.fallbackCopyTextToClipboard = function(text) {
        const textArea = document.createElement('textarea');
        textArea.value = text;
        textArea.style.position = 'fixed';
        textArea.style.left = '-9999px';
        textArea.style.top = '-9999px';
        document.body.appendChild(textArea);
        textArea.focus();
        textArea.select();
        
        try {
            const successful = document.execCommand('copy');
            if (successful) {
                window.showCopyAlert();
            } else {
                alert('링크 복사에 실패했습니다. 직접 URL을 복사해주세요.');
            }
        } catch (err) {
            alert('링크 복사에 실패했습니다. 직접 URL을 복사해주세요.');
        }
        
        document.body.removeChild(textArea);
    };
    
    window.showCopyAlert = function() {
        const $alert = $('.gb-share-copy-alert');
        $alert.fadeIn(200);
        
        setTimeout(() => {
            $alert.fadeOut(200);
        }, 2000);
    };
    
    $(document).ready(function() {
        // 현재 상품 번호 가져오기
        const currentProductNo = getProductNo();
        
        if (!currentProductNo) {
            console.error('상품 번호를 찾을 수 없습니다.');
            return;
        }
        
        // 스타일 추가
        let style = document.createElement('style');
        style.innerHTML = `
            :root{
                --gb-point-color: #0238C7;
                --gb-secondary-color: #F5F7FF;
                --gb-text-color: #333333;
                --gb-light-text: #666666;
                --gb-border-color: #e5e5e5;
                --gb-success-color: #34C759;
                --gb-danger-color: #FF3B30;
                --gb-warning-color: #FF9500;
                --gb-radius: 8px;
                --gb-shadow: 0 4px 12px rgba(0,0,0,0.08);
            }
            
            .group-buying-detail {
                font-family: 'Noto Sans KR', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
                margin: 30px 0;
                padding: 25px;
                box-sizing: border-box;
                border-radius: var(--gb-radius);
                background-color: #fff;
                border: 1px solid var(--gb-border-color);
                box-shadow: var(--gb-shadow);
            }
            
            .group-buying-detail * {
                box-sizing: border-box;
            }
            
            .gb-detail-header {
                display: flex;
                align-items: center;
                margin-bottom: 20px;
            }
            
            .gb-badge-large {
                background-color: var(--gb-point-color);
                color: white;
                font-size: 14px;
                font-weight: 600;
                padding: 6px 14px;
                border-radius: 20px;
                margin-right: 12px;
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            }
            
            .gb-detail-title {
                font-size: 18px;
                font-weight: 700;
                color: var(--gb-text-color);
                margin: 0;
                flex: 1;
            }
            
            .gb-price-section {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 15px 0;
                border-top: 1px solid var(--gb-border-color);
                border-bottom: 1px solid var(--gb-border-color);
                margin-bottom: 20px;
            }
            
            .gb-prices {
                display: flex;
                flex-direction: column;
            }
            
            .gb-original-price {
                font-size: 15px;
                color: var(--gb-light-text);
                text-decoration: line-through;
                margin-bottom: 5px;
            }
            
            .gb-discount-price {
                font-size: 22px;
                font-weight: 700;
                color: var(--gb-text-color);
            }
            
            .gb-discount-rate {
                font-size: 20px;
                font-weight: 700;
                color: white;
                background-color: var(--gb-danger-color);
                padding: 8px 15px;
                border-radius: var(--gb-radius);
            }
            
            .gb-detail-info-box {
                background-color: var(--gb-secondary-color);
                border-radius: var(--gb-radius);
                padding: 20px;
                margin-bottom: 20px;
            }
            
            .gb-info-row {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 15px;
            }
            
            .gb-info-label {
                font-size: 15px;
                color: var(--gb-light-text);
            }
            
            .gb-info-value {
                font-size: 16px;
                font-weight: 600;
                color: var(--gb-text-color);
            }
            
            .gb-timer-large {
                display: flex;
                justify-content: center;
                align-items: center;
                padding: 15px;
                background-color: #fff;
                border-radius: var(--gb-radius);
                margin-bottom: 20px;
                border: 1px solid var(--gb-point-color);
            }
            
            .gb-timer-icon {
                margin-right: 10px;
                color: var(--gb-point-color);
                font-size: 18px;
            }
            
            .gb-timer-label {
                font-size: 15px;
                color: var(--gb-text-color);
                margin-right: 10px;
            }
            
            .gb-timer-time {
                font-size: 18px;
                font-weight: 700;
                color: var(--gb-point-color);
            }
            
            .gb-progress-section {
                margin-bottom: 20px;
            }
            
            .gb-progress-header {
                display: flex;
                justify-content: space-between;
                margin-bottom: 10px;
            }
            
            .gb-progress-title {
                font-size: 16px;
                font-weight: 600;
                color: var(--gb-text-color);
                margin: 0;
            }
            
            .gb-progress-status {
                font-size: 14px;
                color: var(--gb-light-text);
            }
            
            .gb-progress-status.success {
                color: var(--gb-success-color);
                font-weight: 600;
            }
            
            .gb-quantity-info {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 10px;
            }
            
            .gb-quantity-item {
                display: flex;
                flex-direction: column;
                align-items: center;
            }
            
            .gb-quantity-badge {
                display: inline-block;
                padding: 4px 10px;
                border-radius: 15px;
                font-size: 12px;
                font-weight: 600;
                margin-bottom: 5px;
            }
            
            .gb-min-quantity {
                background-color: var(--gb-warning-color);
                color: white;
            }
            
            .gb-current-quantity {
                background-color: var(--gb-point-color);
                color: white;
            }
            
            .gb-max-quantity {
                background-color: var(--gb-secondary-color);
                color: var(--gb-point-color);
                border: 1px solid var(--gb-point-color);
            }
            
            .gb-quantity-value {
                font-size: 16px;
                font-weight: 700;
                color: var(--gb-text-color);
            }
            
            .gb-progress-bar {
                width: 100%;
                height: 12px;
                background-color: var(--gb-border-color);
                border-radius: 6px;
                overflow: hidden;
                position: relative;
                margin-bottom: 8px;
            }
            
            .gb-progress-value {
                height: 100%;
                background-color: var(--gb-point-color);
                border-radius: 6px;
                transition: width 0.5s ease;
            }
            
            .gb-progress-min-marker {
                position: absolute;
                top: 0;
                bottom: 0;
                width: 2px;
                background-color: var(--gb-warning-color);
                z-index: 2;
            }
            
            .gb-progress-min-label {
                position: absolute;
                top: -20px;
                transform: translateX(-50%);
                background-color: var(--gb-warning-color);
                color: white;
                font-size: 11px;
                padding: 2px 6px;
                border-radius: 3px;
                white-space: nowrap;
            }
            
            .gb-note {
                font-size: 14px;
                color: var(--gb-light-text);
                line-height: 1.5;
                margin-bottom: 20px;
            }
            
            .gb-note strong {
                color: var(--gb-danger-color);
                font-weight: 600;
            }
            
            .gb-button-area {
                display: flex;
                gap: 10px;
            }
            
            .gb-button {
                flex: 1;
                padding: 15px 0;
                text-align: center;
                background-color: var(--gb-point-color);
                color: white;
                font-size: 16px;
                font-weight: 600;
                border-radius: var(--gb-radius);
                text-decoration: none;
                transition: all 0.3s;
                border: none;
                cursor: pointer;
            }
            
            .gb-button:hover {
                background-color: #0229a3;
                transform: translateY(-2px);
                box-shadow: 0 4px 8px rgba(2, 56, 199, 0.2);
            }
            
            .gb-button.disabled {
                background-color: #999;
                cursor: not-allowed;
                transform: none;
                box-shadow: none;
            }
            
            .gb-share-button {
                width: 100%;
                background-color: #fff;
                color: var(--gb-point-color);
                border: 1px solid var(--gb-point-color);
                padding: 15px 25px;
                flex: 0 0 auto;
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 10px;
                position: relative;
                transition: all 0.3s ease;
                font-weight: 600;
                letter-spacing: 0.3px;
                overflow: hidden;
                box-shadow: 0 2px 4px rgba(2, 56, 199, 0.05);
            }
            
            .gb-share-button:hover {
                background-color: var(--gb-secondary-color);
                color: var(--gb-point-color);
                box-shadow: 0 4px 12px rgba(2, 56, 199, 0.15);
                transform: translateY(-2px);
            }
            
            .gb-share-button:active {
                transform: translateY(0);
                box-shadow: 0 2px 4px rgba(2, 56, 199, 0.1);
            }
            
            /* SNS 공유하기 관련 스타일 */
            .gb-share-modal {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background-color: rgba(0, 0, 0, 0.5);
                z-index: 9999;
                align-items: center;
                justify-content: center;
            }
            
            .gb-share-modal-content {
                background-color: #fff;
                border-radius: var(--gb-radius);
                padding: 25px;
                width: 320px;
                max-width: 90%;
                box-shadow: var(--gb-shadow);
                position: relative;
            }
            
            .gb-share-modal-title {
                font-size: 18px;
                font-weight: 700;
                margin: 0 0 20px 0;
                text-align: center;
                color: var(--gb-text-color);
            }
            
            .gb-share-close {
                position: absolute;
                top: 15px;
                right: 15px;
                width: 24px;
                height: 24px;
                cursor: pointer;
                color: var(--gb-light-text);
                font-size: 24px;
                line-height: 1;
                text-align: center;
            }
            
            .gb-share-options {
                display: flex;
                flex-wrap: wrap;
                gap: 15px;
                justify-content: center;
                margin-bottom: 20px;
            }
            
            .gb-share-option {
                display: flex;
                flex-direction: column;
                align-items: center;
                gap: 8px;
                width: 60px;
                cursor: pointer;
                transition: transform 0.2s;
            }
            
            .gb-share-option:hover {
                transform: translateY(-3px);
            }
            
            .gb-share-icon {
                width: 50px;
                height: 50px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                color: white;
                font-size: 20px;
            }
            
            .gb-share-kakao .gb-share-icon {
                background-color: #FEE500;
                color: #000000;
            }
            
            .gb-share-facebook .gb-share-icon {
                background-color: #1877F2;
            }
            
            .gb-share-twitter .gb-share-icon {
                background-color: #576c79;
            }
            
            .gb-share-link .gb-share-icon {
                background-color: #4CAF50;
            }
            
            .gb-share-option-text {
                font-size: 12px;
                color: var(--gb-text-color);
                text-align: center;
            }
            
            .gb-share-copy-alert {
                display: none;
                position: fixed;
                bottom: 20px;
                left: 50%;
                transform: translateX(-50%);
                background-color: var(--gb-point-color);
                color: white;
                padding: 10px 20px;
                border-radius: 20px;
                box-shadow: var(--gb-shadow);
                font-size: 14px;
                z-index: 9999;
            }
            
            /* 테마 스타일 */
            .group-buying-detail.style1 {
                --gb-point-color: #0238C7;
                --gb-secondary-color: #F5F7FF;
            }
            
            .group-buying-detail.style2 {
                --gb-point-color: #FF6B6B;
                --gb-secondary-color: #FFF0F0;
            }
            
            .group-buying-detail.style3 {
                --gb-point-color: #41B979;
                --gb-secondary-color: #F0FFF5;
            }
            
            .group-buying-detail.style4 {
                --gb-point-color: #8C6FFF;
                --gb-secondary-color: #F5F0FF;
            }
            
            .group-buying-detail.style5 {
                --gb-point-color: #FF9500;
                --gb-secondary-color: #FFF8E8;
            }
            
            /* 반응형 미디어 쿼리 */
            @media screen and (max-width: 768px) {
                .group-buying-detail {
                    padding: 15px;
                    margin: 20px 0;
                }
                
                .gb-detail-header {
                    flex-direction: column;
                    align-items: flex-start;
                }
                
                .gb-badge-large {
                    margin-bottom: 10px;
                }
                
                .gb-detail-title {
                    font-size: 16px;
                }
                
                .gb-price-section {
                    flex-direction: column;
                    align-items: flex-start;
                    gap: 10px;
                }
                
                .gb-discount-price {
                    font-size: 18px;
                }
                
                .gb-discount-rate {
                    font-size: 16px;
                }
                
                .gb-button-area {
                    flex-direction: column;
                }
                
                .gb-button, .gb-share-button {
                    width: 100%;
                }
                
                .gb-share-button {
                    padding: 12px 20px;
                }
                
                .gb-share-button:before {
                    width: 16px;
                    height: 16px;
                }
            }
        `;
        document.head.appendChild(style);
        
        // 공동구매 정보 가져오기
        $.ajax({
            url: 'https://app.weaverloft.com/group_buying/fo/group_buying_detail',
            type: 'POST',
            dataType: 'json',
            data: JSON.stringify({
                mall_id: scriptTagParams.get('mall_id'),
                token: scriptTagParams.get('token'),
                product_no: currentProductNo
            }),
            success: function(response) {
                if (!response || response.status === 'error') {
                    console.log('공동구매 데이터가 없습니다.');
                    return false;
                }
                
                const groupBuying = response.data;
                renderGroupBuyingDetail(groupBuying);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error('공동구매 데이터 로드 실패:', textStatus, errorThrown);
            }
        });
        
        // 현재 상품 번호 찾기 함수
        function getProductNo() {
            // URL에서 상품 번호 추출 (카페24 기본 형식: /product/detail.html?product_no=123)
            const urlParams = new URLSearchParams(window.location.search);
            const productNo = urlParams.get('product_no');
            
            if (productNo) {
                return productNo;
            }
            
            // 페이지에서 상품 번호 찾기 (카페24에서 제공하는 전역 변수나 메타 태그)
            if (typeof iProductNo !== 'undefined') {
                return iProductNo;
            }
            
            // 다른 DOM 요소에서 찾기
            const productInput = document.querySelector('input[name="product_no"]');
            if (productInput) {
                return productInput.value;
            }
            
            return null;
        }
        
        // 공동구매 상세 정보 렌더링 함수
        function renderGroupBuyingDetail(groupBuying) {
            const targetSelector = decodeURIComponent(scriptTagParams.get('selector'));
            const insertMethod = scriptTagParams.get('method') || 'html';
            
            if (!targetSelector) {
                console.error('대상 선택자가 지정되지 않았습니다.');
                return false;
            }
            
            // 할인율 계산
            const discountRate = Math.round((groupBuying.org_price - groupBuying.event_price) / groupBuying.org_price * 100);
            
            // 현재 주문 수량 계산
            const currentQuantity = groupBuying.current_quantity || 0;
            
            // 최소/최대 수량 및 진행 상태
            const minQuantity = parseInt(groupBuying.min_quantity);
            const maxQuantity = parseInt(groupBuying.max_quantity);
            const progressPercent = maxQuantity > 0 ? Math.min(100, Math.round((currentQuantity / maxQuantity) * 100)) : 0;
            const isSuccess = currentQuantity >= minQuantity;
            
            // 남은 시간 계산
            const now = new Date();
            const endDate = new Date(groupBuying.end_dt);
            const diff = endDate - now;
            
            // 공동구매 종료 여부
            const isEnded = diff <= 0;
            
            // HTML 생성
            let html = '<div class="group-buying-detail">';
            
            // 헤더 영역
            html += '<div class="gb-detail-header">';
            html += '<span class="gb-badge-large">공동구매</span>';
            html += '<h3 class="gb-detail-title">이벤트 특가 진행중!</h3>';
            html += '</div>';
            
            // 가격 영역
            html += '<div class="gb-price-section">';
            html += '<div class="gb-prices">';
            html += '<span class="gb-original-price">' + number_format(groupBuying.org_price) + '원</span>';
            html += '<span class="gb-discount-price">' + number_format(groupBuying.event_price) + '원</span>';
            html += '</div>';
            html += '<span class="gb-discount-rate">' + discountRate + '%</span>';
            html += '</div>';
            
            // 타이머 영역
            html += '<div class="gb-timer-large">';
            html += '<span class="gb-timer-icon"><i class="fas fa-clock"></i></span>';
            html += '<span class="gb-timer-label">남은 시간 :</span>';
            
            if (isEnded) {
                html += '<span class="gb-timer-time">종료됨</span>';
            } else {
                const days = Math.floor(diff / (1000 * 60 * 60 * 24));
                const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((diff % (1000 * 60)) / 1000);
                
                let timeText = '';
                if (days > 0) timeText += days + '일 ';
                timeText += String(hours).padStart(2, '0') + ':' + 
                            String(minutes).padStart(2, '0') + ':' + 
                            String(seconds).padStart(2, '0');
                
                html += '<span class="gb-timer-time" data-end-time="' + groupBuying.end_dt + '">' + timeText + '</span>';
            }
            html += '</div>';
            
            // 진행 상태 영역
            html += '<div class="gb-progress-section">';
            html += '<div class="gb-progress-header">';
            html += '<h4 class="gb-progress-title">공동구매 진행 상태</h4>';
            
            if (isSuccess) {
                html += '<p class="gb-progress-status success">최소 수량 달성 완료!</p>';
            } else {
                const remainingForMin = minQuantity - currentQuantity;
                html += '<p class="gb-progress-status">앞으로 ' + remainingForMin + '개 더 필요해요</p>';
            }
            
            html += '</div>';
            
            // 수량 정보
            html += '<div class="gb-quantity-info">';
            html += '<div class="gb-quantity-item">';
            html += '<span class="gb-quantity-badge gb-min-quantity">최소</span>';
            html += '<span class="gb-quantity-value">' + minQuantity + '개</span>';
            html += '</div>';
            
            html += '<div class="gb-quantity-item">';
            html += '<span class="gb-quantity-badge gb-current-quantity">현재</span>';
            html += '<span class="gb-quantity-value">' + currentQuantity + '개</span>';
            html += '</div>';
            
            html += '<div class="gb-quantity-item">';
            html += '<span class="gb-quantity-badge gb-max-quantity">최대</span>';
            html += '<span class="gb-quantity-value">' + maxQuantity + '개</span>';
            html += '</div>';
            html += '</div>';
            
            // 진행 바
            html += '<div class="gb-progress-bar">';
            
            // 최소 달성량 마커
            const minPercent = maxQuantity > 0 ? Math.min(100, Math.round((minQuantity / maxQuantity) * 100)) : 0;
            html += '<div class="gb-progress-min-marker" data-min-quantity="' + minQuantity + '" data-max-quantity="' + maxQuantity + '" style="left: ' + minPercent + '%;">';
            html += '<div class="gb-progress-min-label">최소</div>';
            html += '</div>';
            
            // 진행 상태에 따른 색상 조정
            let progressColor = 'var(--gb-point-color)';
            if (isSuccess) {
                progressColor = 'var(--gb-success-color)';
            } else if (currentQuantity < minQuantity && progressPercent > 0) {
                progressColor = 'var(--gb-warning-color)';
            }
            
            html += '<div class="gb-progress-value" style="width: ' + progressPercent + '%; background-color: ' + progressColor + ';"></div>';
            html += '</div>';
            html += '</div>';
            
            // 공동구매 안내 메시지
            html += '<div class="gb-note">';
            if (isEnded) {
                html += '공동구매가 <strong>종료</strong>되었습니다.';
            } else if (isSuccess) {
                html += '공동구매 최소 수량을 달성했습니다! <strong>주문 시 구매가 확정</strong>됩니다.';
            } else {
                html += '공동구매는 지정된 최소 수량에 도달해야 <strong>구매가 확정</strong>됩니다.';
            }
            html += '</div>';
            
            // 버튼 영역
            html += '<div class="gb-button-area">';
            
            if (isEnded) {
                html += '<button class="gb-button disabled">공동구매 종료</button>';
            } else {
                html += '<button class="gb-button gb-share-button" id="gb-share-button">이벤트 공유하기</button>';
            }
            
            html += '</div>';
            
            html += '</div>';
            
            // HTML 삽입
            $(targetSelector)[insertMethod](html);
            
            // 공유 모달 추가
            let shareModalHTML = `
                <div class="gb-share-modal">
                    <div class="gb-share-modal-content">
                        <span class="gb-share-close">&times;</span>
                        <h3 class="gb-share-modal-title">공유하기</h3>
                        <div class="gb-share-options">
                            <div class="gb-share-option gb-share-kakao" id="gb-kakao-share">
                                <div class="gb-share-icon">
                                    <img src="https://developers.kakao.com/assets/img/about/logos/kakaolink/kakaolink_btn_small.png" width="30" height="30">
                                </div>
                                <span class="gb-share-option-text">카카오톡</span>
                            </div>
                            <div class="gb-share-option gb-share-facebook" id="gb-facebook-share">
                                <div class="gb-share-icon">
                                    <i class="fab fa-facebook-f"></i>
                                </div>
                                <span class="gb-share-option-text">페이스북</span>
                            </div>
                            <div class="gb-share-option gb-share-twitter" id="gb-twitter-share">
                                <div class="gb-share-icon">
                                    <i class="fa-brands fa-x-twitter"></i>
                                </div>
                                <span class="gb-share-option-text">트위터</span>
                            </div>
                            <div class="gb-share-option gb-share-link" id="gb-link-share">
                                <div class="gb-share-icon">
                                    <i class="fas fa-link"></i>
                                </div>
                                <span class="gb-share-option-text">링크 복사</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="gb-share-copy-alert">링크가 복사되었습니다</div>
            `;
            $('body').append(shareModalHTML);
            
            // 폰트어썸 로드
            if (!document.getElementById('font-awesome')) {
                const fontAwesome = document.createElement('link');
                fontAwesome.id = 'font-awesome';
                fontAwesome.rel = 'stylesheet';
                fontAwesome.href = 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css';
                document.head.appendChild(fontAwesome);
            }
            
            // 카카오 SDK 추가
            if (!document.getElementById('kakao-sdk')) {
                const kakaoScript = document.createElement('script');
                kakaoScript.id = 'kakao-sdk';
                kakaoScript.src = 'https://t1.kakaocdn.net/kakao_js_sdk/2.7.2/kakao.min.js';
                kakaoScript.integrity = 'sha384-TiCUE00h649CAMonG018J2ujOgDKW/kVWlChEuu4jK2vxfAAD0eZxzCKakxg55G4';
                kakaoScript.crossOrigin = 'anonymous';
                document.head.appendChild(kakaoScript);
                
                // 카카오 SDK 초기화
                kakaoScript.onload = function() {
                    if (window.Kakao && !window.Kakao.isInitialized()) {
                        window.Kakao.init('8d10d5c0b9eaaa36d6594aa6b7f9d02e');
                    }
                };
            }
            
            // 테마 적용
            const themeNumber = scriptTagParams.get('theme') || '1';
            $(".group-buying-detail").addClass("style" + themeNumber);
            
            // 타이머 시작
            if (!isEnded) {
                updateTimer();
                setInterval(updateTimer, 1000);
            }
            
            // 진행 바 애니메이션
            setTimeout(() => {
                animateProgressBar();
            }, 300);
        }
        
        // 타이머 업데이트 함수
        function updateTimer() {
            const $timer = $('.gb-timer-time');
            if ($timer.length === 0) return;
            
            const endTime = $timer.data('end-time');
            if (!endTime) return;
            
            const now = new Date();
            const endDate = new Date(endTime);
            const diff = endDate - now;
            
            if (diff <= 0) {
                $timer.text('종료됨');
                $('.gb-button').addClass('disabled').text('공동구매 종료').css({
                    'background-color': '#999',
                    'cursor': 'not-allowed'
                });
                return;
            }
            
            const days = Math.floor(diff / (1000 * 60 * 60 * 24));
            const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((diff % (1000 * 60)) / 1000);
            
            let timeText = '';
            if (days > 0) timeText += days + '일 ';
            timeText += String(hours).padStart(2, '0') + ':' + 
                    String(minutes).padStart(2, '0') + ':' + 
                    String(seconds).padStart(2, '0');
            
            $timer.text(timeText);
            
            // 24시간 이내일 때 강조 효과
            if (diff < 24 * 60 * 60 * 1000) {
                $timer.css('color', 'var(--gb-danger-color)');
                $('.gb-timer-large').css({
                    'border': '1px solid var(--gb-danger-color)',
                    'background-color': 'rgba(255, 59, 48, 0.05)'
                });
            }
        }
        
        // 진행 바 애니메이션
        function animateProgressBar() {
            const $bar = $('.gb-progress-value');
            const width = $bar.css('width');
            
            $bar.css('width', '0');
            setTimeout(() => {
                $bar.css({
                    'width': width,
                    'transition': 'width 1s ease-in-out'
                });
            }, 100);
        }
        
        // 숫자 포맷팅 함수
        function number_format(value) {
            if (value === null || value === undefined) return '';
            const numericValue = String(value).replace(/,/g, '');
            if (numericValue === '-') return '-';
            if (isNaN(numericValue) || numericValue === '') return '';
          
            const maxNumber = 9007199254740991;
            let number = parseFloat(numericValue);
          
            if (number > maxNumber) number = maxNumber;
            if (number < -maxNumber) number = -maxNumber;
          
            return number.toLocaleString('en');
        }

        // 초기화 함수 호출
        initShareModal();
    });
})();