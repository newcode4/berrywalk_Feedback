<?php
/**
 * Plugin Name: Berrywalk Feedback
 * Description: 대표 질문 수집 → 고객 서술형 피드백 → 관리자 검토까지 한 번에 연결하는 MVP 플러그인.
 * Version: 0.2.3
 * Author: Berrywalk
 */

if (!defined('ABSPATH')) exit;

define('BWF_VER', '0.2.3');
define('BWF_DIR', plugin_dir_path(__FILE__));
define('BWF_URL', plugin_dir_url(__FILE__));

/** ── (선택) GitHub 업데이트 체크러 ───────────────────────── */
require_once __DIR__ . '/plugin-update-checker/plugin-update-checker.php';
use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

// ⚠️ 저장소 정보를 정확히 입력하세요.
$repoUser = 'newcode4';
$repoName = 'berrywalk_Feedback'; // 실제 저장소명
try {
    $bwfUpdateChecker = PucFactory::buildUpdateChecker(
        "https://github.com/{$repoUser}/{$repoName}/",
        __FILE__,
        'berrywalk-feedback'
    );
    $bwfUpdateChecker->setBranch('main');
} catch (Throwable $e) {
    // 조용히 무시(관리자 로그 오염 방지)
}

/** ── includes ───────────────────────────────────────────── */
require_once BWF_DIR.'includes/helper.php';
require_once BWF_DIR.'includes/shortcodes.php';
require_once BWF_DIR.'includes/representative-form.php';
require_once BWF_DIR.'includes/feedback-form.php';
require_once BWF_DIR.'includes/crm.php';

/** ── assets ─────────────────────────────────────────────── */
add_action('wp_enqueue_scripts', function () {
  wp_register_style ('bwf-forms', BWF_URL.'public/css/style.css', [], BWF_VER);
  wp_register_script('bwf-owner', BWF_URL.'public/js/feedback.js', ['jquery'], BWF_VER, true);
  wp_register_script('bwf-js',    BWF_URL.'public/js/feedback.js', ['jquery'], BWF_VER, true);
});
