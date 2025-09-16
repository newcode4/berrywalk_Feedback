<?php
/*
Plugin Name: Berrywalk Feedback
Description: 대표 질문 등록 + 피드백 설문 + CRM
Version: 0.1.3
Author: Berrywalk
*/

if (!defined('ABSPATH')) exit;

// 자동 업데이트 (GitHub 연동)
require_once __DIR__ . '/plugin-update-checker/plugin-update-checker.php';
use YahnisElsts\PluginUpdateChecker\v5\PucFactory;
$updateChecker = PucFactory::buildUpdateChecker(
    'https://github.com/newcode4/berrywalk-feedback', // 깃헙 주소
    __FILE__,
    'berrywalk-feedback'
);
$updateChecker->setBranch('main');
$updateChecker->getVcsApi()->enableReleaseAssets();

// 상수
define('BWF_VER','0.1.0');
define('BWF_DIR', plugin_dir_path(__FILE__));
define('BWF_URL', plugin_dir_url(__FILE__));

// 파일 로드
require_once BWF_DIR.'includes/helpers.php';
require_once BWF_DIR.'includes/signup.php';
require_once BWF_DIR.'includes/representative-form.php';
require_once BWF_DIR.'includes/feedback-form.php';
require_once BWF_DIR.'includes/crm.php';

// CSS/JS 등록
add_action('wp_enqueue_scripts', function(){
  wp_enqueue_style('bwf-style', BWF_URL.'public/css/style.css', [], BWF_VER);
  wp_enqueue_script('bwf-js', BWF_URL.'public/js/feedback.js', ['jquery'], BWF_VER, true);
});
