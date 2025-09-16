<?php
if (!defined('ABSPATH')) exit;

/** 대표 회원가입 */
add_shortcode('bwf_signup_representative', function(){
  if (is_user_logged_in()) {
    $u = wp_get_current_user();
    return '<div class="bwf-form"><p><strong>'.bwf_esc($u->user_email).'</strong>로 로그인됨.</p>
            <p><a href="'.esc_url(home_url('/owner-questions/')).'">질문 등록하기</a></p></div>';
  }
  $nonce = wp_create_nonce('bwf_signup_rep');
  ob_start(); ?>
  <form method="post" class="bwf-form bwf-grid" novalidate>
    <input type="hidden" name="bwf_role" value="representative">
    <input type="hidden" name="bwf_nonce" value="<?php echo esc_attr($nonce); ?>">

    <div class="bwf-col-2">
      <label><?php echo '회사명'; ?> <span class="bwf-required">*</span></label>
      <input type="text" name="company_name" required>
    </div>
    <div>
      <label>업종 <span class="bwf-required">*</span></label>
      <select name="industry" required>
        <option value="">선택</option>
        <?php foreach(bwf_industry_options() as $k=>$v) echo '<option value="'.esc_attr($k).'">'.esc_html($v).'</option>'; ?>
      </select>
    </div>
    <div>
      <label>직원 수 <span class="bwf-required">*</span></label>
      <input type="number" name="employees" min="1" required>
    </div>
    <div class="bwf-col-2">
      <label>홈페이지 URL</label>
      <input type="url" name="site_url" placeholder="https://">
    </div>

    <div>
      <label>알게 된 경로 <span class="bwf-required">*</span></label>
      <select name="source" id="bwf-source" required>
        <option value="">선택</option>
        <?php foreach(bwf_source_options() as $k=>$v) echo '<option value="'.esc_attr($k).'">'.esc_html($v).'</option>'; ?>
      </select>
      <input type="text" name="source_etc" id="bwf-source-etc" placeholder="기타 경로 입력" style="display:none;">
    </div>
    <div>
      <label>휴대폰 번호(숫자만) <span class="bwf-required">*</span></label>
      <input type="tel" name="phone" pattern="[0-9]{9,12}" required>
    </div>
    <div class="bwf-col-2">
      <label>연락 가능한 시간대 <span class="bwf-required">*</span></label>
      <input type="text" name="contact_time" placeholder="예: 평일 13:00~18:00" required>
    </div>

    <div class="bwf-col-full"><hr></div>
    <div class="bwf-col-full"><strong>소셜 링크(있으면 입력)</strong></div>
    <?php foreach(bwf_social_fields() as $key=>$label): ?>
      <div class="bwf-col-2">
        <label><?php echo bwf_esc($label); ?></label>
        <input type="url" name="<?php echo esc_attr($key); ?>" placeholder="https://">
      </div>
    <?php endforeach; ?>

    <div class="bwf-col-2">
      <label>이메일 <span class="bwf-required">*</span></label>
      <input type="email" name="user_email" required>
    </div>
    <div class="bwf-col-2">
      <label>비밀번호 <span class="bwf-required">*</span></label>
      <input type="password" name="user_pass" required>
    </div>

    <div class="bwf-col-full bwf-actions">
      <button type="submit" name="bwf_register">회원가입</button>
      <p class="bwf-hint">* 표시는 필수 입력</p>
    </div>
  </form>
  <?php return ob_get_clean();
});

/** 피드백자 회원가입 */
add_shortcode('bwf_signup_feedback', function(){
  if (is_user_logged_in()) {
    $u = wp_get_current_user();
    return '<div class="bwf-form"><p><strong>'.bwf_esc($u->user_email).'</strong>로 로그인됨.</p></div>';
  }
  $nonce = wp_create_nonce('bwf_signup_fb');
  ob_start(); ?>
  <form method="post" class="bwf-form bwf-grid" novalidate>
    <input type="hidden" name="bwf_role" value="feedback_provider">
    <input type="hidden" name="bwf_nonce" value="<?php echo esc_attr($nonce); ?>">

    <div>
      <label>연령대 <span class="bwf-required">*</span></label>
      <input type="text" name="age_range" placeholder="예: 20대 초반" required>
    </div>
    <div>
      <label>성별 <span class="bwf-required">*</span></label>
      <select name="gender" required>
        <option value="">선택</option><option value="male">남성</option><option value="female">여성</option><option value="etc">기타/응답안함</option>
      </select>
    </div>
    <div>
      <label>카테고리 구매 경험 <span class="bwf-required">*</span></label>
      <select name="experience" required>
        <option value="">선택</option><option value="yes">예</option><option value="no">아니오</option>
      </select>
    </div>
    <div>
      <label>알게 된 경로 <span class="bwf-required">*</span></label>
      <select name="source" id="bwf-source-fb" required>
        <option value="">선택</option>
        <?php foreach(bwf_source_options() as $k=>$v) echo '<option value="'.esc_attr($k).'">'.esc_html($v).'</option>'; ?>
      </select>
      <input type="text" name="source_etc" id="bwf-source-etc-fb" placeholder="기타 경로 입력" style="display:none;">
    </div>
    <div class="bwf-col-2">
      <label>이메일 <span class="bwf-required">*</span></label>
      <input type="email" name="user_email" required>
    </div>
    <div class="bwf-col-2">
      <label>비밀번호 <span class="bwf-required">*</span></label>
      <input type="password" name="user_pass" required>
    </div>

    <div class="bwf-col-full bwf-actions">
      <button type="submit" name="bwf_register">회원가입</button>
      <p class="bwf-hint">* 표시는 필수 입력</p>
    </div>
  </form>
  <?php return ob_get_clean();
});

/** 회원가입 처리 */
add_action('init', function(){
  if (!isset($_POST['bwf_register'])) return;

  $role = sanitize_text_field($_POST['bwf_role'] ?? '');
  $nonce = sanitize_text_field($_POST['bwf_nonce'] ?? '');
  if ($role === 'representative' && !wp_verify_nonce($nonce, 'bwf_signup_rep')) return;
  if ($role === 'feedback_provider' && !wp_verify_nonce($nonce, 'bwf_signup_fb')) return;

  $email = sanitize_email($_POST['user_email'] ?? '');
  $pass  = $_POST['user_pass'] ?? '';
  if (!$email || !$pass || !$role) return;

  if ($role === 'representative') {
    foreach (['company_name','industry','employees','phone','contact_time','source'] as $req) {
      if (empty($_POST[$req])) wp_die('필수 항목 누락: '.$req);
    }
  } else {
    foreach (['age_range','gender','experience','source'] as $req) {
      if (empty($_POST[$req])) wp_die('필수 항목 누락: '.$req);
    }
  }

  $user_id = wp_create_user($email, $pass, $email);
  if (is_wp_error($user_id)) wp_die($user_id->get_error_message());
  wp_update_user(['ID'=>$user_id,'role'=>$role]);

  // 메타 저장 + 기타 경로 처리
  foreach($_POST as $k=>$v){
    if(in_array($k,['bwf_register','user_email','user_pass','bwf_role','bwf_nonce'])) continue;
    if ($k === 'source' && $v === 'etc' && !empty($_POST['source_etc'])) {
      $v = 'etc: '.sanitize_text_field($_POST['source_etc']);
    }
    update_user_meta($user_id,$k,sanitize_text_field($v));
  }

  wp_set_current_user($user_id);
  wp_set_auth_cookie($user_id);
  $redirect = ($role === 'representative') ? home_url('/owner-questions/') : home_url('/');
  wp_redirect($redirect); exit;
});
