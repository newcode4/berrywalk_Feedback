<?php
if (!defined('ABSPATH')) exit;

/**
 * 관리자 프로필 UI 복구
 * - WP-Members가 출력하는 문제 섹션/오류 블록 제거(가능하면 서버단 후크도 제거)
 * - 저장 버튼 없을 때 자동 삽입(#your-profile, #createuser 모두 커버)
 */

/* 1) 가능하면 서버단에서 WP-Members 프로필 후크 제거 */
add_action('admin_init', function(){
  foreach (['show_user_profile','edit_user_profile'] as $hook){
    global $wp_filter;
    if (!isset($wp_filter[$hook])) continue;
    $callbacks = $wp_filter[$hook]->callbacks ?? [];
    foreach ($callbacks as $prio => $items){
      foreach ($items as $key => $cb){
        $fn = $cb['function'];
        // 함수명/클래스명/콜러블 문자열에 'wpmem'이 포함되면 제거
        $name = '';
        if (is_string($fn)) $name = $fn;
        elseif (is_array($fn) && is_object($fn[0])) $name = get_class($fn[0]).'::'.$fn[1];
        elseif (is_array($fn)) $name = implode('::', $fn);
        if (stripos($name, 'wpmem') !== false) {
          remove_action($hook, $fn, $prio);
        }
      }
    }
  }
});

/* 2) 프론트 단에서 섹션 숨김 + 저장 버튼 보장 */
function bwf_admin_profile_fix_footer(){
  ?>
  <style>
    /* WP-Members 오류 블록/추가 필드 섹션 숨김(보여도 무해, 저장 방해만 막음) */
    h2:has(+ p + div + p + p + p + p + p + p + p + p + p + p + p + p) {}
  </style>
  <script>
  (function(){
    function ensureSubmit(form){
      if(!form) return;
      if(!form.querySelector('p.submit input[type=submit], .submit input[type=submit]')){
        const p = document.createElement('p'); p.className='submit';
        const btn = document.createElement('input');
        btn.type='submit'; btn.className='button button-primary';
        btn.value='프로필 업데이트';
        p.appendChild(btn);
        form.appendChild(p);
      }
    }
    document.addEventListener('DOMContentLoaded', function(){
      // 1) WP-Members 섹션 헤더 텍스트 찾으면 해당 섹션부터 다음 H2 전까지 숨김
      const headers = Array.from(document.querySelectorAll('h2'));
      headers.forEach(h=>{
        const t = h.textContent.trim();
        if (/WP-?Members\s*추가\s*필드/i.test(t)){
          let el = h; el.style.display='none';
          while (el && el.nextElementSibling && el.nextElementSibling.tagName !== 'H2'){
            el = el.nextElementSibling; el.style.display='none';
          }
        }
      });
      // 2) 저장 버튼 없으면 강제 삽입
      ensureSubmit(document.querySelector('form#your-profile'));
      ensureSubmit(document.querySelector('form#createuser'));
    });
  })();
  </script>
  <?php
}
add_action('admin_footer-profile.php','bwf_admin_profile_fix_footer');
add_action('admin_footer-user-edit.php','bwf_admin_profile_fix_footer');
add_action('admin_footer-user-new.php','bwf_admin_profile_fix_footer');
