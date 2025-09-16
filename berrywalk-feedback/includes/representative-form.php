<?php
if (!defined('ABSPATH')) exit;

// 대표 질문 등록
add_shortcode('bwf_representative_form', function(){
  if(!is_user_logged_in()) return '<p>로그인 후 이용해주세요.</p>';
  $uid = get_current_user_id();

  if(isset($_POST['bwf_save_questions'])){
    $questions = [
      'problem' => sanitize_textarea_field($_POST['problem'] ?? ''),
      'q1' => sanitize_textarea_field($_POST['q1'] ?? ''),
      'q2' => sanitize_textarea_field($_POST['q2'] ?? ''),
      'q3' => sanitize_textarea_field($_POST['q3'] ?? '')
    ];
    update_user_meta($uid,'bwf_questions',$questions);
    echo '<p>질문지가 저장되었습니다. 피드백 URL: '.esc_url(add_query_arg(['rep'=>$uid],home_url('/feedback/'))).'</p>';
  }

  ob_start(); ?>
  <form method="post" class="bwf-form">
    <textarea name="problem" placeholder="현재 사업의 가장 큰 고민" required></textarea>
    <input type="text" name="q1" placeholder="질문 1" required>
    <input type="text" name="q2" placeholder="질문 2" required>
    <input type="text" name="q3" placeholder="질문 3" required>
    <button type="submit" name="bwf_save_questions">저장</button>
  </form>
  <?php return ob_get_clean();
});
