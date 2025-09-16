<?php
if (!defined('ABSPATH')) exit;

add_shortcode('bw_feedback_form', function () {
  $rep_id = intval($_GET['rep'] ?? 0);
  if (!$rep_id) {
    return '<div class="bwf-form"><p>대표 정보가 없습니다.</p></div>';
  }

  $questions = get_user_meta($rep_id, 'bwf_questions', true);
  if (!$questions || !is_array($questions)) {
    return '<div class="bwf-form"><p>대표님 질문지가 없습니다.</p></div>';
  }

  $err = '';
  if (!empty($_POST['bwf_submit_feedback'])) {
    // 서버측 최소 100자 검증
    $answers = [
      'first_impression' => bwf_textarea('first_impression'),
      'target_fit'       => bwf_textarea('target_fit'),
      'competitor'       => bwf_textarea('competitor'),
      'buy_reason'       => bwf_textarea('buy_reason'),
      'recommend'        => bwf_textarea('recommend'),
      'q1'               => bwf_textarea('q1'),
      'q2'               => bwf_textarea('q2'),
      'q3'               => bwf_textarea('q3'),
    ];
    foreach ($answers as $k => $v) {
      if (mb_strlen(trim($v)) < 100) { $err = '모든 문항은 최소 100자 이상 입력해주세요.'; break; }
    }

    if (!$err) {
      $data = [
        'rep'     => $rep_id,
        'user'    => get_current_user_id(),
        'answers' => $answers,
        't'       => current_time('mysql'),
      ];
      $all = get_option('bwf_feedbacks', []);
      $all[] = $data;
      update_option('bwf_feedbacks', $all, false);

      return '<div class="bwf-form"><p>피드백이 제출되었습니다. 감사합니다!</p></div>';
    }
  }

  ob_start(); ?>
  <div class="bwf-form">
    <?php if ($err): ?><p class="bwf-error-text"><?php echo esc_html($err); ?></p><?php endif; ?>

    <form method="post">
      <div class="bwf-col-full">
        <label>[첫인상] <?php echo bwf_esc($questions['company'] ?? '서비스'); ?>를 처음 봤을 때 느낌은? <span class="bwf-required">*</span></label>
        <textarea name="first_impression" required data-minlength="100" placeholder="예: 친근했지만, 첫 화면에서 무엇을 하는 서비스인지 한눈에 안 들어왔어요."></textarea>
        <div class="bwf-counter"></div>
      </div>

      <div class="bwf-col-full">
        <label>대표가 상정한 타겟과 실제로 맞았나요? <span class="bwf-required">*</span></label>
        <textarea name="target_fit" required data-minlength="100" placeholder="예: 20대 대학생에게 적합해 보였고, 저는 그 대상과 유사해서 공감됐습니다."></textarea>
        <div class="bwf-counter"></div>
      </div>

      <div class="bwf-col-full">
        <label>경쟁사 대비 장점/아쉬움은? <span class="bwf-required">*</span></label>
        <textarea name="competitor" required data-minlength="100" placeholder="예: A사는 저렴하지만 품질이 낮고, 여긴 영상이 깔끔해서 좋았어요. 다만 가격은 조금 높게 느껴졌습니다."></textarea>
        <div class="bwf-counter"></div>
      </div>

      <div class="bwf-col-full">
        <label>구매를 망설이게 하는 가장 큰 요인과 개선점은? <span class="bwf-required">*</span></label>
        <textarea name="buy_reason" required data-minlength="100" placeholder="예: 가격/체험 부족 때문에 고민될 것 같아요. 첫 달 할인이나 무료 체험이 있으면 바로 결제할 듯해요."></textarea>
        <div class="bwf-counter"></div>
      </div>

      <div class="bwf-col-full">
        <label>지인 추천 의향과 조건은? <span class="bwf-required">*</span></label>
        <textarea name="recommend" required data-minlength="100" placeholder="예: 지금은 콘텐츠가 더 필요해 보입니다. 라이브 코칭이 추가되면 적극 추천할 것 같아요."></textarea>
        <div class="bwf-counter"></div>
      </div>

      <hr>

      <div class="bwf-col-full">
        <label><?php echo bwf_esc($questions['q1'] ?? '맞춤 질문 1'); ?> <span class="bwf-required">*</span></label>
        <textarea name="q1" required data-minlength="100" placeholder="자유롭게 서술"></textarea>
        <div class="bwf-counter"></div>
      </div>

      <div class="bwf-col-full">
        <label><?php echo bwf_esc($questions['q2'] ?? '맞춤 질문 2'); ?> <span class="bwf-required">*</span></label>
        <textarea name="q2" required data-minlength="100" placeholder="자유롭게 서술"></textarea>
        <div class="bwf-counter"></div>
      </div>

      <div class="bwf-col-full">
        <label><?php echo bwf_esc($questions['q3'] ?? '맞춤 질문 3'); ?> <span class="bwf-required">*</span></label>
        <textarea name="q3" required data-minlength="100" placeholder="자유롭게 서술"></textarea>
        <div class="bwf-counter"></div>
      </div>

      <div class="bwf-col-full bwf-actions">
        <button type="submit" name="bwf_submit_feedback">제출</button>
        <p class="bwf-hint">각 문항은 최소 100자입니다.</p>
      </div>
    </form>
  </div>
  <?php
  
  return ob_get_clean();
});
