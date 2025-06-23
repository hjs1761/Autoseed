<?php
// header.php
?>
<!DOCTYPE html>
<html lang="ko">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?php echo $title ?? '인플루언서 솔루션'; ?></title>

    <!-- CSS 라이브러리 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    
    <!-- 데이터테이블 스타일 -->
    <link href="https://cdn.datatables.net/1.13.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    
    <!-- 커스텀 CSS -->
    <link href="/assets/css/style.css" rel="stylesheet">

    <!-- 커스텀 CSS -->
    <link rel="stylesheet" href="/assets/css/common.css" />

    <!-- Tabulator CSS (CDN) -->
    <link rel="stylesheet" href="https://unpkg.com/tabulator-tables@6.3.0/dist/css/tabulator.min.css" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tabulator/6.3.0/css/tabulator_bulma.min.css" rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />

    <!-- SweetAlert2 CSS (CDN) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    <!-- XLSX JS (CDN) -->
    <script src="/assets/js/lib/xlsx.full.min.js" charset="utf-8" content="text/javascript;charset=utf-8"></script>

    <!-- JS 라이브러리 -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- 커스텀 JS (common.js 등) -->
    <script charset="utf-8" content="text/javascript;charset=utf-8">
        // 리버스프록시 관련 세팅
        $.ajaxPrefilter(function(options, originalOptions, jqXHR) {
            // ajax 호출 시 절대경로인 경우 '/pricebot' 추가
            if (options.url.startsWith('/')) {
                options.url = '<?=SITE_DIR?>' + options.url;
            }
        });

        // 페이지 로드 완료 시 로딩 화면 숨기기
        window.addEventListener('load', function() {
            $('#loading').hide()
        });
    </script>
    <script src="/assets/js/common.js" charset="utf-8" content="text/javascript;charset=utf-8"></script>
</head>

<body>
    <!-- 로딩 오버레이 -->
    <div id="loading">
        <div class="spinner"></div>
        <div class="loading-text">로딩 중...</div>
    </div>

    <!-- LNB 배경 오버레이 (모바일용) -->
    <div class="lnb-backdrop" id="lnbBackdrop"></div>

    <!-- LNB (사이드바) : 100vh 고정 -->
    <nav class="lnb" id="lnb">
        <div class="lnb-header">
            <a href="<?=MAIN_PAGE?>"><h1>위버로프트</h1></a>
            <!-- 모바일 닫기 버튼 -->
            <button class="lnb-close" id="lnbCloseBtn">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <ul class="lnb-menu">
            <li><a href="/dashboard">대시보드</a></li>
            <li><a href="/group_buying">공동구매 관리</a></li>
            <li><a href="/order">주문 관리</a></li>
            <li><a href="/notice">공지사항</a></li>
            <?php if (isAdmin()): ?>
            <li class="has-sub">
                <a href="#">회원 관리</a>
                <ul class="sub-menu">
                    <li><a href="/user">회원 목록</a></li>
                </ul>
                <ul class="sub-menu">
                    <li><a href="/scripttag">스크립트태그</a></li>
                </ul>
            </li>
            <li><a href="/logs">로그 모니터링</a></li>
            <?php endif; ?>
        </ul>
    </nav>

    <!-- 상단바 (고정) : LNB 오른쪽부터 시작 -->
    <header class="topbar d-flex align-items-center justify-content-between shadow-sm" id="topbar">
        <div class="topbar-left d-flex align-items-center">
            <!-- 모바일 토글 버튼 -->
            <button class="lnb-toggle-btn" id="lnbToggleBtn">
                <span></span>
                <span></span>
                <span></span>
            </button>
            <strong class="ms-2 d-flex align-items-center">
                <?=$title?>
            </strong>
        </div>
        <div class="topbar-right d-flex align-items-center">
            <!-- 드롭다운 유저 프로필 -->
            <div class="dropdown">
                <div class="user-profile d-flex align-items-center gap-2 p-2 rounded-3 cursor-pointer" data-bs-toggle="dropdown" aria-expanded="false">
                    <div class="user-icon bg-primary rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 38px; height: 38px; color: white;">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="user-details pe-2">
                        <div class="d-flex flex-column">
                            <span class="fw-bold text-dark"><?= htmlspecialchars($_SESSION['user_info']['user_name']) ?><span class="text-dark fw-normal" style="font-size: 12px;">&nbsp;관리자님</span></span>
                            <small class="text-primary-emphasis">
                                <i class="fas fa-store-alt me-1"></i>
                                <?= htmlspecialchars($_SESSION['user_info']['mall_id']) ?>
                            </small>
                        </div>
                    </div>
                    <i class="fas fa-chevron-down text-muted"></i>
                </div>
                
                <!-- 드롭다운 메뉴 -->
                <ul class="dropdown-menu dropdown-menu-end shadow">
                    <li><h6 class="dropdown-header">사용자 메뉴</h6></li>
                    <li><a class="dropdown-item" href="/mypage"><i class="fas fa-user-circle me-2 text-primary"></i> 내 정보</a></li>
                    <!-- <li><a class="dropdown-item" href="#"><i class="fas fa-cog me-2 text-secondary"></i> 설정</a></li> -->
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger" href="/logout"><i class="fas fa-sign-out-alt me-2"></i> 로그아웃</a></li>
                </ul>
            </div>
        </div>
    </header>

    <!-- 메인 콘텐츠 영역을 감싸는 wrapper (Topbar/LNB 공간만큼 offset) -->
    <div id="wrapper" class="d-flex">