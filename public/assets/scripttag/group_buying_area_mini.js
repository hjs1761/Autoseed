(function() {
    let scriptTag = document.currentScript;
    let scriptTagParams = new URLSearchParams(scriptTag.src.split('?')[1]);
    $(document).ready(function() {
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
            
            .group-buying-area {
                font-family: 'Noto Sans KR', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
                margin: 40px 0;
                padding: 0;
                box-sizing: border-box;
                max-width: 1200px;
                margin-left: auto;
                margin-right: auto;
            }
            
            .group-buying-area * {
                box-sizing: border-box;
            }
            
            .group-buying-title {
                font-size: 22px;
                font-weight: 700;
                color: var(--gb-text-color);
                margin-bottom: 24px;
                text-align: center;
                position: relative;
            }
            
            .group-buying-title:after {
                content: '';
                display: block;
                width: 40px;
                height: 3px;
                background-color: var(--gb-point-color);
                margin: 12px auto 0;
            }
            
            .group-buying-list {
                display: grid;
                grid-template-columns: repeat(4, 1fr);
                grid-gap: 20px;
                margin: 0;
                padding: 0;
                list-style: none;
            }
            
            .group-buying-item {
                border-radius: var(--gb-radius);
                background-color: #fff;
                box-shadow: var(--gb-shadow);
                overflow: hidden;
                transition: all 0.3s ease;
                position: relative;
                border: 1px solid var(--gb-border-color);
            }
            
            .group-buying-item:hover {
                transform: translateY(-5px);
                box-shadow: 0 8px 16px rgba(0,0,0,0.12);
            }
            
            .gb-product-image {
                position: relative;
                width: 100%;
                padding-top: 100%;
                overflow: hidden;
                background-color: #f5f5f5;
            }
            
            .gb-product-image img {
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                object-fit: cover;
                transition: transform 0.5s ease;
            }
            
            .group-buying-item:hover .gb-product-image img {
                transform: scale(1.05);
            }
            
            .gb-badge {
                position: absolute;
                top: 12px;
                left: 12px;
                background-color: var(--gb-point-color);
                color: white;
                font-size: 12px;
                font-weight: 600;
                padding: 4px 10px;
                border-radius: 20px;
                z-index: 1;
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            }
            
            .gb-sale-badge {
                position: absolute;
                top: 12px;
                right: 12px;
                background-color: var(--gb-danger-color);
                color: white;
                font-size: 14px;
                font-weight: 700;
                padding: 6px 10px;
                border-radius: 20px;
                z-index: 1;
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            }
            
            .gb-product-info {
                padding: 16px;
            }
            
            .gb-product-name {
                font-size: 15px;
                font-weight: 600;
                color: var(--gb-text-color);
                margin: 0 0 12px 0;
                overflow: hidden;
                text-overflow: ellipsis;
                display: -webkit-box;
                -webkit-line-clamp: 2;
                -webkit-box-orient: vertical;
                line-height: 1.4;
                height: 42px;
            }
            
            .gb-product-price {
                display: flex;
                justify-content: space-between;
                align-items: baseline;
                margin-bottom: 14px;
            }
            
            .gb-price-container {
                display: flex;
                flex-direction: column;
            }
            
            .gb-original-price {
                font-size: 13px;
                color: var(--gb-light-text);
                text-decoration: line-through;
                margin-bottom: 4px;
            }
            
            .gb-discount-price {
                font-size: 18px;
                font-weight: 700;
                color: var(--gb-text-color);
            }
            
            .gb-discount-rate {
                font-size: 16px;
                font-weight: 700;
                color: var(--gb-danger-color);
            }
            
            .time-box {
                display: none;
                background-color: var(--gb-secondary-color);
                border-radius: var(--gb-radius);
                padding: 12px;
                margin-top: 12px;
            }
            
            .gb-progress-container {
                margin-bottom: 12px;
            }
            
            .gb-progress-label {
                display: flex;
                justify-content: space-between;
                font-size: 12px;
                color: var(--gb-light-text);
                margin-bottom: 6px;
            }
            
            .gb-progress-bar {
                width: 100%;
                height: 8px;
                background-color: var(--gb-border-color);
                border-radius: 4px;
                overflow: hidden;
                position: relative;
            }
            
            .gb-progress-value {
                height: 100%;
                background-color: var(--gb-point-color);
                border-radius: 4px;
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
                font-size: 10px;
                padding: 2px 4px;
                border-radius: 2px;
                white-space: nowrap;
            }
            
            .gb-quantity-info {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 8px;
            }
            
            .gb-quantity-badge {
                display: inline-block;
                padding: 2px 6px;
                border-radius: 10px;
                font-size: 11px;
                font-weight: 600;
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
            
            .gb-timer {
                display: flex;
                justify-content: center;
                align-items: center;
                font-size: 14px;
                color: var(--gb-text-color);
                background-color: rgba(255,255,255,0.6);
                padding: 6px 0;
                border-radius: 4px;
            }
            
            .gb-timer-icon {
                margin-right: 8px;
                color: var(--gb-point-color);
            }
            
            .gb-timer-time {
                font-weight: 700;
                color: var(--gb-point-color);
            }
            
            .gb-button {
                display: block;
                width: 100%;
                padding: 12px 0;
                margin-top: 14px;
                text-align: center;
                background-color: var(--gb-point-color);
                color: white;
                font-size: 15px;
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
            
            /* 테마 스타일 */
            .group-buying-area.style1 {
                --gb-point-color: #0238C7;
                --gb-secondary-color: #F5F7FF;
            }
            
            .group-buying-area.style2 {
                --gb-point-color: #FF6B6B;
                --gb-secondary-color: #FFF0F0;
            }
            
            .group-buying-area.style3 {
                --gb-point-color: #41B979;
                --gb-secondary-color: #F0FFF5;
            }
            
            .group-buying-area.style4 {
                --gb-point-color: #8C6FFF;
                --gb-secondary-color: #F5F0FF;
            }
            
            .group-buying-area.style5 {
                --gb-point-color: #FF9500;
                --gb-secondary-color: #FFF8E8;
            }
            
            /* 반응형 미디어 쿼리 */
            @media screen and (max-width: 1200px) {
                .group-buying-list {
                    grid-template-columns: repeat(3, 1fr);
                    grid-gap: 20px;
                }
                
                .group-buying-title {
                    font-size: 20px;
                }
            }
            
            @media screen and (max-width: 992px) {
                .group-buying-list {
                    grid-template-columns: repeat(2, 1fr);
                    grid-gap: 20px;
                }
                
                .group-buying-title {
                    font-size: 18px;
                }
            }
            
            @media screen and (max-width: 768px) {
                .group-buying-area {
                    margin: 30px 10px;
                }
                
                .group-buying-list {
                    grid-template-columns: repeat(2, 1fr);
                    grid-gap: 15px;
                }
                
                .group-buying-title {
                    font-size: 16px;
                    margin-bottom: 16px;
                }
                
                .gb-product-name {
                    font-size: 14px;
                    height: 40px;
                    margin-bottom: 8px;
                }
                
                .gb-discount-price {
                    font-size: 16px;
                }
                
                .gb-discount-rate {
                    font-size: 14px;
                }
                
                .time-box {
                    padding: 10px;
                }
                
                .gb-button {
                    padding: 10px 0;
                    font-size: 14px;
                }
            }
            
            @media screen and (max-width: 480px) {
                .group-buying-list {
                    grid-template-columns: repeat(1, 1fr);
                    grid-gap: 16px;
                }
                
                .gb-product-info {
                    padding: 14px;
                }
                
                .group-buying-title {
                    font-size: 16px;
                }
                
                .gb-product-image {
                    padding-top: 80%;
                }
            }
        `;
        document.head.appendChild(style);

        $.ajax({
            url: 'https://app.weaverloft.com/group_buying/fo/group_buying_list',
            type: 'POST',
            dataType: 'json',
            data: JSON.stringify({
                mall_id: scriptTagParams.get('mall_id'),
                token: scriptTagParams.get('token')
            }),
            success: function(response) {
                if (response.status !== 'success' || !response.data || response.data.length === 0) {
                    console.log('공동구매 데이터가 없습니다.');
                    return false;
                }

                const targetSelector = decodeURIComponent(scriptTagParams.get('selector'));
                const insertMethod = scriptTagParams.get('method') || 'html';
                
                if (!targetSelector) {
                    console.error('대상 선택자가 지정되지 않았습니다.');
                    return false;
                }
                
                // HTML 생성
                let html = '<div class="group-buying-area">';
                html += '<h3 class="group-buying-title">공동구매 특가!</h3>';
                html += '<ul class="group-buying-list">';
                
                // 각 공동구매 아이템에 대한 HTML 생성
                response.data.forEach(function(item) {
                    // 할인율 계산
                    const discountRate = Math.round((item.org_price - item.event_price) / item.org_price * 100);
                    
                    // 남은 시간 계산
                    const now = new Date();
                    const end = new Date(item.end_dt);
                    const diff = end - now;
                    
                    if (diff <= 0) return; // 종료된 공동구매는 표시하지 않음
                    
                    const days = Math.floor(diff / (1000 * 60 * 60 * 24));
                    const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                    const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
                    const seconds = Math.floor((diff % (1000 * 60)) / 1000);
                    
                    // 시간 텍스트 생성
                    let timeText = '';
                    if (days > 0) timeText += days + '일 ';
                    timeText += String(hours).padStart(2, '0') + ':' + 
                            String(minutes).padStart(2, '0') + ':' + 
                            String(seconds).padStart(2, '0');
                    
                    // 진행률 및 상태 계산
                    const currentQuantity = item.current_quantity;
                    const minQuantity = parseInt(item.min_quantity);
                    const maxQuantity = parseInt(item.max_quantity);
                    const progressPercent = maxQuantity > 0 ? Math.min(100, Math.round((currentQuantity / maxQuantity) * 100)) : 0;
                    const isSuccess = currentQuantity >= minQuantity;
                    const remainingForMin = minQuantity - currentQuantity;
                    
                    // 진행 상태에 따른 색상 조정
                    let progressColor = 'var(--gb-point-color)';
                    if (isSuccess) {
                        progressColor = 'var(--gb-success-color)';
                    } else if (currentQuantity < minQuantity && progressPercent > 0) {
                        progressColor = 'var(--gb-warning-color)';
                    }
                    
                    // 최소 달성량 퍼센트 계산
                    const minPercent = maxQuantity > 0 ? Math.min(100, Math.round((minQuantity / maxQuantity) * 100)) : 0;
                    
                    // 아이템 HTML 생성
                    html += '<li class="group-buying-item">';
                    html += '<div class="gb-product-image">';
                    html += '<span class="gb-badge">공동구매</span>';
                    html += '<img src="' + item.image_url + '" alt="' + item.product_name + '">';
                    html += '</div>';
                    html += '<div class="gb-product-info">';
                    html += '<h4 class="gb-product-name">' + item.product_name + '</h4>';
                    html += '<div class="gb-product-price">';
                    html += '<div class="gb-price-container">';
                    html += '<span class="gb-original-price">' + number_format(item.org_price) + '원</span>';
                    html += '<span class="gb-discount-price">' + number_format(item.event_price) + '원</span>';
                    html += '</div>';
                    html += '<span class="gb-discount-rate">' + discountRate + '%</span>';
                    html += '</div>';
                    
                    // 타이머와 진행 상태
                    html += '<div class="time-box">';
                    html += '<div class="gb-progress-container">';
                    
                    html += '<div class="gb-progress-label">';
                    
                    // 공동구매 진행 상태 텍스트
                    if (isSuccess) {
                        html += '<span><strong>달성 성공!</strong> 구매가 확정됩니다</span>';
                    } else {
                        html += '<span>앞으로 <strong>' + remainingForMin + '개</strong> 더 필요해요</span>';
                    }
                    
                    // 주문 수량 정보
                    html += '<span>현재 ';
                    html += currentQuantity + '/' + maxQuantity + '개</span>';
                    html += '</div>';
                    
                    // 수량 표시 UI
                    html += '<div class="gb-quantity-info">';
                    html += '<span><span class="gb-quantity-badge gb-min-quantity">최소</span> ' + minQuantity + '개</span>';
                    html += '<span><span class="gb-quantity-badge gb-current-quantity">현재</span> ' + currentQuantity + '개</span>';
                    html += '<span><span class="gb-quantity-badge gb-max-quantity">최대</span> ' + maxQuantity + '개</span>';
                    html += '</div>';
                    
                    // 진행 바
                    html += '<div class="gb-progress-bar">';
                    // 최소 달성량 마커
                    html += '<div class="gb-progress-min-marker" data-min-quantity="' + minQuantity + '" data-max-quantity="' + maxQuantity + '">';
                    html += '<div class="gb-progress-min-label">최소</div>';
                    html += '</div>';
                    
                    html += '<div class="gb-progress-value" style="width: ' + progressPercent + '%; background-color: ' + progressColor + ';"></div>';
                    html += '</div>';
                    html += '</div>';
                    
                    // 타이머
                    html += '<div class="gb-timer">';
                    html += '<span class="gb-timer-icon"><i class="fa fa-clock-o"></i></span>';
                    html += '<span class="gb-timer-time" data-end-time="' + item.end_dt + '">' + timeText + '</span>';
                    html += '</div>';
                    html += '</div>';
                    
                    // 구매 버튼
                    html += '<a href="' + item.product_url + '" class="gb-button" target="_blank">공동구매 참여하기</a>';
                    html += '</div>';
                    html += '</li>';
                });
                
                html += '</ul>';
                html += '</div>';
                
                // HTML 삽입
                $(targetSelector)[insertMethod](html);
                
                // 테마 적용
                const themeNumber = scriptTagParams.get('theme') || '1';
                $(".group-buying-area").addClass("style" + themeNumber);
                
                // 타이머 기능 추가
                $(".time-box").fadeIn(300);
                updateTimers();
                
                // 반응형 이미지 로드 최적화
                lazyLoadImages();
                
                // 페이지 로드 후 진행바 애니메이션 효과
                animateProgressBars();
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error('공동구매 데이터 로드 실패:', textStatus, errorThrown);
            }
        });
        
        // 타이머 업데이트 함수
        function updateTimers() {
            $('.gb-timer-time').each(function() {
                const endTime = $(this).data('end-time');
                const $timer = $(this);
                const $item = $timer.closest('.group-buying-item');
                
                function updateTimer() {
                    const now = new Date();
                    const endDate = new Date(endTime);
                    const diff = endDate - now;
                    
                    if (diff <= 0) {
                        $timer.text('종료됨');
                        $item.find('.gb-button').addClass('disabled').text('공동구매 종료').css({
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
                        if (!$item.hasClass('urgent')) {
                            $item.addClass('urgent').find('.time-box').css({
                                'border': '1px solid var(--gb-danger-color)',
                                'background-color': 'rgba(255, 59, 48, 0.05)'
                            });
                        }
                    }
                }
                
                updateTimer();
                setInterval(updateTimer, 1000);
            });

            // 최소 달성량 마커 위치 조정
            $('.gb-progress-min-marker').each(function() {
                const $this = $(this);
                const minQuantity = parseInt($this.data('min-quantity'));
                const maxQuantity = parseInt($this.data('max-quantity'));
                
                if (minQuantity > 0 && maxQuantity > 0) {
                    const percentage = (minQuantity / maxQuantity) * 100;
                    $this.css('left', `${percentage}%`);
                    
                    // 최소 달성량 라벨 위치 조정
                    const $label = $this.find('.gb-progress-min-label');
                    $label.css('left', '0');
                    
                    // 라벨이 화면 밖으로 나가지 않도록 조정
                    if (percentage < 10) {
                        $label.css('left', '0');
                        $label.css('transform', 'translateX(0)');
                    } else if (percentage > 90) {
                        $label.css('right', '0');
                        $label.css('left', 'auto');
                        $label.css('transform', 'translateX(0)');
                    }
                }
            });
        }
        
        // 이미지 지연 로딩 함수
        function lazyLoadImages() {
            if ('IntersectionObserver' in window) {
                const imageObserver = new IntersectionObserver((entries, observer) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            const img = entry.target;
                            const src = img.dataset.src;
                            
                            if (src) {
                                img.src = src;
                                img.removeAttribute('data-src');
                                img.classList.add('loaded');
                                observer.unobserve(img);
                            }
                        }
                    });
                }, {
                    rootMargin: '50px 0px',
                    threshold: 0.01
                });
                
                document.querySelectorAll('.gb-product-image img').forEach(img => {
                    if (img.dataset.src) {
                        imageObserver.observe(img);
                    }
                });
            } else {
                // 폴백: IntersectionObserver를 지원하지 않는 브라우저용
                $('.gb-product-image img').each(function() {
                    const img = $(this);
                    const src = img.data('src');
                    
                    if (src) {
                        img.attr('src', src);
                        img.removeAttr('data-src');
                        setTimeout(() => img.addClass('loaded'), 100);
                    }
                });
            }
        }
        
        // 진행 바 애니메이션
        function animateProgressBars() {
            $('.gb-progress-value').each(function() {
                const $bar = $(this);
                const targetWidth = $bar.width();
                
                $bar.width(0);
                setTimeout(() => {
                    $bar.css({
                        'width': targetWidth + 'px',
                        'transition': 'width 1s ease-in-out'
                    });
                }, 300);
            });
        }
        
        // 스크롤 이벤트에 따른 등장 효과
        $(window).on('scroll', function() {
            $('.group-buying-item').each(function() {
                if (isElementInViewport(this) && !$(this).hasClass('appeared')) {
                    $(this).addClass('appeared');
                }
            });
        });
        
        // 요소가 화면에 보이는지 확인하는 함수
        function isElementInViewport(el) {
            const rect = el.getBoundingClientRect();
            return (
                rect.top >= 0 &&
                rect.left >= 0 &&
                rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
                rect.right <= (window.innerWidth || document.documentElement.clientWidth)
            );
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
    });
})();