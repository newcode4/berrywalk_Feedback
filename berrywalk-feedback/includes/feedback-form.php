<?php
if (!defined('ABSPATH')) exit;

add_shortcode('bw_feedback_form', function () {
  wp_enqueue_style('bwf-forms');
  wp_enqueue_script('bwf-feedback');

  $rep_id = intval($_GET['rep'] ?? 0);
  if (!$rep_id) return '<div class="bwf-form"><p>대표 정보가 없습니다.</p></div>';

  $q = get_user_meta($rep_id,'bwf_questions',true);
  if (!$q || !is_array($q)) return '<div class="bwf-form"><p>대표님 질문지가 없습니다.</p></div>';

  $err = '';
  if (!empty($_POST['bwf_submit_feedback'])) {
    $answers = [];
    foreach(['first_impression','target_fit','competitor','buy_reason','recommend','q1','q2','q3'] as $k){
      $answers[$k] = sanitize_textarea_field($_POST[$k] ?? '');
      if (mb_strlen(trim($answers[$k])) < 100) $err = '모든 문항은 최소 100자 이상 입력해주세요.';
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
      update_option('bwf_feedbacks', $all, false); // ✅ CRM과 동일 저장소

      return '<div class="bwf-form"><p>피드백이 제출되었습니다. 감사합니다!</p></div>';
    }
  }

  ob_start(); ?>
  <div class="bwf-form">
    <div class="bwf-topcount">작성 <span id="bwf-answered">0</span>/<span id="bwf-total">0</span> 문항</div>
    <div id="bwf-progress"><div class="bwf-bar"></div></div>
    <?php if ($err): ?><p class="bwf-error-text"><?php echo esc_html($err); ?></p><?php endif; ?>

    <h3>대표님 핵심 질문지</h3>
    <p class="bwf-hint">각 문항은 <strong>최소 100자</strong>입니다. 솔직하고 구체적인 경험을 적어주세요.</p>

    <form method="post" class="bwf-grid">
      <!-- ① 현재 비즈니스에서 가장 큰 고민 -->
      <div class="bwf-col-full">
        <label>현재 비즈니스에서 가장 큰 고민은 무엇인가요? <span class="bwf-required">*</span></label>
        <small class="bwf-guide">예: “신규 고객 유입이 어렵다”, “재구매율이 낮다”, “차별점이 불분명하다” 등</small>
        <textarea name="first_impression" required data-minlength="100" placeholder="느낀 점을 구체적으로 적어주세요."></textarea>
        <div class="bwf-helper"><span class="bwf-guide">최소 100자</span><span class="bwf-counter"></span></div>
      </div>

      <!-- ② 어떤 문제를 해결? -->
      <div class="bwf-col-full">
        <label>우리 서비스가 고객의 ‘어떤 문제’를 해결하나요? <span class="bwf-required">*</span></label>
        <small class="bwf-guide">한두 문장으로 핵심 이점을 적어주세요. 예: “바쁜 직장인에게 집밥처럼 건강한 한 끼를 배달”</small>
        <textarea name="target_fit" required data-minlength="100"></textarea>
        <div class="bwf-helper"><span class="bwf-guide">최소 100자</span><span class="bwf-counter"></span></div>
      </div>

      <!-- ③ 누가, 왜 우리를 선택? -->
      <div class="bwf-col-full">
        <label>이 서비스를 ‘누가’ 이용해야 하나요? 왜 우리를 선택하나요? <span class="bwf-required">*</span></label>
        <small class="bwf-guide">예: “운동은 하고 싶지만 시간/비용이 부족한 20대 대학생…“</small>
        <textarea name="competitor" required data-minlength="100"></textarea>
        <div class="bwf-helper"><span class="bwf-guide">최소 100자</span><span class="bwf-counter"></span></div>
      </div>

      <!-- ④ 1:1로 단 한 가지 묻는다면 -->
      <div class="bwf-col-full">
        <label>타겟 고객 1:1로 단 한 가지를 묻는다면? <span class="bwf-required">*</span></label>
        <small class="bwf-guide">예: “우리 서비스의 어떤 점이 가장 도움이 되었나요?”</small>
        <textarea name="buy_reason" required data-minlength="100"></textarea>
        <div class="bwf-helper"><span class="bwf-guide">최소 100자</span><span class="bwf-counter"></span></div>
      </div>

      <!-- ⑤ 경쟁사 대비 차별점 -->
      <div class="bwf-col-full">
        <label>현재 경쟁사와 차별점은? <span class="bwf-required">*</span></label>
        <small class="bwf-guide">예: “A사는 저렴하지만 품질 낮음, B사는 고품질이나 비쌈… 우리는 합리적 가격+높은 만족”</small>
        <textarea name="recommend" required data-minlength="100"></textarea>
        <div class="bwf-helper"><span class="bwf-guide">최소 100자</span><span class="bwf-counter"></span></div>
      </div>

      <div class="bwf-col-full"><hr></div>

      <!-- 대표 맞춤 질문 3가지 -->
      <div class="bwf-col-full">
        <label><?php echo bwf_esc($q['q1'] ?? '맞춤 질문 1'); ?> <span class="bwf-required">*</span></label>
        <textarea name="q1" required data-minlength="100" placeholder="자유 서술"></textarea>
        <div class="bwf-helper"><span class="bwf-guide">최소 100자</span><span class="bwf-counter"></span></div>
      </div>
      <div class="bwf-col-full">
        <label><?php echo bwf_esc($q['q2'] ?? '맞춤 질문 2'); ?> <span class="bwf-required">*</span></label>
        <textarea name="q2" required data-minlength="100"></textarea>
        <div class="bwf-helper"><span class="bwf-guide">최소 100자</span><span class="bwf-counter"></span></div>
      </div>
      <div class="bwf-col-full">
        <label><?php echo bwf_esc($q['q3'] ?? '맞춤 질문 3'); ?> <span class="bwf-required">*</span></label>
        <textarea name="q3" required data-minlength="100"></textarea>
        <div class="bwf-helper"><span class="bwf-guide">최소 100자</span><span class="bwf-counter"></span></div>
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
