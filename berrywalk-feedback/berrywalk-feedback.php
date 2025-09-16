<?php
/**
 * Plugin Name: Berrywalk Feedback
 * Description: 대표 질문 수집 → 고객 서술형 피드백 → 관리자 검토까지 한 번에 연결하는 MVP 플러그인.
 * Version: 0.2.7
 * Author: Berrywalk
 */

if (!defined('ABSPATH')) exit;

define('BWF_VER', '0.2.7');
define('BWF_DIR', plugin_dir_path(__FILE__));
define('BWF_URL', plugin_dir_url(__FILE__));

/** 업데이트 체크러 (GitHub) */
require_once __DIR__ . '/plugin-update-checker/plugin-update-checker.php';
use YahnisElsts\PluginUpdateChecker\v5\PucFactory;
try {
  $updateChecker = PucFactory::buildUpdateChecker(
    'https://github.com/newcode4/berrywalk_Feedback/',
    __FILE__,
    'berrywalk-feedback'
  );
  $updateChecker->setBranch('main');

  $updateChecker->getVcsApi()->enableReleaseAssets();
} catch (Throwable $e) {}

/** includes */
require_once BWF_DIR.'includes/helper.php';
require_once BWF_DIR.'includes/signup.php';              // ✅ 가입 폼
require_once BWF_DIR.'includes/representative-form.php'; // 대표 질문지
require_once BWF_DIR.'includes/feedback-form.php';       // 고객 피드백 (저장 단일화)
require_once BWF_DIR.'includes/crm.php';                 // CRM 테이블
require_once BWF_DIR.'includes/admin-users.php';         // ✅ 사용자 화면(컬럼/프로필)

/** Assets */
add_action('wp_enqueue_scripts', function () {
  wp_register_style ('bwf-forms',    BWF_URL.'public/css/style.css', [], BWF_VER);
  wp_register_script('bwf-js',        BWF_URL.'public/js/feedback.js', ['jquery'], BWF_VER, true);
  wp_register_script('bwf-owner',     BWF_URL.'public/js/feedback.js', ['jquery'], BWF_VER, true);
  wp_register_script('bwf-feedback',  BWF_URL.'public/js/feedback.js', ['jquery'], BWF_VER, true);
});
