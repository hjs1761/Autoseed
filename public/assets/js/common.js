/**
 * 공통 전역변수
 */
// 전역에서 활성 팝업을 관리
let activePopup = null;

/**
 * 사이드바 열고 닫기 상태를 localStorage에 보관
 * @param {string} lnbId
 * @param {string} mainId
 * @param {array} toggleBtnIds
 */
function setupLnbToggleWithStorage(lnbId, mainId, toggleBtnId) {
  const lnb = document.getElementById(lnbId);
  const main = document.getElementById(mainId);
  const toggleBtn = document.getElementById(toggleBtnId);
  const topbar = document.getElementById("topbar");
  const wrapper = document.getElementById("wrapper");

  if (!lnb || !main || !toggleBtn || !topbar || !wrapper) return;

  // 초기 상태 복원
  const savedState = localStorage.getItem("lnbState");
  if (savedState === "collapsed") {
    lnb.classList.add("collapsed");
    $(toggleBtn).addClass("open");
    topbar.classList.add("lnb-collapsed");
    wrapper.classList.add("lnb-collapsed");
  } else {
    lnb.classList.remove("collapsed");
    $(toggleBtn).removeClass("open");
    topbar.classList.remove("lnb-collapsed");
    wrapper.classList.remove("lnb-collapsed");
  }

  // 클릭 시 토글
  toggleBtn.addEventListener("click", () => {
    toggleBtn.classList.toggle("open");
    if (lnb.classList.contains("collapsed")) {
      lnb.classList.remove("collapsed");
      topbar.classList.remove("lnb-collapsed");
      wrapper.classList.remove("lnb-collapsed");
      localStorage.setItem("lnbState", "open");
    } else {
      lnb.classList.add("collapsed");
      topbar.classList.add("lnb-collapsed");
      wrapper.classList.add("lnb-collapsed");
      localStorage.setItem("lnbState", "collapsed");
    }
  });
}

/**
 * 서브메뉴 (has-sub) 토글
 * @param {string} selector - 서브메뉴 선택자
 */
function setupLnbSubmenu(selector) {
  const links = document.querySelectorAll(selector);
  links.forEach((link) => {
    link.addEventListener("click", (e) => {
      e.preventDefault();
      link.parentElement.classList.toggle("open");
    });
  });
}

/**
 * 탭(Tab) 전역 설정
 */
function setupTabs() {
  const tabItems = document.querySelectorAll(".tab-item");
  const tabPanes = document.querySelectorAll(".tab-pane");

  tabItems.forEach((tab) => {
    tab.addEventListener("click", () => {
      tabItems.forEach((t) => t.classList.remove("active"));
      tabPanes.forEach((p) => p.classList.remove("active"));

      tab.classList.add("active");
      const targetId = tab.getAttribute("data-tab");
      const pane = document.getElementById(targetId);
      if (pane) pane.classList.add("active");
    });
  });
}

/**
 * 아코디언(Accordion) 전역 설정
 * @param {string} selector - 아코디언 선택자
 */
function setupAccordion(selector) {
  const items = document.querySelectorAll(selector);
  items.forEach((item) => {
    const header = item.querySelector(".accordion-header");
    if (header) {
      header.addEventListener("click", () => {
        item.classList.toggle("open");
      });
    }
  });
}

/**
 * 모달 열기/닫기
 * @param {string} modalId - 모달 ID
 * @param {string} backdropId - 백드롭 ID
 */
function openModal(modalId, backdropId) {
  const modal = document.getElementById(modalId);
  const backdrop = document.getElementById(backdropId);
  if (modal) modal.classList.add("show");
  if (backdrop) backdrop.classList.add("show");
}

function closeModal(modalId, backdropId) {
  const modal = document.getElementById(modalId);
  const backdrop = document.getElementById(backdropId);
  if (modal) modal.classList.remove("show");
  if (backdrop) backdrop.classList.remove("show");
}

/**
 * Toast 메시지 표시
 * @param {string} message - 표시할 메시지
 * @param {string} type - 토스트 타입 (success, error, warning, info)
 * @param {number} autoHide - 자동 숨김 시간 (ms)
 */
function showToast(message, type = "success", autoHide = 3000) {
  let toastContainer = document.getElementById("toastContainer");
  if (!toastContainer) {
    toastContainer = document.createElement("div");
    toastContainer.id = "toastContainer";
    toastContainer.className = "toast-container";
    document.body.appendChild(toastContainer);
  }

  const toastEl = document.createElement("div");
  toastEl.className = `toast toast-${type}`;
  toastEl.textContent = message;
  toastContainer.appendChild(toastEl);

  setTimeout(() => {
    toastEl.classList.add("show");
  }, 50);

  if (autoHide > 0) {
    setTimeout(() => {
      hideToast(toastEl);
    }, autoHide);
  }
}

/**
 * Toast 메시지 숨기기
 * @param {HTMLElement} toastEl - 숨길 토스트 요소
 */
function hideToast(toastEl) {
  if (!toastEl) return;
  toastEl.classList.remove("show");
  setTimeout(() => {
    if (toastEl.parentNode) toastEl.parentNode.removeChild(toastEl);
  }, 300);
}

/**
 * 필터 팝업 생성
 * @param {HTMLElement} icon - 클릭된 필터 아이콘
 * @param {string} currentValue - 현재 필터값
 * @param {Function} success - 필터 적용 콜백
 * @param {Function} cancel - 필터 취소 콜백
 * @param {string} placeholder - 입력창 플레이스홀더
 */
function createFilterPopup(icon, currentValue, success, cancel, placeholder) {
  const popup = document.createElement("div");
  popup.className = "filter-popup";

  const input = document.createElement("input");
  input.type = "text";
  input.placeholder = placeholder || "검색어 입력";
  input.value = currentValue || "";
  popup.appendChild(input);

  const btnApply = document.createElement("button");
  btnApply.textContent = "적용";
  popup.appendChild(btnApply);

  const btnCancel = document.createElement("button");
  btnCancel.textContent = "취소";
  popup.appendChild(btnCancel);

  function removePopup() {
    if (popup.parentNode) {
      popup.parentNode.removeChild(popup);
      activePopup = null;
    }
  }

  btnApply.addEventListener("click", () => {
    const val = input.value.trim();
    success(val);
    if (val !== "") {
      icon.classList.add("filter-active");
    } else {
      icon.classList.remove("filter-active");
    }
    removePopup();
  });

  btnCancel.addEventListener("click", () => {
    cancel();
    icon.classList.remove("filter-active");
    removePopup();
  });

  setTimeout(() => {
    document.addEventListener("click", function onDocClick(e) {
      if (!popup.contains(e.target) && e.target !== icon) {
        removePopup();
        document.removeEventListener("click", onDocClick);
      }
    });
  }, 0);

  const iconRect = icon.getBoundingClientRect();
  popup.style.top = iconRect.bottom + window.scrollY + 5 + "px";
  popup.style.left = iconRect.left + window.scrollX - 20 + "px";

  document.body.appendChild(popup);
  activePopup = popup;
  input.focus();
}

/**
 * Tabulator 커스텀 헤더 필터
 * @param {Object} cell - Tabulator 셀 객체
 * @param {Function} onRendered - 렌더링 완료 콜백
 * @param {Function} success - 성공 콜백
 * @param {Function} cancel - 취소 콜백
 * @param {Object} editorParams - 에디터 파라미터
 * @returns {HTMLElement} 필터 아이콘 요소
 */
function customFunnelHeaderFilter(cell, onRendered, success, cancel, editorParams) {
  const headerEl = cell.getElement();
  const titleText = headerEl.textContent;
  headerEl.innerHTML = "";

  const wrapper = document.createElement("div");
  wrapper.classList.add("header-filter-wrapper");

  const titleSpan = document.createElement("span");
  titleSpan.textContent = titleText;
  wrapper.appendChild(titleSpan);

  const icon = document.createElement("span");
  icon.classList.add("filter-icon", "fa", "fa-filter");
  wrapper.appendChild(icon);

  headerEl.appendChild(wrapper);

  icon.addEventListener("click", (e) => {
    e.stopPropagation();
    if (activePopup) {
      activePopup.parentNode.removeChild(activePopup);
      activePopup = null;
      return;
    }
    createFilterPopup(icon, "", success, cancel, editorParams?.placeholder);
  });

  return icon;
}

/**
 * 쿠키 설정
 * @param {string} name - 쿠키 이름
 * @param {string} value - 쿠키 값
 * @param {Object} options - 쿠키 옵션
 */
function setCookie(name, value, options = {}) {
  const defaultOptions = { sameSite: 'Lax', secure: true };
  options = { ...defaultOptions, ...options };

  let cookieString = `${encodeURIComponent(name)}=${encodeURIComponent(value)}`;

  if (options.expires) {
    let expires = options.expires;
    if (typeof expires === 'number') {
      const date = new Date();
      date.setTime(date.getTime() + expires * 1000);
      expires = date;
    }
    if (expires instanceof Date) {
      cookieString += `; expires=${expires.toUTCString()}`;
    }
  }

  if (options.path) cookieString += `; path=${options.path}`;
  if (options.domain) cookieString += `; domain=${options.domain}`;
  if (options.secure) cookieString += '; secure';
  if (options.sameSite) cookieString += `; samesite=${options.sameSite}`;

  document.cookie = cookieString;
}

/**
 * 쿠키 가져오기
 * @param {string} name - 쿠키 이름
 * @returns {string|null} 쿠키 값
 */
function getCookie(name) {
  const nameEQ = `${encodeURIComponent(name)}=`;
  const cookies = document.cookie.split('; ');

  for (let cookie of cookies) {
    if (cookie.startsWith(nameEQ)) {
      return decodeURIComponent(cookie.substring(nameEQ.length));
    }
  }
  return null;
}

/**
 * 쿠키 삭제
 * @param {string} name - 쿠키 이름
 * @param {Object} options - 쿠키 옵션
 */
function deleteCookie(name, options = {}) {
  setCookie(name, '', { ...options, expires: -1 });
}

/**
 * 숫자 포맷(콤마 찍기)
 * @param {number|string} value - 숫자나 문자열 형태의 값
 * @returns {string} 포매팅된 숫자 문자열
 */
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

/**
 * 날짜 시간 포맷 변환
 */
function formatDateTime(dateString) {
  if (!dateString) return '';
  
  const date = new Date(dateString);
  if (isNaN(date.getTime())) return dateString;
  
  const year = date.getFullYear();
  const month = String(date.getMonth() + 1).padStart(2, '0');
  const day = String(date.getDate()).padStart(2, '0');
  const hours = String(date.getHours()).padStart(2, '0');
  const minutes = String(date.getMinutes()).padStart(2, '0');
  
  return `${year}-${month}-${day} ${hours}:${minutes}`;
}

/**
 * 현재 URL 기준으로 LNB 메뉴 하이라이트
 */
function highlightCurrentMenu() {
  const currentPath = window.location.pathname;
  const menuLinks = document.querySelectorAll(".lnb-menu a");

  menuLinks.forEach((link) => {
    const href = link.getAttribute("href");
    if (!href || href === "#") return;

    if (currentPath.endsWith(href)) {
      link.classList.add("active");
      const parentLi = link.closest("li.has-sub");
      if (parentLi) {
        parentLi.classList.add("open");
      }
    }
  });
}

/**
 * 페이지 로드 시 초기화
 */
function initCommonScripts() {
  setupLnbToggleWithStorage("lnb", "main", "lnbToggleBtn");
  setupLnbSubmenu(".lnb-menu li.has-sub > a");
  setupTabs();
  setupAccordion(".accordion-item");

  document.querySelector(".lnb-toggle-btn")?.addEventListener("click", function () {
    document.querySelector(".lnb").classList.toggle("active");
    this.classList.toggle("open");
  });

  // 모바일에서 닫기 버튼 클릭 시 LNB 닫기
  document.getElementById("lnbCloseBtn")?.addEventListener("click", function () {
    const lnb = document.getElementById("lnb");
    const backdrop = document.getElementById("lnbBackdrop");
    if (lnb) lnb.classList.remove("active");
    if (backdrop) backdrop.classList.remove("show");
  });

  // Input number + comma
  $(document).on('keyup', 'input[data-role=number]', function (event) {
    if(event.keyCode === 65 || event.keyCode === 17) return;
    if(this.value == '0') return;
    
    let cursorIndex = this.selectionStart;
    const before = this.value.substring(0, cursorIndex).match(/,/g)?.length || 0;
    
    // 소수점 처리를 위해 값을 분리
    const hasDecimal = this.value.includes('.');
    let integerPart = this.value;
    let decimalPart = '';
    
    if (hasDecimal) {
      const parts = this.value.split('.');
      integerPart = parts[0];
      decimalPart = parts.length > 1 ? '.' + parts[1].replace(/[^0-9]/g, '') : '';
    }
    
    // 정수 부분만 처리
    integerPart = integerPart.replace(/[^-0-9]/g, '');
    integerPart = (integerPart.indexOf("-") === 0 ? "-" : "") + integerPart.replace(/[-]/gi, '');
    integerPart = integerPart.replace(/(^0+)/g, '');
    integerPart = integerPart.replace(/,/g, '');
    integerPart = integerPart.replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    
    // 정수 부분과 소수 부분 합치기
    this.value = integerPart + decimalPart;
    
    // 커서 위치 조정
    const after = this.value.substring(0, cursorIndex).match(/,/g)?.length || 0;
    if(before != after) {
      cursorIndex += (after - before);
    }
    this.setSelectionRange(cursorIndex, cursorIndex);
  });

  highlightCurrentMenu();
}

document.addEventListener("DOMContentLoaded", initCommonScripts);

/**
 * SweetAlert2 Alert 래퍼
 * @param {string|Object} html - 메시지 또는 설정 객체
 * @param {Function} callback - 콜백 함수
 * @param {string} title - 제목
 * @param {string} icon - 아이콘 타입
 */
$.alert = function(html, callback, title, icon) {
    // 객체로 전달된 경우
    if (typeof html === 'object') {
        Swal.fire({
            title: html.title ?? '알림',
            html: html.content,
            icon: html.type === 'red' ? 'error' : 
                  html.type === 'green' ? 'success' : 
                  html.type === 'orange' ? 'warning' : 
                  html.type === 'blue' ? 'info' : 'info',
            confirmButtonText: '확인',
            customClass: {
                htmlContainer: 'swal2-custom-html'
            }
        }).then(() => {
            if (typeof callback === 'function') {
                callback();
            }
        });
        return;
    }

    // 개별 파라미터로 전달된 경우
    Swal.fire({
        title: title ?? '알림',
        html: html,
        icon: icon ?? 'info',
        confirmButtonText: '확인',
        customClass: {
            htmlContainer: 'swal2-custom-html'
        }
    }).then(() => {
        if (typeof callback === 'function') {
            callback();
        }
    });
};

/**
 * SweetAlert2 Confirm 래퍼
 * @param {string|Object} html - 메시지 또는 설정 객체
 * @param {Function} confirmCallback - 확인 콜백 함수
 * @param {string} title - 제목
 * @param {string} icon - 아이콘 타입
 * @param {string} confirmText - 확인 버튼 텍스트
 */
$.confirm = function(html, confirmCallback, title, icon, confirmText) {
    // 객체로 전달된 경우
    if (typeof html === 'object') {
        Swal.fire({
            title: html.title ?? '확인',
            html: html.content,
            icon: html.type === 'red' ? 'error' : 
                  html.type === 'green' ? 'success' : 
                  html.type === 'orange' ? 'warning' : 
                  html.type === 'blue' ? 'info' : 'question',
            showCancelButton: true,
            confirmButtonText: html.buttons?.confirm?.text ?? '확인',
            cancelButtonText: html.buttons?.cancel?.text ?? '취소',
            reverseButtons: true,
            customClass: {
                htmlContainer: 'swal2-custom-html'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                if (typeof confirmCallback === 'function') confirmCallback();
            }
        });
        return;
    }

    // 개별 파라미터로 전달된 경우
    Swal.fire({
        title: title ?? '확인',
        html: html,
        icon: icon ?? 'question',
        showCancelButton: true,
        confirmButtonText: confirmText ?? '확인',
        cancelButtonText: '취소',
        reverseButtons: true,
        customClass: {
            htmlContainer: 'swal2-custom-html'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            if (typeof confirmCallback === 'function') confirmCallback();
        }
    });
};

/**
* 필수 필드 검사
*/
function checkRequiredFields(data, fieldLabels) {
  for (const [field, label] of Object.entries(fieldLabels)) {
      if (!data[field]) {
          $.alert({
              title: '알림',
              content: `${label}을(를) 입력해주세요.`,
              type: 'orange'
          });
          return false;
      }
  }
  return true;
}

/**
* 날짜 범위 검사
*/
function checkDateRange(startDt, endDt) {
  if (!startDt || !endDt) {
      $.alert({
          title: '알림',
          content: '시작일과 마감일을 모두 입력해주세요.',
          type: 'orange'
      });
      return false;
  }
  
  const start = new Date(startDt);
  const end = new Date(endDt);
  
  if (isNaN(start.getTime()) || isNaN(end.getTime())) {
      $.alert({
          title: '알림',
          content: '날짜 형식이 올바르지 않습니다.',
          type: 'orange'
      });
      return false;
  }
  
  if (start >= end) {
      $.alert({
          title: '알림',
          content: '마감일은 시작일보다 이후여야 합니다.',
          type: 'orange'
      });
      return false;
  }
  
  return true;
}

/**
* 수량 검사
*/
function checkQuantity(minQty, maxQty) {
  if (minQty <= 0) {
      $.alert({
          title: '알림',
          content: '최소 구매 수량은 0보다 커야 합니다.',
          type: 'orange'
      });
      return false;
  }
  
  if (maxQty <= 0) {
      $.alert({
          title: '알림',
          content: '최대 구매 수량은 0보다 커야 합니다.',
          type: 'orange'
      });
      return false;
  }
  
  if (minQty > maxQty) {
      $.alert({
          title: '알림',
          content: '최소 구매 수량은 최대 구매 수량보다 작아야 합니다.',
          type: 'orange'
      });
      return false;
  }
  
  return true;
}

/**
* 할인가 검사
* - 할인가는 숫자, 0보다 커야 함
* - 원가보다 낮아야 함
*/
function checkDiscountPrice(discountPriceStr, originalPriceStr) {
  const discountPrice = parseInt(String(discountPriceStr).replace(/[^0-9]/g, ''));
  const originalPrice = parseInt(String(originalPriceStr).replace(/[^0-9]/g, ''));

  // 할인가가 숫자인지 검증
  if (isNaN(discountPrice)) {
      $.alert({
          title: '알림',
          content: '할인가는 숫자만 입력 가능합니다.',
          type: 'orange'
      });
      return false;
  }

  // 할인가가 양수인지 검증
  if (discountPrice <= 0) {
      $.alert({
          title: '알림',
          content: '할인가는 0보다 커야 합니다.',
          type: 'orange'
      });
      return false;
  }

  // 할인가가 원가보다 낮은지 검증
  if (discountPrice >= originalPrice) {
      $.alert({
          title: '알림',
          content: '할인가는 원가보다 낮아야 합니다.',
          type: 'orange'
      });
      return false;
  }
  return true;
}