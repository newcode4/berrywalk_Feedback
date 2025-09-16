<?php
if (!defined('ABSPATH')) exit;

add_shortcode('bw_feedback_form', function(){
  $rep_id = intval($_GET['rep'] ?? 0);
  if (!$rep_id) return '<div class="bwf-form"><p>대표 정보가 없습니다. 링크를 확인해주세요.</p></div>';

  $q = get_user_meta($rep_id,'bwf_questions',true);
  if (!$q) return '<div class="bwf-form"><p>등록된 대표 질문이 없습니다.</p></div>';

  $company = get_user_meta($rep_id,'company_name',true);

  // 응답자 정보(자동 입력/참고)
  $me = is_user_logged_in() ? wp_get_current_user() : null;
  $age = $me ? get_user_meta($me->ID,'age_range',true) : '';
  $gender = $me ? get_user_meta($me->ID,'gender',true) : '';
  $exp = $me ? get_user_meta($me->ID,'experience',true) : '';
  $src = $me ? get_user_meta($me->ID,'source',true) : '';

  if (isset($_POST['bwf_submit_feedback'])) {
    $answers = [];
    foreach(['first_impression','target_fit','competitor','buy_reason','recommend','q1','q2','q3'] as $k){
      $answers[$k] = sanitize_textarea_field($_POST[$k] ?? '');
      if (mb_strlen($answers[$k]) < 100) $err = '모든 문항은 최소 100자 이상 입력해주세요.';
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
      return '<div class="bwf-form"><p>피드백 감사해요! 소중한 의견은 서비스 개선에 바로 반영됩니다.</p></div>';
    }
  }

  ob_start(); ?>
  <div class="bwf-form">
    <h3><?php echo bwf_esc($company ?: '서비스'); ?> 피드백 설문</h3>
    <p class="bwf-hint">친구에게 조언하듯 편하게 적어주세요. 각 문항은 <strong>최소 100자</strong>입니다.</p>

    <?php if(!empty($me)): ?>
      <div class="bwf-bio">
        <span>연령대: <?php echo bwf_esc($age ?: '-'); ?></span>
        <span>성별: <?php echo bwf_esc($gender ?: '-'); ?></span>
        <span>경험: <?php echo bwf_esc($exp ?: '-'); ?></span>
        <span>유입: <?php echo bwf_esc($src ?: '-'); ?></span>
      </div>
    <?php endif; ?>

    <?php if(!empty($err)) echo '<p style="color:#ef4444">'.$err.'</p>'; ?>

    <form method="post" class="bwf-grid">

      <div class="bwf-col-full">
        <label>1) 첫인상 및 서비스 평가 <span class="bwf-required">*</span></label>
        <textarea name="first_impression" required data-minlength="100"
          placeholder="[<?php echo bwf_esc($company ?: '회사명'); ?>]를 처음 봤을 때 어떤 느낌이었나요? 무엇이 헷갈렸나요?"></textarea>
        <p class="bwf-hint">예: 친근하지만 무엇을 하는지 한눈에 안 들어왔다 등</p>
        <div class="bwf-counter"></div>
      </div>

      <div class="bwf-col-full">
        <label>2) 대표가 가정한 타겟과 일치하나요? <span class="bwf-required">*</span></label>
        <textarea name="target_fit" required data-minlength="100"
          placeholder="대표가 상정한 타겟과 실제로 맞았나요? 더 맞는 타겟이 있다면 누구일까요?"></textarea>
        <div class="bwf-counter"></div>
      </div>

      <div class="bwf-col-full">
        <label>3) 경쟁사 및 차별점 분석 <span class="bwf-required">*</span></label>
        <textarea name="competitor" required data-minlength="100"
          placeholder="아는 경쟁사와 비교했을 때 가장 큰 장점/아쉬움은 무엇인가요?"></textarea>
        <div class="bwf-counter"></div>
      </div>

      <div class="bwf-col-full">
        <label>4) 꼭 사야 할 이유/장애 요인 <span class="bwf-required">*</span></label>
        <textarea name="buy_reason" required data-minlength="100"
          placeholder="왜 이 서비스를 이용해야 하나요? 망설이는 이유는? 개선되면 구매할 의향은?"></textarea>
        <div class="bwf-counter"></div>
      </div>

      <div class="bwf-col-full">
        <label>5) 추천 의향 <span class="bwf-required">*</span></label>
        <textarea name="recommend" required data-minlength="100"
          placeholder="지인에게 추천할 건가요? 아니라면 어떤 점이 개선되면 추천하겠나요?"></textarea>
        <div class="bwf-counter"></div>
      </div>

      <div class="bwf-col-full"><hr></div>
      <div class="bwf-col-full"><strong>대표님 맞춤 질문</strong></div>

      <div class="bwf-col-full">
        <label><?php echo bwf_esc($q['q1'] ?? '맞춤 질문 1'); ?> <span class="bwf-required">*</span></label>
        <textarea name="q1" required data-minlength="100" placeholder="자유롭게 서술"></textarea>
        <div class="bwf-counter"></div>
      </div>
      <div class="bwf-col-full">
        <label><?php echo bwf_esc($q['q2'] ?? '맞춤 질문 2'); ?> <span class="bwf-required">*</span></label>
        <textarea name="q2" required data-minlength="100" placeholder="자유롭게 서술"></textarea>
        <div class="bwf-counter"></div>
      </div>
      <div class="bwf-col-full">
        <label><?php echo bwf_esc($q['q3'] ?? '맞춤 질문 3'); ?> <span class="bwf-required">*</span></label>
        <textarea name="q3" required data-minlength="100" placeholder="자유롭게 서술"></textarea>
        <div class="bwf-counter"></div>
      </div>

      <div class="bwf-col-full bwf-actions">
        <button type="submit" name="bwf_submit_feedback">제출</button>
        <p class="bwf-hint">각 문항은 최소 100자입니다.</p>
      </div>
    </form>
  </div>
  <?php return ob_get_clean();
});
