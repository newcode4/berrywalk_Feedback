<?php
if (!defined('ABSPATH')) exit;

add_shortcode('bw_feedback_form', function () {
  wp_enqueue_style('bwf-forms');
  wp_enqueue_script('bwf-feedback');

  $rep_id = intval($_GET['rep'] ?? 0);
if (!$rep_id) return '<p>유효하지 않은 요청입니다.</p>';

    // ✅ 대표가 저장한 질문 세트 로드
    $qset = get_user_meta($rep_id, 'bwf_questions', true);
    $qset = is_array($qset) ? $qset : [];
    $problem   = $qset['problem']        ?? '';
    $value     = $qset['value']          ?? '';
    $ideal     = $qset['ideal_customer'] ?? '';
    $q1        = $qset['q1'] ?? '';
    $q2        = $qset['q2'] ?? '';
    $q3        = $qset['q3'] ?? '';
    $oneQ      = $qset['one_question']   ?? '';
    $diff      = $qset['competitors']    ?? '';

  // 대표가 작성한 3가지 질문 불러오기(예: user_meta 또는 post_meta에서 가져오는 부분은 기존 로직 유지)
  // 아래는 예시. 실제 저장 위치에 맞춰 수정하세요.
  $q1 = get_user_meta($rep_id, 'bw_q1', true);
  $q2 = get_user_meta($rep_id, 'bw_q2', true);
  $q3 = get_user_meta($rep_id, 'bw_q3', true);

  // 저장 처리
  if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['bwf_submit'])) {
    $answers = array_map('sanitize_textarea_field', $_POST['answer'] ?? []);
    $all = get_option('bwf_feedbacks', []);
    $all[] = [
      't' => wp_date('Y-m-d H:i:s'),
      'rep'     => $rep_id,
      'user'    => get_current_user_id(),
      'answers' => $answers,
    ];
    update_option('bwf_feedbacks', $all, false);
    wp_redirect(home_url('/feedback-thanks/'));
    exit;
  }

  ob_start(); ?>
  <form method="post" class="bwf-form">
    <div class="bwf-sticky bwf-col-full">
      <div class="bwf-topcount">작성 <span class="done">0</span>/<span class="total">0</span>문항</div>
      <div id="bwf-progress"><div class="bar"></div><span class="label"></span></div>
    </div>

    <h2 class="bwf-col-full">대표 질문 요약</h2>
    <ul class="bwf-rep">
    <li><b>① 가장 큰 고민:</b> <?php echo esc_html($problem); ?></li>
    <li><b>② 핵심 가치(대표 생각):</b> <?php echo esc_html($value); ?></li>
    <li><b>③ 이상적 타겟:</b> <?php echo esc_html($ideal); ?></li>
    <li><b>④-1 맞춤 질문:</b> <?php echo esc_html($q1); ?></li>
    <li><b>④-2 맞춤 질문:</b> <?php echo esc_html($q2); ?></li>
    <li><b>④-3 맞춤 질문:</b> <?php echo esc_html($q3); ?></li>
    <li><b>⑤ 1:1 한 가지:</b> <?php echo esc_html($oneQ); ?></li>
    <li><b>⑥ 경쟁사/차별:</b> <?php echo esc_html($diff); ?></li>
    </ul>


    <!-- 1 -->
    <div class="bwf-col-full bwf-field">
      <label>현재 비즈니스에서 가장 큰 고민은 무엇인가요? <span class="bwf-required">*</span></label>
      <textarea name="answer[biggest_pain]" data-minlength="100" rows="5" placeholder="예: 신규 고객 유입이 너무 어렵습니다. 광고 효율이 안 나와요."></textarea>
      <div class="bwf-helper"><span class="bwf-guide">최소 100자</span><span class="bwf-counter"></span></div>
    </div>

    <!-- 2 -->
    <div class="bwf-col-full bwf-field">
      <label>우리 서비스가 고객의 ‘어떤 문제’를 해결하나요? <span class="bwf-required">*</span></label>
      <textarea name="answer[problem_to_solve]" data-minlength="100" rows="5" placeholder="예: 바쁜 직장인에게 집밥처럼 건강한 한 끼를 배달해주는 것"></textarea>
      <div class="bwf-helper"><span class="bwf-guide">최소 100자</span><span class="bwf-counter"></span></div>
    </div>

    <!-- 3 -->
    <div class="bwf-col-full bwf-field">
      <label>이 서비스를 ‘누가’ 이용해야 하나요? 왜 우리를 선택하나요? <span class="bwf-required">*</span></label>
      <textarea name="answer[target_why]" data-minlength="100" rows="5" placeholder="예: 운동은 하고 싶지만 시간/비용이 부족한 20대 대학생…"></textarea>
      <div class="bwf-helper"><span class="bwf-guide">최소 100자</span><span class="bwf-counter"></span></div>
    </div>

    <!-- 4: 고객에게 물어보고 싶은 3가지(=1문항으로 카운트) -->
    <div class="bwf-col-full">
      <h3>고객에게 물어보고 싶은 3가지 (아래 3개가 모두 작성되면 1문항으로 계산)</h3>
    </div>
    <div class="bwf-col-full bwf-field">
      <textarea name="answer[q1]" data-minlength="100" data-group="ask3" rows="4" placeholder="질문 1"></textarea>
      <div class="bwf-helper"><span class="bwf-guide">최소 100자</span><span class="bwf-counter"></span></div>
    </div>
    <div class="bwf-col-full bwf-field">
      <textarea name="answer[q2]" data-minlength="100" data-group="ask3" rows="4" placeholder="질문 2"></textarea>
      <div class="bwf-helper"><span class="bwf-guide">최소 100자</span><span class="bwf-counter"></span></div>
    </div>
    <div class="bwf-col-full bwf-field">
      <textarea name="answer[q3]" data-minlength="100" data-group="ask3" rows="4" placeholder="질문 3"></textarea>
      <div class="bwf-helper"><span class="bwf-guide">최소 100자</span><span class="bwf-counter"></span></div>
    </div>

    <!-- 5 -->
    <div class="bwf-col-full bwf-field">
      <label>타겟 고객 1:1로 단 한 가지를 묻는다면? <span class="bwf-required">*</span></label>
      <textarea name="answer[one_question]" data-minlength="100" rows="4" placeholder="예: 우리 서비스의 어떤 점이 가장 도움이 되었나요?"></textarea>
      <div class="bwf-helper"><span class="bwf-guide">최소 100자</span><span class="bwf-counter"></span></div>
    </div>

    <!-- 6 -->
    <div class="bwf-col-full bwf-field">
      <label>경쟁사와의 차별점은? <span class="bwf-required">*</span></label>
      <textarea name="answer[differentiation]" data-minlength="100" rows="4" placeholder="예: A사는 저렴하지만 품질 낮음… 우리는 적정 가격에 높은 만족도…"></textarea>
      <div class="bwf-helper"><span class="bwf-guide">최소 100자</span><span class="bwf-counter"></span></div>
    </div>

    <div class="bwf-col-full bwf-actions">
      <button type="submit" name="bwf_submit">저장</button>
      <p class="bwf-hint">각 항목 최소 100자 이상 작성</p>
    </div>
  </form>
  <?php return ob_get_clean();
});
