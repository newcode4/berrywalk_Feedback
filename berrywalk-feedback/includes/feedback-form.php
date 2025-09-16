<?php
if (!defined('ABSPATH')) exit;

add_shortcode('bw_feedback_form', function(){
  $rep_id = intval($_GET['rep'] ?? 0);
  if (!$rep_id) return '<div class="bwf-form"><p>대표님 식별 정보가 없습니다. 링크를 다시 확인해주세요.</p></div>';

  $questions = get_user_meta($rep_id,'bwf_questions',true);
  if (!$questions) return '<div class="bwf-form"><p>등록된 대표 질문이 없습니다.</p></div>';

  if (isset($_POST['bwf_submit_feedback'])) {
    $answers = [
      'first_impression'=>sanitize_textarea_field($_POST['first_impression'] ?? ''),
      'target_fit'=>sanitize_textarea_field($_POST['target_fit'] ?? ''),
      'competitor'=>sanitize_textarea_field($_POST['competitor'] ?? ''),
      'buy_reason'=>sanitize_textarea_field($_POST['buy_reason'] ?? ''),
      'recommend'=>sanitize_textarea_field($_POST['recommend'] ?? ''),
      'q1'=>sanitize_textarea_field($_POST['q1'] ?? ''),
      'q2'=>sanitize_textarea_field($_POST['q2'] ?? ''),
      'q3'=>sanitize_textarea_field($_POST['q3'] ?? ''),
    ];
    // 길이 검증
    foreach($answers as $k=>$v){
      if (mb_strlen($v) < 100) {
        $err = '모든 문항은 최소 100자 이상 입력해주세요.';
        break;
      }
    }
    if (empty($err)) {
      $all = get_option('bwf_feedbacks',[]);
      $all[] = [
        'rep' => $rep_id,
        'user'=> get_current_user_id(),
        't'   => current_time('mysql'),
        'answers' => $answers
      ];
      update_option('bwf_feedbacks',$all,false);
      return '<div class="bwf-form"><p>피드백이 제출되었습니다. 감사합니다!</p></div>';
    }
  }

  ob_start(); ?>
  <form method="post" class="bwf-form">
    <?php if(!empty($err)) echo '<p style="color:#ef4444">'.$err.'</p>'; ?>

    <h3>공통 질문</h3>
    <textarea name="first_impression" placeholder="첫인상: 홈페이지/서비스를 봤을 때 느낀 점을 서술해주세요(최소 100자)" required></textarea>
    <textarea name="target_fit" placeholder="타겟 적합성: 대표가 가정한 타겟과 실제로 맞는지, 혹은 더 맞는 타겟은 누구인지(최소 100자)" required></textarea>
    <textarea name="competitor" placeholder="경쟁사/차별점: 알고 있는 유사 서비스와 비교해 장단점을 서술(최소 100자)" required></textarea>
    <textarea name="buy_reason" placeholder="구매 의사: 꼭 사야 할 이유/장애 요인/개선되면 구매할 의향(최소 100자)" required></textarea>
    <textarea name="recommend" placeholder="추천 의향: 추천 가능/불가 이유와 개선점(최소 100자)" required></textarea>

    <h3>대표님 맞춤 질문</h3>
    <label><?php echo esc_html($questions['q1']); ?></label>
    <textarea name="q1" placeholder="자유롭게 서술(최소 100자)" required></textarea>
    <label><?php echo esc_html($questions['q2']); ?></label>
    <textarea name="q2" placeholder="자유롭게 서술(최소 100자)" required></textarea>
    <label><?php echo esc_html($questions['q3']); ?></label>
    <textarea name="q3" placeholder="자유롭게 서술(최소 100자)" required></textarea>

    <button type="submit" name="bwf_submit_feedback">제출</button>
  </form>
  <?php return ob_get_clean();
});
