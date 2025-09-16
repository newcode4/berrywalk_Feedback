<?php
if (!defined('ABSPATH')) exit;

// 대표 회원가입
add_shortcode('bwf_signup_representative', function(){
  ob_start(); ?>
  <form method="post" class="bwf-form">
    <input type="hidden" name="bwf_role" value="representative">
    <input type="text" name="company_name" placeholder="회사명" required>
    <input type="text" name="industry" placeholder="업종" required>
    <input type="number" name="employees" placeholder="직원 수">
    <input type="url" name="site_url" placeholder="홈페이지 URL">
    <input type="text" name="source" placeholder="알게 된 경로">
    <input type="email" name="user_email" placeholder="이메일" required>
    <input type="password" name="user_pass" placeholder="비밀번호" required>
    <button type="submit" name="bwf_register">회원가입</button>
  </form>
  <?php return ob_get_clean();
});

// 피드백자 회원가입
add_shortcode('bwf_signup_feedback', function(){
  ob_start(); ?>
  <form method="post" class="bwf-form">
    <input type="hidden" name="bwf_role" value="feedback_provider">
    <input type="text" name="age_range" placeholder="연령대">
    <select name="gender"><option value="">성별</option><option>남</option><option>여</option></select>
    <input type="text" name="experience" placeholder="카테고리 경험 (예/아니오)">
    <input type="text" name="source" placeholder="알게 된 경로">
    <input type="email" name="user_email" placeholder="이메일" required>
    <input type="password" name="user_pass" placeholder="비밀번호" required>
    <button type="submit" name="bwf_register">회원가입</button>
  </form>
  <?php return ob_get_clean();
});

// 처리 로직
add_action('init', function(){
  if(isset($_POST['bwf_register'])){
    $email = sanitize_email($_POST['user_email']);
    $pass  = $_POST['user_pass'];
    $role  = $_POST['bwf_role'];

    $user_id = wp_create_user($email, $pass, $email);
    if(!is_wp_error($user_id)){
      wp_update_user(['ID'=>$user_id,'role'=>$role]);
      foreach($_POST as $k=>$v){
        if(in_array($k,['bwf_register','user_email','user_pass','bwf_role'])) continue;
        update_user_meta($user_id,$k,sanitize_text_field($v));
      }
      wp_redirect(home_url('/welcome/')); exit;
    }
  }
});
