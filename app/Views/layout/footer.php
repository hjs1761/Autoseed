<?php
// footer.php
?>

<style>
    .chat-button-glow {
        animation: glowPulse 1.5s infinite;
        box-shadow: 0 0 10px rgba(255, 193, 7, 0.8);
        transition: transform 0.3s ease;
    }

    .chat-button-glow:hover {
        transform: scale(1.1);
        box-shadow: 0 0 20px rgba(255, 193, 7, 1);
    }

    @keyframes glowPulse {
        0% {
            box-shadow: 0 0 10px rgba(255, 193, 7, 0.8);
        }

        50% {
            box-shadow: 0 0 20px rgba(255, 193, 7, 1);
        }

        100% {
            box-shadow: 0 0 10px rgba(255, 193, 7, 0.8);
        }
    }
</style>

<!-- 실시간 채팅 상담 버튼 (카카오톡 오픈채팅 URL) -->
<div class="chat-button-area" style="position: fixed; bottom: 30px; right: 30px; z-index: 9999;">
    <button class="btn btn-warning btn-lg chat-button-glow" onclick="openKakaoChat()"
        style="border-radius: 50%; width: 80px; height: 80px; position: relative;">
        <i class="fas fa-comment-dots" style="font-size: 32px; display: block;"></i>
    </button>
</div>

        </div><!-- /#content -->
    </div><!-- /.wrapper -->

    <!-- 푸터 영역 -->
    <footer class="footer mt-auto py-3 bg-light">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <p class="mb-0 text-muted">&copy; <?php echo date('Y'); ?> 인플루언서 솔루션</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="mb-0 text-muted">버전: 1.0.0</p>
                </div>
            </div>
        </div>
    </footer>

    <script>
        $(document).ready(function() {
            // 사이드바 토글
            $('#sidebarCollapse').on('click', function() {
                $('#sidebar').toggleClass('active');
                $('#content').toggleClass('active');
            });

            // 사이드바 접기 아이콘 클릭
            $('.sidebar-collapse-icon').on('click', function() {
                $('#sidebar').toggleClass('active');
                $('#content').toggleClass('active');
            });

            // DataTable 설정 (클래스가 있는 경우만 초기화)
            if ($('.datatable').length) {
                $('.datatable').DataTable({
                    language: {
                        url: '//cdn.datatables.net/plug-ins/1.13.5/i18n/ko.json'
                    },
                    responsive: true
                });
            }
        });
    </script>
</body>

</html>