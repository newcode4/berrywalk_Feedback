<?php
/*
Plugin Name: Berrywalk Feedback (ACF)
Description: 대표 질문지 + 대표 회원가입 + 마이페이지 (ACF 기반 경량 플러그인)
Version: 1.5.0
Author: Berrywalk
Text Domain: berrywalk-feedback-acf
Update URI: https://github.com/newcode4/berrywalk-feedback-acf
*/

if (!defined('ABSPATH')) exit;

define('BWFB_VER',  '1.5.0');
define('BWFB_FILE', __FILE__);
define('BWFB_DIR',  plugin_dir_path(__FILE__));
define('BWFB_URL',  plugin_dir_url(__FILE__));



if (!defined('BW_TG_BOT_TOKEN')) define('BW_TG_BOT_TOKEN', '');
if (!defined('BW_TG_CHAT_ID'))   define('BW_TG_CHAT_ID',   '');

/** ──────────────── 필수 파일 로더(누락 시 안전 종료) ──────────────── */
$__need = [
  'includes/cpt-admin.php',
  'includes/form.php',
  'includes/signup.php',
  'includes/save.php',
  'includes/view-list.php',
  'includes/acf-fields.php',
];
foreach ($__need as $__rel) {
  $__path = BWFB_DIR . $__rel;
  if (file_exists($__path)) {
    require_once $__path;
  } else {
    // 파일 누락이면 활성화 즉시 비활성화 + 관리자 알림
    add_action('admin_init', function(){ deactivate_plugins(plugin_basename(BWFB_FILE)); });
    add_action('admin_notices', function() use ($__rel){
      echo '<div class="notice notice-error"><p><strong>Berrywalk Feedback</strong> — 누락된 파일: <code>' .
        esc_html($__rel) . '</code> 전체 폴더를 업로드하세요.</p></div>';
    });
    return;
  }
}

/** ──────────────── 에셋 등록(핸들 호환 유지) ──────────────── */
add_action('wp_enqueue_scripts', function(){
  $css = BWFB_URL.'assets/owner-form.css';
  $js  = BWFB_URL.'assets/owner-form.js';
  wp_register_style ('bwos-form', $css, [], BWFB_VER);
  wp_register_style ('bwf-forms', $css, [], BWFB_VER); // alias
  wp_register_script('bwos-form', $js,  ['jquery'], BWFB_VER, true);
  wp_register_script('bwf-forms', $js,  ['jquery'], BWFB_VER, true); // alias
});

// ───────── 깃허브 자동 업데이트 (PUC v5) ─────────
if ( is_admin() ) {
  require_once __DIR__ . '/plugin-update-checker/plugin-update-checker.php';
  // v5 네임스페이스 사용
  $factory = '\YahnisElsts\PluginUpdateChecker\v5\PucFactory';

  // ★ 본인 리포 경로 정확히 입력 (예: newcode4/berrywalk-feedback-acf)
  $repo = 'newcode4/berrywalk_Feedback';

  // ★ 슬러그는 '폴더명' 그대로! (예: berrywalk-feedback-acf)
  $slug = 'berrywalk-feedback-acf';

  if (class_exists($factory)) {
    $updateChecker = \YahnisElsts\PluginUpdateChecker\v5\PucFactory::buildUpdateChecker(
      'https://github.com/'.$repo,
      __FILE__,
      $slug
    );
    // 기본 브랜치
    $updateChecker->setBranch('main');
    // (권장) 릴리즈 자산(zip) 우선
    $updateChecker->getVcsApi()->enableReleaseAssets();
    // (private 리포면) $updateChecker->setAuthentication('ghp_xxx');
  }
}
