# Git 훅 설정 방법

이 프로젝트는 자동화된 커밋 메시지 형식을 사용합니다.
모든 개발자는 아래 설정을 따라 Git 훅을 설정해주세요.

## 설정 방법

### 1. 전체 저장소에 훅 디렉토리 설정

이 명령어는 모든 신규 클론에 자동으로 적용됩니다:

```bash
git config --local core.hooksPath .githooks
```

### 2. 훅 파일에 실행 권한 부여

#### Linux/Mac:
```bash
chmod +x .githooks/prepare-commit-msg
```

#### Windows:
Windows에서는 `.githooks/prepare-commit-msg.ps1`이 자동으로 적용됩니다.

## 커밋 메시지 형식

이 훅은 다음과 같은 형식으로 커밋 메시지를 자동 생성합니다:

```
YYYYMMDD [사용자이름] [파일유형] 커밋 메시지
```

- YYYYMMDD: 날짜 형식 (예: 20250528)
- [사용자이름]: Git에 설정된 사용자 이름
- [파일유형]: 변경된 파일 유형에 따라 [Backend], [Frontend], [Docs] 등 자동 추가

## 수동 설정 방법 (기존 클론)

이미 저장소를 클론한 사용자는 다음 명령어로 설정할 수 있습니다:

```bash
git config --local core.hooksPath .githooks
```

Windows 사용자는 다음 명령어를 추가로 실행해주세요:

```powershell
Copy-Item .githooks/prepare-commit-msg.ps1 .git/hooks/prepare-commit-msg.ps1
``` 