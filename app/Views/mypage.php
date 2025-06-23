<div class="container py-4">
    <div class="row mb-4">
        <div class="col-lg-12">
            <h1 class="page-title">내 정보</h1>
            <p class="text-muted">계정 정보를 확인하고 관리할 수 있습니다.</p>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-4 mb-4">
            <!-- 프로필 카드 -->
            <div class="card shadow-sm">
                <div class="card-body text-center">
                    <div class="avatar-container mb-4">
                        <div class="avatar avatar-xl">
                            <i class="bi bi-person-circle display-1"></i>
                        </div>
                    </div>
                    <h4 class="mb-1"><?php echo htmlspecialchars($user['name']); ?></h4>
                    <p class="text-muted mb-3"><?php echo htmlspecialchars($user['email']); ?></p>
                    
                    <div class="d-flex justify-content-center mb-4">
                        <span class="badge bg-<?php echo $user['is_admin'] ? 'primary' : 'success'; ?> p-2">
                            <?php echo $user['is_admin'] ? '관리자' : '일반회원'; ?>
                        </span>
                    </div>
                    
                    <div class="info-item mb-3">
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">회원 상태:</span>
                            <span class="badge bg-<?php echo $user['status'] === 'active' ? 'success' : ($user['status'] === 'inactive' ? 'warning' : 'secondary'); ?> p-2">
                                <?php 
                                    echo $user['status'] === 'active' ? '활성' : 
                                        ($user['status'] === 'inactive' ? '비활성' : '삭제됨'); 
                                ?>
                            </span>
                        </div>
                    </div>
                    
                    <div class="info-item mb-3">
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">마지막 로그인:</span>
                            <span><?php echo $user['last_login_at'] ? date('Y-m-d H:i', strtotime($user['last_login_at'])) : '없음'; ?></span>
                        </div>
                    </div>
                    
                    <div class="info-item mb-3">
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">가입일:</span>
                            <span><?php echo date('Y-m-d', strtotime($user['created_at'])); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-8">
            <!-- 정보 수정 폼 -->
            <div class="card shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title m-0">계정 정보 수정</h5>
                </div>
                <div class="card-body">
                    <form id="updateUserForm" action="/api/users/<?php echo $user['id']; ?>" method="POST">
                        <input type="hidden" name="_method" value="PUT">
                        
                        <div class="mb-3">
                            <label for="name" class="form-label">이름</label>
                            <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">이메일</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" readonly>
                            <small class="text-muted">이메일은 변경할 수 없습니다.</small>
                        </div>
                        
                        <div class="mb-3">
                            <label for="password" class="form-label">새 비밀번호</label>
                            <input type="password" class="form-control" id="password" name="password" placeholder="변경하려면 입력하세요">
                            <small class="text-muted">비밀번호는 최소 8자 이상이어야 합니다.</small>
                        </div>
                        
                        <div class="mb-3">
                            <label for="password_confirm" class="form-label">비밀번호 확인</label>
                            <input type="password" class="form-control" id="password_confirm" name="password_confirm" placeholder="비밀번호 확인">
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">정보 수정</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 폼 제출 처리
    const form = document.getElementById('updateUserForm');
    
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // 비밀번호 유효성 검사
        const password = document.getElementById('password').value;
        const passwordConfirm = document.getElementById('password_confirm').value;
        
        if (password && password.length < 8) {
            alert('비밀번호는 최소 8자 이상이어야 합니다.');
            return;
        }
        
        if (password && password !== passwordConfirm) {
            alert('비밀번호가 일치하지 않습니다.');
            return;
        }
        
        // 폼 데이터 수집
        const formData = new FormData(form);
        const data = {};
        
        formData.forEach((value, key) => {
            // 비어있는 비밀번호는 제외
            if (key === 'password' && !value) return;
            if (key === 'password_confirm') return; // 확인용 필드는 제외
            if (key === '_method') return; // PUT 메서드 표시는 제외
            
            data[key] = value;
        });
        
        // API 호출
        fetch(form.action, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                alert('정보가 성공적으로 수정되었습니다.');
                location.reload();
            } else {
                alert('오류가 발생했습니다: ' + (result.message || '알 수 없는 오류'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('요청 처리 중 오류가 발생했습니다.');
        });
    });
});
</script>