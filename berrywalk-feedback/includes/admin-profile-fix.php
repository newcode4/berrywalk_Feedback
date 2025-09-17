<?php
if (!defined('ABSPATH')) exit;

/* 관리자 프로필 화면 UI 복구: 문제 섹션 숨김 + 저장 버튼 보장 */
function bwf_admin_profile_fix_footer(){
  ?>
  <script>
  (function(){
    const form = document.querySelector('form#your-profile');
    if(!form) return;

    // 1) WP-Members 추가 필드 섹션 텍스트를 가진 H2를 찾아서 해당 섹션을 숨김
    const headers = Array.from(document.querySelectorAll('h2'));
    const target = headers.find(h => /WP-?Members\s*추가\s*필드/i.test(h.textContent));
    if(target){
      let el = target.nextElementSibling;
      while(el && el.tagName !== 'H2'){
        el.style.display = 'none';
        el = el.nextElementSibling;
      }
      // 헤더도 숨김
      target.style.display = 'none';
    }

    // 2) 저장 버튼이 없으면 하단에 강제로 추가
    const hasSubmit = form.querySelector('p.submit input[type=submit], .submit input[type=submit]');
    if(!hasSubmit){
      const p = document.createElement('p'); p.className='submit';
      const btn = document.createElement('input');
      btn.type='submit'; btn.className='button button-primary';
      btn.value='사용자 업데이트';
      p.appendChild(btn);
      form.appendChild(p);
    }
  })();
  </script>
  <?php
}
add_action('admin_footer-profile.php','bwf_admin_profile_fix_footer');
add_action('admin_footer-user-edit.php','bwf_admin_profile_fix_footer');
