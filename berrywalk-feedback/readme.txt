=== Berrywalk Feedback Survey (MVP) ===
Contributors: berrywalk
Requires at least: 5.8
Tested up to: 6.6
Stable tag: 0.1.0
License: GPLv2 or later

대표 질문 수집 → 고객 서술형 피드백 → 관리자 검토까지 한 번에 연결하는 MVP 플러그인.

== 사용법 ==
1) 페이지 A(대표용): [bw_owner_form]
   - 제출 시 고객 피드백용 고유 링크 생성 → 관리자 이메일로 발송

2) 페이지 B(고객용): [bw_feedback_form]
   - URL에 ?ref=토큰 포함되어야 대표 질문이 자동 노출됨
   - 각 문항 최소 100자

3) 감사 페이지: /feedback-thanks/ (원하는 문구로 페이지 생성)

== 업데이트 ==
- /plugin-update-checker 포함.
- inc/update.php에서 GitHub URL/Branch/Token 설정.
