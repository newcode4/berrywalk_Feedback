<?php
if (!defined('ABSPATH')) exit;

add_shortcode('bw_owner_form', function(){
  if (!is_user_logged_in()) return '<div class="bwf-form"><p>로그인 후 이용해주세요.</p></div>';

  $uid = get_current_user_id();
  $saved = get_user_meta($uid,'bwf_questions',true);

  if (isset($_POST['bwf_save_questions'])) {
    $questions = [
      'problem' => sanitize_textarea_field($_POST['problem'] ?? ''),
      'q1' => sanitize_text_field($_POST['q1'] ?? ''),
      'q2' => sanitize_text_field($_POST['q2'] ?? ''),
      'q3' => sanitize_text_field($_POST['q3'] ?? ''),
    ];
    if (!$questions['problem'] || !$questions['q1'] || !$questions['q2'] || !$questions['q3']) {
      $msg = '<p style="color:#ef4444">모든 항목을 입력해주세요.</p>';
    } else {
      update_user_meta($uid,'bwf_questions',$questions);
      $saved = $questions;
      $msg = '<p style="color:#10b981">질문지가 저장되었습니다.</p>';
    }
  }

  // 피드백 링크
  $feedback_url = add_query_arg(['rep'=>$uid], get_permalink(get_page_by_path('customer-feedback')) ?: home_url('/customer-feedback/'));

  ob_start(); ?>
  <div class="bwf-form">
    <h3>대표님 핵심 질문 등록</h3>
    <?php if(!empty($msg)) echo $msg; ?>
    <form method="post">
      <textarea name="problem" placeholder="현재 비즈니스에서 가장 큰 고민을 서술해주세요.*" required><?php echo esc_textarea($saved['problem'] ?? ''); ?></textarea>
      <input type="text" name="q1" placeholder="맞춤 질문 1*" value="<?php echo esc_attr($saved['q1'] ?? ''); ?>" required>
      <input type="text" name="q2" placeholder="맞춤 질문 2*" value="<?php echo esc_attr($saved['q2'] ?? ''); ?>" required>
      <input type="text" name="q3" placeholder="맞춤 질문 3*" value="<?php echo esc_attr($saved['q3'] ?? ''); ?>" required>
      <button type="submit" name="bwf_save_questions">저장</button>
    </form>

    <div style="margin-top:12px;">
      <strong>피드백 링크</strong><br>
      <input type="text" value="<?php echo esc_attr($feedback_url); ?>" readonly onclick="this.select();" />
      <p style="font-size:12px;color:#6b7280">이 링크를 고객에게 공유하세요.</p>
    </div>
  </div>
  <?php return ob_get_clean();
});
