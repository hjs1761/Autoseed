<?php
// error.php
?>
<div class="container">
    <div class="row justify-content-center mt-5">
        <div class="col-md-8">
            <div class="card shadow-lg animate-fadeIn">
                <div class="card-body text-center p-5">
                    <div class="mb-4">
                        <i class="fas fa-exclamation-triangle text-danger" style="font-size: 5rem;"></i>
                    </div>
                    <h2 class="card-title text-danger mb-4"><?php echo htmlspecialchars($title ?? '오류 발생'); ?></h2>
                    <div class="alert alert-danger">
                        <p class="mb-0"><?php echo nl2br(htmlspecialchars($message ?? '알 수 없는 오류가 발생했습니다.')); ?></p>
                    </div>
                    <div class="mt-4">
                        <p class="text-muted">이 문제가 계속 발생하면 관리자에게 문의하세요.</p>
                        <div class="d-flex justify-content-center gap-3 mt-4">
                            <a href="javascript:history.back()" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left"></i>이전 페이지
                            </a>
                            <a href="/<?=MAIN_PAGE?>" class="btn btn-primary">
                                <i class="fas fa-home"></i>홈으로 이동
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="text-center mt-4 text-muted">
                <small>오류 코드: <?php echo isset($code) ? htmlspecialchars($code) : 'E' . time(); ?></small>
            </div>
        </div>
    </div>
</div> 