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
      // 본질 3문항
      'problem'        => sanitize_textarea_field($_POST['problem'] ?? ''),
      'value'          => sanitize_textarea_field($_POST['value'] ?? ''),
      'ideal_customer' => sanitize_textarea_field($_POST['ideal_customer'] ?? ''),
      // 맞춤 3문항
      'q1' => sanitize_textarea_field($_POST['q1'] ?? ''),
        'q2' => sanitize_textarea_field($_POST['q2'] ?? ''),
        'q3' => sanitize_textarea_field($_POST['q3'] ?? ''),
      // 타겟 1:1 한 가지
      'one_question'   => sanitize_text_field($_POST['one_question'] ?? ''),
      // 경쟁사
      'competitors'    => sanitize_textarea_field($_POST['competitors'] ?? ''),
    ];
    update_user_meta($uid,'bwf_questions',$data);
    $saved = $data;
    $msg = '<p style="color:#10b981">저장 완료. 아래 링크로 피드백을 받을 수 있습니다.</p>';
  }

  // 피드백 링크
  $fb_page = get_page_by_path(BWF_FEEDBACK_PAGE_SLUG);
  $feedback_url = add_query_arg(['rep'=>$uid], $fb_page ? get_permalink($fb_page) : home_url('/'.BWF_FEEDBACK_PAGE_SLUG.'/'));

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
      <div class="bwf-col-full">
        <label>1.현재 비즈니스에서 가장 큰 고민은 무엇인가요? <span class="bwf-required">*</span></label>
        <textarea name="problem" required data-minlength="50" placeholder="예: 신규 고객 유입이 너무 어렵습니다..."><?php echo esc_textarea($S('problem')); ?></textarea>
      </div>
      <div class="bwf-col-full">
        <label>2.우리 서비스가 고객의 ‘어떤 문제’를 해결하나요? <span class="bwf-required">*</span></label>
        <textarea name="value" required data-minlength="50" placeholder="예: 바쁜 직장인에게 집밥처럼 건강한 한 끼를 배달..."><?php echo esc_textarea($S('value')); ?></textarea>
      </div>
      <div class="bwf-col-full">
        <label>3.이 서비스를 ‘누가’ 이용해야 하나요? 왜 우리를 선택하나요? <span class="bwf-required">*</span></label>
        <textarea name="ideal_customer" required data-minlength="50" placeholder="예: 운동은 하고 싶지만 시간/비용이 부족한 20대 대학생..."><?php echo esc_textarea($S('ideal_customer')); ?></textarea>
      </div>

      <div class="bwf-col-full"><hr></div>

      <!-- 고객에게 물어보고 싶은 3가지 (먼저) -->
      <div class="bwf-col-full"><strong>4.고객에게 물어보고 싶은 3가지</strong></div>
        <div class="bwf-grid-2 bwf-col-full">
        <div>
            <label>질문 1</label>
            <textarea name="q1" placeholder="예: 우리 서비스를 알게 된 경로는 무엇인가요?" required><?php echo esc_textarea($S('q1')); ?></textarea>
        </div>
        <div>
            <label>질문 2</label>
            <textarea name="q2" placeholder="예: 우리 서비스를 선택한 가장 큰 이유는 무엇이었나요?" required><?php echo esc_textarea($S('q2')); ?></textarea>
        </div>
        </div>
        <div class="bwf-col-full">
        <label>질문 3</label>
        <textarea name="q3" placeholder="예: 구매를 망설이게 하는 가장 큰 요인은 무엇인가요?" required><?php echo esc_textarea($S('q3')); ?></textarea>
        </div>

      <!-- 타겟 1:1 한 가지 -->
      <div class="bwf-col-full"><hr></div>
      <div class="bwf-col-full">
        <label>5.타겟 고객 1:1로 단 한 가지를 묻는다면? <span class="bwf-required">*</span></label>
        <input type="text" name="one_question" value="<?php echo esc_attr($S('one_question')); ?>" placeholder="예: 우리 서비스의 어떤 점이 가장 도움이 되었나요?" required>
      </div>

      <!-- 경쟁사 -->
      <div class="bwf-col-full">
        <label>6.경쟁사와의 차별점은? <span class="bwf-required">*</span></label>
        <textarea name="competitors" required data-minlength="50" placeholder="예: A사는 저렴하지만 품질 낮음, B사는 고품질이나 비쌈..."><?php echo esc_textarea($S('competitors')); ?></textarea>
      </div>

      <div class="bwf-col-full bwf-actions">
        <button type="submit" name="bwf_save_questions">저장</button>
      </div>
    </form>

    <div style="margin-top:12px;">
      <strong>피드백 링크</strong>
      <input type="text" value="<?php echo esc_attr($feedback_url); ?>" readonly onclick="this.select();">
      <p class="bwf-hint">이 링크를 고객에게 공유하세요.</p>
    </div>
  </div>
  <?php return ob_get_clean();
});
