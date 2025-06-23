param (
    [Parameter(Mandatory=$true)]
    [string]$message
)

# 날짜 포맷 (YYYYMMDD)
$date = Get-Date -Format "yyyyMMdd"

# 사용자 이니셜 추출
$userName = git config user.name
$initials = ($userName -split ' ' | ForEach-Object { $_.Substring(0,1) }) -join ''

# 최종 커밋 메시지 생성
$commitMessage = "$date $initials $message"

Write-Host "커밋 메시지: $commitMessage"

# 커밋 실행
git commit -m "$commitMessage" 