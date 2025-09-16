<?php
if (!defined('ABSPATH')) exit;

define('BWF_FEEDBACK_PAGE_SLUG','customer-feedback'); // 필요시 너의 슬러그로 변경

add_shortcode('bw_owner_form', function(){
  if (!is_user_logged_in()) return '<div class="bwf-form"><p>로그인 후 이용해주세요.</p></div>';

  $uid = get_current_user_id();
  $saved = get_user_meta($uid,'bwf_questions', true) ?: [];
  $S = function($k){ return isset($_POST[$k]) ? $_POST[$k] : ($saved[$k] ?? ''); };

  $msg = '';
  if (isset($_POST['bwf_save_questions']) && wp_verify_nonce($_POST['bwf_nonce'],'bwf_owner_form')) {
    $data = [
        'problem'        => sanitize_textarea_field($_POST['problem'] ?? ''),
        'value'          => sanitize_textarea_field($_POST['value'] ?? ''),
        'ideal_customer' => sanitize_textarea_field($_POST['ideal_customer'] ?? ''),
        'q1' => sanitize_textarea_field($_POST['q1'] ?? ''),
        'q2' => sanitize_textarea_field($_POST['q2'] ?? ''),
        'q3' => sanitize_textarea_field($_POST['q3'] ?? ''),
        'one_question'   => sanitize_text_field($_POST['one_question'] ?? ''),
        'competitors'    => sanitize_textarea_field($_POST['competitors'] ?? ''),
        '_saved_at'      => current_time('mysql'),
        '_id'            => uniqid('q_', true),
    ];

    // 최신본 저장
    update_user_meta($uid,'bwf_questions',$data);
    update_user_meta($uid,'bwf_questions_saved_at',$data['_saved_at']);

    // 히스토리 누적
    $hist = get_user_meta($uid,'bwf_questions_history', true);
    if (!is_array($hist)) $hist = [];
    $hist[] = $data;
    update_user_meta($uid,'bwf_questions_history',$hist);
}

  // 피드백 링크
  $fb_page = get_page_by_path(BWF_FEEDBACK_PAGE_SLUG);
//   $feedback_url = add_query_arg(['rep'=>$uid], $fb_page ? get_permalink($fb_page) : home_url('/'.BWF_FEEDBACK_PAGE_SLUG.'/'));

  wp_enqueue_style('bwf-forms');
    wp_enqueue_script('bwf-js');

  ob_start(); ?>
  <div class="bwf-form">
    <?php echo $msg; ?>
    <div class="bwf-topcount">작성 <span id="bwf-answered">0</span>/<span id="bwf-total">0</span> 문항</div>
    <div id="bwf-progress"><div class="bwf-bar"></div></div>

    <h3>대표님 핵심 질문지</h3>
    <p class="bwf-hint">사업의 본질을 파악하고 고객에게 정말 묻고 싶은 질문을 구체화합니다. 각 문항은 <strong>최소 100자</strong>입니다.</p>

    <form method="post" class="bwf-grid">
      <?php wp_nonce_field('bwf_owner_form','bwf_nonce'); ?>

      <!-- 본질 질문 -->
      <!-- ① 현재 비즈니스에서 가장 큰 고민 -->
        <label>1. 현재 비즈니스에서 가장 큰 고민은 무엇인가요? <span class="bwf-required">*</span></label>
        <div class="bwf-help">
        <div>예시: “신규 고객 유입이 너무 어렵습니다. 광고 효율이 안 나옵니다.”, “재구매율이 낮아 성장이 정체돼요.”</div>
        </div>
        <textarea name="problem" required data-minlength="50"></textarea>

        <!-- ② 어떤 문제를 해결하나요 -->
        <label>2. 우리 서비스가 고객의 ‘어떤 문제’를 해결하나요? <span class="bwf-required">*</span></label>
        <div class="bwf-help">
        <div>설명: 고객에게 주는 가장 중요한 ‘이점/해결’을 한두 문장으로.</div>
        <div>예시: “바쁜 직장인에게 집밥처럼 건강한 한 끼 배달”, “복잡한 서류 작업을 5분 만에 끝내줌”.</div>
        </div>
        <textarea name="value" required data-minlength="50"></textarea>

        <!-- ③ 누가 이용해야 하나요/왜 선택하나요 -->
        <label>3. 이 서비스를 ‘누가’ 이용해야 하나요? 왜 우리를 선택하나요? <span class="bwf-required">*</span></label>
        <div class="bwf-help">
        <div>설명: 이상적 타겟 + 선택 이유를 구체적으로.</div>
        <div>예시: “운동은 하고 싶지만 시간/비용이 부족한 20대 대학생 — 15분 홈트+저렴한 구독료”.</div>
        </div>
        <textarea name="ideal_customer" required data-minlength="50"></textarea>

        <!-- ④-1 ~ ④-3 맞춤 질문 -->
        <label>4-1. 타겟 고객 1:1로 단 한 가지를 묻는다면? <span class="bwf-required">*</span></label>
        <div class="bwf-help">
        <div>예시: “우리 서비스의 어떤 점이 가장 문제 해결에 도움이 됐나요?”</div>
        </div>
        <textarea name="q1" data-group="ask3" required data-minlength="50"></textarea>

        <label>4-2. 두 번째 맞춤 질문 <span class="bwf-required">*</span></label>
        <div class="bwf-help"><div>예시: “우리 서비스를 알게 된 경로와 결심 포인트는 무엇이었나요?”</div></div>
        <textarea name="q2" data-group="ask3" required data-minlength="50"></textarea>

        <label>4-3. 세 번째 맞춤 질문 <span class="bwf-required">*</span></label>
        <div class="bwf-help"><div>예시: “사용 중 가장 불편했던 점은 무엇이었나요?”</div></div>
        <textarea name="q3" data-group="ask3" required data-minlength="50"></textarea>

        <!-- ⑤ 1:1 한 가지 -->
        <label>5. 타겟 고객 1:1로 단 한 가지를 묻는다면? <span class="bwf-required">*</span></label>
        <div class="bwf-help"><div>예시: “처음 사용 후 무엇이 가장 달라졌나요?”</div></div>
        <input type="text" name="one_question" placeholder="예: 우리의 어떤 점이 당신 문제를 가장 잘 해결했나요?">

        <!-- ⑥ 경쟁/차별 -->
        <label>6. 경쟁사와의 차별점은? <span class="bwf-required">*</span></label>
        <div class="bwf-help">
        <div>설명: 주요 경쟁사 1~2곳과 비교해 ‘가격/품질/속도/편의’ 등 차이를 적시.</div>
        <div>예시: “A는 싸지만 품질이 낮고, B는 비싸지만 퀄리티가 높다 → 우리는 적정 가격+높은 만족”.</div>
        </div>
        <textarea name="competitors" required data-minlength="50"></textarea>


      <div class="bwf-col-full bwf-actions">
        <button type="submit" class="bwf-btn" name="bwf_save_questions">저장</button>
      </div>
    </form>

   
  </div>
  <?php return ob_get_clean();
});
