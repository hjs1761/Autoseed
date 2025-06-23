# Git 사용 가이드

이 문서는 AutoSeed 프로젝트의 Git 사용 규칙을 정리한 가이드입니다. 모든 팀원은 이 규칙을 따라 코드를 관리해야 합니다.

## 1. 브랜치 규칙

| 용도 | 브랜치 이름 예시 | 설명 |
| --- | --- | --- |
| 운영/배포 | `main` | 절대 직접 수정하지 않음 |
| 개발 | `feature/기능명` | 각자 맡은 기능 단위로 생성 |
| 버그 수정 | `fix/버그설명` | 버그 수정 전용 브랜치 |
| 리팩토링 | `refactor/설명` | 구조 개선 작업 등 |

> main은 읽기 전용이라고 생각하세요.
> 
> 작업 시작 시 `main`에서 새 브랜치 생성하세요.

```bash
# 브랜치 만들기 (예: 로그인 기능)
git checkout main
git pull
git checkout -b feature/login
```

## 2. 커밋 메시지 규칙

| 형식 | 예시 | 설명 |
| --- | --- | --- |
| `feat:` | `feat: 로그인 기능 추가` | 새 기능 |
| `fix:` | `fix: 비밀번호 오류 수정` | 버그 수정 |
| `docs:` | `docs: README 수정` | 문서 변경 |
| `style:` | `style: 코드 정렬/들여쓰기 수정` | 기능 변경 없는 코드 스타일 수정 |
| `refactor:` | `refactor: DB 연결 구조 변경` | 리팩토링 |

```bash
git commit -m "feat: 게시판 작성 기능 추가"
```

## 3. Push 규칙

| 규칙 | 설명 |
| --- | --- |
| 무조건 **작업 브랜치에서 push** |  |
| `main`에는 직접 push ❌ 금지 |  |
| Push 전에 `pull` 받아 최신화 |  |
| 작업 끝나면 **PR(Merge 요청)** 생성 |  |

```bash
git add .
git commit -m "feat: 댓글 작성 기능 추가"
git push origin feature/comment
```

## 4. Pull Request (PR) 규칙

- 제목: `feat: 로그인 기능 추가`
- 설명:
    - 어떤 기능인지
    - 테스트 완료 여부 (`테스트 완료`)
- 병합은 본인이 하되, **리뷰 받고 병합** 또는 **셀프체크 후 병합**

## 5. 충돌 방지 기본 습관

```bash
# 병합 전에 항상 최신화
git checkout main
git pull

# 다시 내 브랜치로 돌아와 최신 코드 반영
git checkout feature/내기능
git rebase main
# 또는
git merge main
```

## 6. `.gitignore` 설정 필수

- 불필요한 파일이 Git에 올라가지 않도록 `.gitignore`를 반드시 설정합니다.
- 무시 대상 예시:
    - IDE/OS 파일: `.DS_Store`, `.idea/`, `.vscode/`
    - 빌드/의존성: `node_modules/`, `vendor/`, `dist/`
    - 민감 정보: `.env`, `.key`, `secrets.json`
    - 로그/캐시: `.log`, `.cache`, `.phpunit.result.cache`
- 이미 커밋된 파일을 무시하려면 캐시 제거 후 커밋합니다:
    
    ```bash
    git rm --cached 파일명
    ```
    
- 팀 프로젝트에서는 `.gitignore` 정책을 사전 공유하고, 프레임워크에 맞는 공식 템플릿을 참고합니다:
    
    https://github.com/github/gitignore 

# main 브랜치에서 최신 코드 가져오기
git checkout main
git pull

# DB 연결 브랜치 생성
git checkout -b feature/db-connection

# config/database.php 파일 작업
# (파일 편집 후)

# 변경사항 커밋
git add config/database.php
git commit -m "feat: 데이터베이스 연결 설정 구현"

# 원격 저장소에 푸시
git push origin feature/db-connection

# GitHub에서 PR 생성: "feat: 데이터베이스 연결 설정 구현"