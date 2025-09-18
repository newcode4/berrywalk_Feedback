<?php
/*
Plugin Name: Berrywalk Feedback (ACF)
Description: 대표 질문지 + 대표 회원가입 + 마이페이지 (ACF 기반 경량 플러그인)
Version: 1.4.6
Author: Berrywalk
Text Domain: berrywalk-feedback-acf
*/

if (!defined('ABSPATH')) exit;

/** ───────────────── 기본 상수 ───────────────── */
define('BWFB_VER',  '1.4.6');
define('BWFB_FILE', __FILE__);
define('BWFB_DIR',  plugin_dir_path(__FILE__));
define('BWFB_URL',  plugin_dir_url(__FILE__));

/** ───────────────── (선택) 텔레그램 설정 ─────────────────
 * 보안을 위해 wp-config.php 에 아래처럼 두는 것을 권장합니다.
 *   define('BW_TG_BOT_TOKEN', '1234:AAAA...'); // BotFather
 *   define('BW_TG_CHAT_ID',   '@your_public_channel_id'); // @로 시작(공개채널)
 * 플러그인 파일에 두고 싶으면 아래 주석을 풀어 사용하세요.
 */
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

/** ──────────────── (선택) 깃허브 자동 업데이트 ────────────────
 * /plugin-update-checker/ 폴더가 있으면 사용합니다.
 * 리포 경로를 "사용자명/리포명"으로 바꿔주세요.
 */
add_action('init', function () {
  $puc = BWFB_DIR.'plugin-update-checker/plugin-update-checker.php';
  if (!is_admin() || !file_exists($puc)) return;

  require_once $puc;

  // ❗여기를 본인 깃허브 리포에 맞게 수정: 예) 'jakvis2/berrywalk-feedback-acf'
  $repo = 'jakvis2/berrywalk-feedback-acf';

  // PUC v5 / v4 모두 자동 호환
  if (class_exists('Puc_v5_Factory')) {
    $up = Puc_v5_Factory::buildUpdateChecker(
      'https://github.com/'.$repo.'/',
      BWFB_FILE,
      'berrywalk-feedback-acf' // 플러그인 슬러그(폴더명과 맞추기)
    );
  } elseif (class_exists('Puc_v4_Factory')) {
    $up = Puc_v4_Factory::buildUpdateChecker(
      'https://github.com/'.$repo.'/',
      BWFB_FILE,
      'berrywalk-feedback-acf'
    );
  } else {
    return;
  }

  // 기본 브랜치(main/master)에 맞게 설정
  if (method_exists($up, 'setBranch')) $up->setBranch('main');
});
