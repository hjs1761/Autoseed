<!-- app/Views/user/index.php -->
<main class="main flex-fill animate-fadeIn" id="main">
    <div class="container-fluid py-4">
        <div class="row mb-4">
            <div class="col-lg-12">
                <h1 class="page-title">회원 관리</h1>
                <p class="text-muted">인플루언서 솔루션의 회원 목록을 관리할 수 있습니다.</p>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="card-title m-0">회원 목록</h5>
                <div>
                    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addUserModal">
                        <i class="bi bi-person-plus-fill me-1"></i> 회원 추가
                    </button>
                </div>
            </div>
            <div class="card-body">
                <!-- 검색 필터 -->
                <div class="row mb-4">
                    <div class="col-md-8">
                        <form id="searchForm" class="d-flex gap-2">
                            <select name="status" class="form-select" style="width: 150px;">
                                <option value="">모든 상태</option>
                                <option value="active">활성</option>
                                <option value="inactive">비활성</option>
                                <option value="deleted">삭제됨</option>
                            </select>
                            <select name="is_admin" class="form-select" style="width: 150px;">
                                <option value="">모든 권한</option>
                                <option value="1">관리자</option>
                                <option value="0">일반회원</option>
                            </select>
                            <div class="input-group">
                                <input type="text" name="search" class="form-control" placeholder="이름 또는 이메일 검색">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-search"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                    <div class="col-md-4 text-end">
                        <div class="btn-group">
                            <button type="button" class="btn btn-outline-secondary" id="btnExportCSV">
                                <i class="bi bi-file-earmark-excel me-1"></i> CSV 내보내기
                            </button>
                        </div>
                    </div>
                </div>

                <!-- 회원 테이블 -->
                <div class="table-responsive">
                    <table class="table table-hover datatable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>이름</th>
                                <th>이메일</th>
                                <th>권한</th>
                                <th>상태</th>
                                <th>마지막 로그인</th>
                                <th>가입일</th>
                                <th>관리</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($users)): ?>
                            <tr>
                                <td colspan="8" class="text-center">등록된 회원이 없습니다.</td>
                            </tr>
                            <?php else: ?>
                                <?php foreach ($users as $user): ?>
                                <tr>
                                    <td><?php echo $user['id']; ?></td>
                                    <td><?php echo htmlspecialchars($user['name']); ?></td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo $user['is_admin'] ? 'primary' : 'success'; ?>">
                                            <?php echo $user['is_admin'] ? '관리자' : '일반회원'; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?php echo $user['status'] === 'active' ? 'success' : ($user['status'] === 'inactive' ? 'warning' : 'secondary'); ?>">
                                            <?php 
                                                echo $user['status'] === 'active' ? '활성' : 
                                                    ($user['status'] === 'inactive' ? '비활성' : '삭제됨'); 
                                            ?>
                                        </span>
                                    </td>
                                    <td><?php echo $user['last_login_at'] ? date('Y-m-d H:i', strtotime($user['last_login_at'])) : '없음'; ?></td>
                                    <td><?php echo date('Y-m-d', strtotime($user['created_at'])); ?></td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button type="button" class="btn btn-outline-primary btn-edit" data-id="<?php echo $user['id']; ?>">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-danger btn-delete" data-id="<?php echo $user['id']; ?>" data-name="<?php echo htmlspecialchars($user['name']); ?>">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- 회원 추가 모달 -->
    <div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="addUserForm" action="/api/users" method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addUserModalLabel">회원 추가</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="name" class="form-label">이름</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">이메일</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">비밀번호</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                            <small class="text-muted">비밀번호는 최소 8자 이상이어야 합니다.</small>
                        </div>
                        <div class="mb-3">
                            <label for="is_admin" class="form-label">권한</label>
                            <select class="form-select" id="is_admin" name="is_admin">
                                <option value="0">일반회원</option>
                                <option value="1">관리자</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="status" class="form-label">상태</label>
                            <select class="form-select" id="status" name="status">
                                <option value="active">활성</option>
                                <option value="inactive">비활성</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">취소</button>
                        <button type="submit" class="btn btn-primary">추가</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- 회원 편집 모달 -->
    <div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="editUserForm" action="/api/users/" method="POST">
                    <input type="hidden" name="_method" value="PUT">
                    <input type="hidden" name="id" id="edit_id">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editUserModalLabel">회원 정보 수정</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="edit_name" class="form-label">이름</label>
                            <input type="text" class="form-control" id="edit_name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_email" class="form-label">이메일</label>
                            <input type="email" class="form-control" id="edit_email" name="email" readonly>
                        </div>
                        <div class="mb-3">
                            <label for="edit_password" class="form-label">새 비밀번호</label>
                            <input type="password" class="form-control" id="edit_password" name="password" placeholder="변경하려면 입력하세요">
                            <small class="text-muted">비밀번호는 최소 8자 이상이어야 합니다.</small>
                        </div>
                        <div class="mb-3">
                            <label for="edit_is_admin" class="form-label">권한</label>
                            <select class="form-select" id="edit_is_admin" name="is_admin">
                                <option value="0">일반회원</option>
                                <option value="1">관리자</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="edit_status" class="form-label">상태</label>
                            <select class="form-select" id="edit_status" name="status">
                                <option value="active">활성</option>
                                <option value="inactive">비활성</option>
                                <option value="deleted">삭제됨</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">취소</button>
                        <button type="submit" class="btn btn-primary">저장</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // DataTable 초기화
    const dataTable = $('.datatable').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.5/i18n/ko.json'
        },
        responsive: true,
        order: [[0, 'desc']]
    });
    
    // 검색 폼 제출 이벤트
    $('#searchForm').on('submit', function(e) {
        e.preventDefault();
        
        // 각 필터 값 가져오기
        const status = $('select[name="status"]').val();
        const isAdmin = $('select[name="is_admin"]').val();
        const search = $('input[name="search"]').val();
        
        // DataTable 검색 적용
        dataTable.search(search).draw();
        
        // 추가 필터링
        $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
            const rowStatus = data[4]; // 상태 컬럼
            const rowIsAdmin = data[3]; // 권한 컬럼
            
            let statusMatch = true;
            if (status) {
                statusMatch = rowStatus.includes(status === 'active' ? '활성' : 
                              (status === 'inactive' ? '비활성' : '삭제됨'));
            }
            
            let isAdminMatch = true;
            if (isAdmin !== '') {
                isAdminMatch = rowIsAdmin.includes(isAdmin === '1' ? '관리자' : '일반회원');
            }
            
            return statusMatch && isAdminMatch;
        });
        
        dataTable.draw();
        
        // 필터 제거
        $.fn.dataTable.ext.search.pop();
    });
    
    // 회원 추가 폼 제출
    $('#addUserForm').on('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const data = {};
        
        formData.forEach((value, key) => {
            data[key] = value;
        });
        
        fetch('/api/users', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                alert('회원이 성공적으로 추가되었습니다.');
                $('#addUserModal').modal('hide');
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
    
    // 회원 편집 버튼 클릭
    $('.btn-edit').on('click', function() {
        const userId = $(this).data('id');
        
        // 회원 정보 로드
        fetch(`/api/users/${userId}`)
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                const user = result.data;
                
                // 폼에 데이터 채우기
                $('#edit_id').val(user.id);
                $('#edit_name').val(user.name);
                $('#edit_email').val(user.email);
                $('#edit_is_admin').val(user.is_admin ? '1' : '0');
                $('#edit_status').val(user.status);
                
                // 폼 액션 URL 설정
                $('#editUserForm').attr('action', `/api/users/${userId}`);
                
                // 모달 열기
                $('#editUserModal').modal('show');
            } else {
                alert('회원 정보를 불러오는 중 오류가 발생했습니다.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('회원 정보를 불러오는 중 오류가 발생했습니다.');
        });
    });
    
    // 회원 편집 폼 제출
    $('#editUserForm').on('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const data = {};
        const userId = $('#edit_id').val();
        
        formData.forEach((value, key) => {
            if (key === '_method' || key === 'id') return;
            if (key === 'password' && !value) return;
            
            data[key] = value;
        });
        
        fetch(`/api/users/${userId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                alert('회원 정보가 성공적으로 수정되었습니다.');
                $('#editUserModal').modal('hide');
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
    
    // 회원 삭제 버튼 클릭
    $('.btn-delete').on('click', function() {
        if (!confirm(`정말로 "${$(this).data('name')}" 회원을 삭제하시겠습니까?`)) {
            return;
        }
        
        const userId = $(this).data('id');
        
        fetch(`/api/users/${userId}`, {
            method: 'DELETE'
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                alert('회원이 성공적으로 삭제되었습니다.');
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
    
    // CSV 내보내기 버튼 클릭
    $('#btnExportCSV').on('click', function() {
        const visibleData = dataTable.rows({ search: 'applied' }).data().toArray();
        let csvContent = "ID,이름,이메일,권한,상태,마지막 로그인,가입일\n";
        
        visibleData.forEach(row => {
            // HTML 태그 제거 및 CSV 형식으로 변환
            const cleanRow = row.map(cell => {
                // HTML 태그 제거
                const div = document.createElement('div');
                div.innerHTML = cell;
                const text = div.textContent || div.innerText || '';
                
                // 쉼표가 포함된 경우 따옴표로 감싸기
                return text.includes(',') ? `"${text}"` : text;
            });
            
            // 관리 컬럼 제외
            csvContent += cleanRow.slice(0, 7).join(',') + "\n";
        });
        
        // CSV 파일 다운로드
        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        const url = URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.setAttribute('href', url);
        link.setAttribute('download', '회원목록_' + new Date().toISOString().split('T')[0] + '.csv');
        link.style.visibility = 'hidden';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    });
});
</script>