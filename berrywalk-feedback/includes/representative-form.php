<?php
if (!defined('ABSPATH')) exit;

add_shortcode('bw_owner_form', function(){
  if (!is_user_logged_in()) return '<div class="bwf-form"><p>로그인 후 이용해주세요.</p></div>';
  $uid = get_current_user_id();
  $saved = get_user_meta($uid,'bwf_questions',true) ?: [];

  if (isset($_POST['bwf_save_questions'])) {
    $data = [
      'problem'        => sanitize_textarea_field($_POST['problem'] ?? ''),
      'value'          => sanitize_textarea_field($_POST['value'] ?? ''),
      'ideal_customer' => sanitize_textarea_field($_POST['ideal_customer'] ?? ''),
      'one_question'   => sanitize_text_field($_POST['one_question'] ?? ''),
      'competitors'    => sanitize_textarea_field($_POST['competitors'] ?? ''),
      'q1'             => sanitize_text_field($_POST['q1'] ?? ''),
      'q2'             => sanitize_text_field($_POST['q2'] ?? ''),
      'q3'             => sanitize_text_field($_POST['q3'] ?? ''),
    ];
    update_user_meta($uid,'bwf_questions',$data);
    $saved = $data;
    $ok = true;
  }

  $feedback_url = add_query_arg(['rep'=>$uid], get_permalink(get_page_by_path('customer-feedback')) ?: home_url('/customer-feedback/'));

  ob_start(); ?>
  <div class="bwf-form">
    <h3>대표님 핵심 질문지</h3>
    <p class="bwf-hint">사업의 본질을 파악하고 고객에게 정말 묻고 싶은 질문을 구체화하는 단계입니다.</p>
    <?php if(!empty($ok)) echo '<p style="color:#10b981">저장 완료. 피드백 링크가 생성되었습니다.</p>'; ?>

    <form method="post" class="bwf-grid">
      <div class="bwf-col-full">
        <label>현재 비즈니스에서 가장 큰 고민은 무엇인가요? <span class="bwf-required">*</span></label>
        <textarea name="problem" required data-minlength="100" placeholder="예: 신규 고객 유입이 너무 어렵습니다..."><?php echo esc_textarea($saved['problem'] ?? ''); ?></textarea>
        <p class="bwf-hint">예: 광고 효율 저하, 재구매율 정체, 차별점 불분명 등</p>
        <div class="bwf-counter"></div>
      </div>

      <div class="bwf-col-full">
        <label>우리 서비스가 고객의 ‘어떤 문제’를 해결하나요? <span class="bwf-required">*</span></label>
        <textarea name="value" required data-minlength="100" placeholder="예: 바쁜 직장인에게 집밥처럼 건강한 한 끼를 배달..."><?php echo esc_textarea($saved['value'] ?? ''); ?></textarea>
        <div class="bwf-counter"></div>
      </div>

      <div class="bwf-col-full">
        <label>이 서비스를 ‘누가’ 이용해야 하나요? 왜 우리를 선택하나요? <span class="bwf-required">*</span></label>
        <textarea name="ideal_customer" required data-minlength="100" placeholder="예: 운동은 하고 싶지만 시간/비용이 부족한 20대 대학생..."><?php echo esc_textarea($saved['ideal_customer'] ?? ''); ?></textarea>
        <div class="bwf-counter"></div>
      </div>

      <div class="bwf-col-full">
        <label>타겟 고객 1:1로 단 한 가지를 묻는다면? <span class="bwf-required">*</span></label>
        <input type="text" name="one_question" required value="<?php echo esc_attr($saved['one_question'] ?? ''); ?>" placeholder="예: 우리 서비스의 어떤 점이 가장 도움이 되었나요?">
      </div>

      <div class="bwf-col-full">
        <label>경쟁사와의 차별점은? <span class="bwf-required">*</span></label>
        <textarea name="competitors" required data-minlength="100" placeholder="예: A사는 저렴하지만 품질 낮음, B사는 고품질이나 비쌈..."><?php echo esc_textarea($saved['competitors'] ?? ''); ?></textarea>
        <div class="bwf-counter"></div>
      </div>

      <div class="bwf-col-full"><hr></div>
      <div class="bwf-col-full"><strong>대표님 맞춤 질문 3가지</strong></div>

      <div class="bwf-col-3">
        <input type="text" name="q1" placeholder="맞춤 질문 1" value="<?php echo esc_attr($saved['q1'] ?? ''); ?>" required>
      </div>
      <div class="bwf-col-3">
        <input type="text" name="q2" placeholder="맞춤 질문 2" value="<?php echo esc_attr($saved['q2'] ?? ''); ?>" required>
      </div>
      <div class="bwf-col-3">
        <input type="text" name="q3" placeholder="맞춤 질문 3" value="<?php echo esc_attr($saved['q3'] ?? ''); ?>" required>
      </div>

      <div class="bwf-col-full bwf-actions">
        <button type="submit" name="bwf_save_questions">저장</button>
      </div>
    </form>

    <div style="margin-top:12px;">
      <strong>피드백 링크</strong><br>
      <input type="text" value="<?php echo esc_attr($feedback_url); ?>" readonly onclick="this.select();" />
      <p class="bwf-hint">이 링크를 고객에게 공유하세요.</p>
    </div>
  </div>
  <?php return ob_get_clean();
});
