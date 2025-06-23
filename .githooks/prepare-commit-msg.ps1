# PowerShell 스크립트 - 커밋 메시지 자동화

# 인자 가져오기
$commitMsgFile = $args[0]
$commitSource = $args[1]

# 날짜 정보 가져오기 (YYYYMMDD 형식)
$date = Get-Date -Format "yyyyMMdd"

# Git 사용자 이름 가져오기
$userName = git config user.name

# 자동 생성된 커밋이 아닐 경우에만 처리 (merge, squash 등 제외)
if ([string]::IsNullOrEmpty($commitSource) -or $commitSource -eq "message") {
    # 스테이징된 파일 목록 가져오기
    $stagedFiles = git diff --cached --name-only

    # 파일 유형 분류
    $phpFiles = $false
    $jsFiles = $false
    $cssFiles = $false
    $docFiles = $false

    # 스테이지된 파일 유형 확인
    foreach ($file in $stagedFiles) {
        if ($file -match "\.php$") {
            $phpFiles = $true
        }
        elseif ($file -match "\.js$") {
            $jsFiles = $true
        }
        elseif ($file -match "\.css$") {
            $cssFiles = $true
        }
        elseif ($file -match "\.md$" -or $file -match "^README" -or $file -match "docs/") {
            $docFiles = $true
        }
    }

    # 원래 커밋 메시지 읽기
    $originalMsg = Get-Content -Path $commitMsgFile -Raw

    # 파일 유형에 따라 접두사 결정
    $prefix = ""
    if ($docFiles -and -not ($phpFiles -or $jsFiles -or $cssFiles)) {
        $prefix = "[Docs] "
    }
    elseif ($phpFiles) {
        $prefix = "[Backend] "
    }
    elseif ($jsFiles -or $cssFiles) {
        $prefix = "[Frontend] "
    }

    # 최종 커밋 메시지 생성
    $newMsg = "$date [$userName] $prefix$originalMsg"
    Set-Content -Path $commitMsgFile -Value $newMsg
} 