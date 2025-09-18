<?php
/**
 * Plugin Name: BW Owner Suite (light)
 * Description: 대표 질문지 + 회원가입(대표 전용) + 마이페이지. ACF 기반 경량 플러그인.
 * Version: 1.4.5
 * Author: Berrywalk
 */
if (!defined('ABSPATH')) exit;

define('BWOS_VER', '1.4.5');
define('BWOS_DIR', plugin_dir_path(__FILE__));
define('BWOS_URL', plugin_dir_url(__FILE__));

// GitHub 업데이트 확인 (admin에서만 동작)
if ( is_admin() ) {
  require_once BWOS_DIR.'lib/plugin-update-checker/plugin-update-checker.php';
  $bwos_uc = Puc_v4_Factory::buildUpdateChecker(
    'https://github.com/newcode4/berrywalk_Feedback', // 깃허브 저장소
    __FILE__,                                         // 이 플러그인 메인 파일
    'bw-owner-suite-acf'                              // 플러그인 슬러그(폴더명)
  );
  $bwos_uc->setBranch('main'); // 기본 브랜치가 main일 때
  // 릴리스 Asset(zip) 사용 시:
  $api = $bwos_uc->getVcsApi();
  if (method_exists($api, 'enableReleaseAssets')) {
    $api->enableReleaseAssets();
  }
}


// 텔레그램
define('BW_TG_BOT_TOKEN', '8060380419:AAFDlmr9TmX1K5Ocagl6WafN5s2O6oVnvdw');  // BotFather에서 발급
define('BW_TG_CHAT_ID',  '@berrywalk_ownerfeedback');                  // 개인(@me) 또는 채널/그룹 ID

require_once BWOS_DIR.'includes/cpt-admin.php';
require_once BWOS_DIR.'includes/form.php';
require_once BWOS_DIR.'includes/signup.php';
require_once BWOS_DIR.'includes/save.php';
require_once BWOS_DIR.'includes/view-list.php';
require_once BWOS_DIR.'includes/acf-fields.php'; // 관리자 ACF 메타박스(5문항)

add_action('wp_enqueue_scripts', function(){
  $css = BWOS_URL.'assets/owner-form.css';
  $js  = BWOS_URL.'assets/owner-form.js';
  wp_register_style ('bwos-form', $css, [], BWOS_VER);
  wp_register_style ('bwf-forms', $css, [], BWOS_VER); // alias
  wp_register_script('bwos-form', $js,  ['jquery'], BWOS_VER, true);
  wp_register_script('bwf-forms', $js,  ['jquery'], BWOS_VER, true); // alias
});
