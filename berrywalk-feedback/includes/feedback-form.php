<?php
if (!defined('ABSPATH')) exit;

// 피드백 작성
add_shortcode('bwf_feedback_form', function(){
  $rep_id = intval($_GET['rep'] ?? 0);
  if(!$rep_id) return '<p>유효하지 않은 링크입니다.</p>';

  $questions = get_user_meta($rep_id,'bwf_questions',true);
  if(!$questions) return '<p>대표님 질문지가 없습니다.</p>';

  if(isset($_POST['bwf_submit_feedback'])){
    $data = [
      'rep' => $rep_id,
      'user' => get_current_user_id(),
      'answers' => [
        'first_impression'=>sanitize_textarea_field($_POST['first_impression'] ?? ''),
        'target_fit'=>sanitize_textarea_field($_POST['target_fit'] ?? ''),
        'competitor'=>sanitize_textarea_field($_POST['competitor'] ?? ''),
        'buy_reason'=>sanitize_textarea_field($_POST['buy_reason'] ?? ''),
        'recommend'=>sanitize_textarea_field($_POST['recommend'] ?? ''),
        'q1'=>sanitize_textarea_field($_POST['q1'] ?? ''),
        'q2'=>sanitize_textarea_field($_POST['q2'] ?? ''),
        'q3'=>sanitize_textarea_field($_POST['q3'] ?? ''),
      ],
      't'=>current_time('mysql')
    ];
    $all = get_option('bwf_feedbacks',[]);
    $all[] = $data;
    update_option('bwf_feedbacks',$all,false);
    echo '<p>피드백이 제출되었습니다. 감사합니다!</p>';
  }

  ob_start(); ?>
  <form method="post" class="bwf-form">
    <h3>공통 질문</h3>
    <textarea name="first_impression" placeholder="첫인상" required></textarea>
    <textarea name="target_fit" placeholder="타겟 적합성" required></textarea>
    <textarea name="competitor" placeholder="경쟁사와 차별점" required></textarea>
    <textarea name="buy_reason" placeholder="구매 이유/장애 요인" required></textarea>
    <textarea name="recommend" placeholder="추천 의향" required></textarea>

    <h3>대표님 맞춤 질문</h3>
    <textarea name="q1" placeholder="<?php echo esc_attr($questions['q1']); ?>" required></textarea>
    <textarea name="q2" placeholder="<?php echo esc_attr($questions['q2']); ?>" required></textarea>
    <textarea name="q3" placeholder="<?php echo esc_attr($questions['q3']); ?>" required></textarea>

    <button type="submit" name="bwf_submit_feedback">제출</button>
  </form>
  <?php return ob_get_clean();
});
