<?php
if (!defined('ABSPATH')) exit;

// ❌ 잘못된 helpers.php → ✅ helper.php
require_once __DIR__ . '/helper.php';

// 대표 회원가입
add_shortcode('bwf_signup_representative', function () {
  // 필요한 CSS/JS 로드
  wp_enqueue_style('bwf-forms');


  if (is_user_logged_in()) {
    $u = wp_get_current_user();
    return '<div class="bwf-form"><p><strong>'.bwf_esc($u->user_login).'</strong>로 로그인됨.</p>
            <p><a href="'.esc_url(home_url('/owner-questions/')).'">질문 등록하기</a></p></div>';
  }

  $nonce = wp_create_nonce('bwf_signup_rep');
  $S = function($k,$d=''){ return isset($_POST[$k]) ? esc_attr($_POST[$k]) : $d; };

  ob_start(); ?>
  <form method="post" class="bwf-form bwf-grid" novalidate>
    <input type="hidden" name="bwf_role" value="representative">
    <input type="hidden" name="bwf_nonce" value="<?php echo esc_attr($nonce); ?>">

    <!-- 로그인 정보: 2열 정렬 / 아이디를 가장 앞에 -->
    <div>
      <label>아이디(영문/숫자) <span class="bwf-required">*</span></label>
      <input type="text" name="user_login" value="<?php echo $S('user_login'); ?>" pattern="[A-Za-z0-9_\.\-]{4,32}" required>
    </div>
    <div>
      <label>비밀번호 <span class="bwf-required">*</span></label>
      <input type="password" name="user_pass" required>
    </div>
    <div>
      <label>이메일 <span class="bwf-required">*</span></label>
      <input type="email" name="user_email" value="<?php echo $S('user_email'); ?>" required>
    </div>
    <div>
      <label>이름 <span class="bwf-required">*</span></label>
      <input type="text" name="first_name" value="<?php echo $S('first_name'); ?>" required>
    </div>

    <!-- 회사 기본 -->
    <div>
      <label>회사명 <span class="bwf-required">*</span></label>
      <input type="text" name="bw_company_name" value="<?php echo $S('bw_company_name'); ?>" required>
    </div>
    <div>
      <label>업종 <span class="bwf-required">*</span></label>
      <select name="bw_industry" required>
        <option value="">선택</option>
        <?php foreach(bwf_industry_options() as $k=>$v){
          $sel = selected($S('bw_industry'), $k, false);
          echo "<option value='".esc_attr($k)."' $sel>".esc_html($v)."</option>";
        }?>
      </select>
    </div>
    <div>
      <label>직원 수 <span class="bwf-required">*</span></label>
      <input type="number" name="bw_employees" value="<?php echo $S('bw_employees'); ?>" min="1" required>
    </div>
    <div>
      <label>홈페이지 URL</label>
      <input type="url" name="user_url" value="<?php echo $S('user_url'); ?>" placeholder="https://">
    </div>

    <!-- 연락/유입 -->
    <div>
      <label>알게 된 경로 <span class="bwf-required">*</span></label>
      <select name="bw_discover" id="bwf-source" required>
        <option value="">선택</option>
        <?php foreach(bwf_source_options() as $k=>$v){
          $sel = selected($S('bw_discover'), $k, false);
          echo "<option value='".esc_attr($k)."' $sel>".esc_html($v)."</option>";
        }?>
      </select>
      <input type="text" name="source_etc" id="bwf-source-etc" placeholder="기타 경로" style="display:none;" value="<?php echo $S('source_etc'); ?>">
    </div>
    <div>
      <label>휴대폰 번호 <span class="bwf-required">*</span></label>
        <input type="tel" name="bw_phone" id="bwf-phone"
        inputmode="numeric" autocomplete="tel"
        placeholder="010-1234-5678"
        maxlength="13" pattern="^010-\d{4}-\d{4}$" required>
    </div>
    <div>
      <label>연락 가능한 시간대 <span class="bwf-required">*</span></label>
      <input type="text" name="bw_contact_window" value="<?php echo $S('bw_contact_window'); ?>" placeholder="예: 평일 13:00~18:00" required>
    </div>

    <div class="bwf-col-full"><hr></div>

    <!-- 소셜 링크 -->
    <div class="bwf-col-full"><strong>소셜 링크(있으면 입력)</strong></div>
    <?php foreach(bwf_social_fields() as $key=>$label): ?>
      <div>
        <label><?php echo bwf_esc($label); ?></label>
        <input type="url" name="<?php echo esc_attr($key); ?>" value="<?php echo $S($key); ?>" placeholder="https://">
      </div>
    <?php endforeach; ?>

    <div class="bwf-col-full bwf-actions">
      <?php if(!empty($_POST['bwf_error'])): ?>
        <div class="bwf-error-text"><?php echo bwf_esc($_POST['bwf_error']); ?></div>
      <?php endif; ?>
      <button type="submit" name="bwf_register">회원가입</button>
      <p class="bwf-hint">* 표시는 필수 입력</p>
    </div>
  </form>
  <?php return ob_get_clean();
});

/** 피드백자 회원가입(간단) */
add_shortcode('bwf_signup_feedback', function () {
  if (is_user_logged_in()) {
    $u = wp_get_current_user();
    return '<div class="bwf-form"><p><strong>' . bwf_esc($u->user_login) . '</strong>로 로그인됨.</p></div>';
  }
  $nonce = wp_create_nonce('bwf_signup_fb');
  $S = function($k,$d=''){ return isset($_POST[$k]) ? esc_attr($_POST[$k]) : $d; };

  ob_start(); ?>
  <form method="post" class="bwf-form bwf-grid" novalidate>
    <input type="hidden" name="bwf_role" value="feedback_provider">
    <input type="hidden" name="bwf_nonce" value="<?php echo esc_attr($nonce); ?>">

    <div>
      <label>이메일 <span class="bwf-required">*</span></label>
      <input type="email" name="user_email" value="<?php echo $S('user_email'); ?>" required>
    </div>
    <div>
      <label>비밀번호 <span class="bwf-required">*</span></label>
      <input type="password" name="user_pass" required>
    </div>
    <div>
      <label>아이디 <span class="bwf-required">*</span></label>
      <input type="text" name="user_login" value="<?php echo $S('user_login'); ?>" pattern="[A-Za-z0-9_\.\-]{4,32}" required>
    </div>
    <div>
      <label>이름 <span class="bwf-required">*</span></label>
      <input type="text" name="first_name" value="<?php echo $S('first_name'); ?>" required>
    </div>

    <div>
      <label>연령대 <span class="bwf-required">*</span></label>
      <input type="text" name="age_range" value="<?php echo $S('age_range'); ?>" placeholder="예: 20대 초반" required>
    </div>
    <div>
      <label>성별 <span class="bwf-required">*</span></label>
      <select name="gender" required>
        <option value="">선택</option>
        <option value="male"   <?php selected($S('gender'),'male'); ?>>남성</option>
        <option value="female" <?php selected($S('gender'),'female'); ?>>여성</option>
        <option value="etc"    <?php selected($S('gender'),'etc'); ?>>기타/응답안함</option>
      </select>
    </div>
    <div>
      <label>카테고리 구매 경험 <span class="bwf-required">*</span></label>
      <select name="experience" required>
        <option value="">선택</option>
        <option value="yes" <?php selected($S('experience'),'yes'); ?>>예</option>
        <option value="no"  <?php selected($S('experience'),'no'); ?>>아니오</option>
      </select>
    </div>
    <div>
      <label>알게 된 경로 <span class="bwf-required">*</span></label>
      <select name="source" id="bwf-source-fb" required>
        <option value="">선택</option>
        <?php foreach(bwf_source_options() as $k=>$v){
          $sel = selected($S('source'), $k, false);
          echo "<option value='".esc_attr($k)."' $sel>".esc_html($v)."</option>";
        }?>
      </select>
      <input type="text" name="source_etc" id="bwf-source-etc-fb" placeholder="기타 경로" style="display:none;" value="<?php echo $S('source_etc'); ?>">
    </div>

    <div class="bwf-col-full bwf-actions">
      <?php if(!empty($_POST['bwf_error'])): ?>
        <div class="bwf-error-text"><?php echo bwf_esc($_POST['bwf_error']); ?></div>
      <?php endif; ?>
      <button type="submit" name="bwf_register">회원가입</button>
      <p class="bwf-hint">* 표시는 필수 입력</p>
    </div>
  </form>
  <?php return ob_get_clean();
});

/** 폼 처리 */
add_action('init', function(){
  if (!isset($_POST['bwf_register'])) return;

  $role  = sanitize_text_field($_POST['bwf_role'] ?? '');
  $nonce = sanitize_text_field($_POST['bwf_nonce'] ?? '');
  if ($role === 'representative' && !wp_verify_nonce($nonce,'bwf_signup_rep')) return;
  if ($role === 'feedback_provider' && !wp_verify_nonce($nonce,'bwf_signup_fb')) return;

  // 공통
  $user_login = sanitize_user($_POST['user_login'] ?? '');
  $user_email = sanitize_email($_POST['user_email'] ?? '');
  $user_pass  = $_POST['user_pass'] ?? '';
  $first_name = sanitize_text_field($_POST['first_name'] ?? '');

  // 서버 검증
  $err = '';
  if (!$user_login || !$user_email || !$user_pass || !$first_name) $err = '필수 항목이 누락되었습니다.';
  if (!$err && username_exists($user_login)) $err = '이미 사용 중인 아이디입니다.';
  if (!$err && email_exists($user_email))    $err = '이미 등록된 이메일입니다.';

  if ($role === 'representative') {
    foreach (['bw_company_name','bw_industry','bw_employees','bw_contact_window','bw_discover','bw_phone'] as $k) {
      if (empty($_POST[$k])) { $err = '필수 항목이 누락되었습니다.'; break; }
    }
  } else {
    foreach (['age_range','gender','experience','source'] as $k) {
      if (empty($_POST[$k])) { $err = '필수 항목이 누락되었습니다.'; break; }
    }
  }
  if ($role === 'representative') {
 $raw = isset($_POST['bw_phone']) ? $_POST['bw_phone'] : '';
$digits = preg_replace('/\D+/','',$raw);
if (strlen($digits)!==11 || strpos($digits,'010')!==0) {
  $_POST['bwf_error'] = '휴대폰 번호는 010-1234-5678 형식으로 입력해주세요.'; return;
}
$_POST['bw_phone'] = preg_replace('/^(\d{3})(\d{4})(\d{4})$/','$1-$2-$3',$digits);
}

  if ($err) {
    $_POST['bwf_error'] = $err; // sticky 에러 표시
    return;
  }

  // 계정 생성
  $uid = wp_insert_user([
    'user_login' => $user_login,
    'user_email' => $user_email,
    'user_pass'  => $user_pass,
    'first_name' => $first_name,
    'role'       => $role,
    'user_url'   => esc_url_raw($_POST['user_url'] ?? '')
  ]);
  if (is_wp_error($uid)) { $_POST['bwf_error'] = $uid->get_error_message(); return; }

  // 메타 저장 (WP-Members 필드 네이밍 반영)
  $save = function($key){
    if(isset($_POST[$key])) update_user_meta(get_current_user_id() ?: 0, $key, sanitize_text_field($_POST[$key]));
  };

  // 로그인 세션
  wp_set_current_user($uid);
  wp_set_auth_cookie($uid);

  // 대표 메타
  if ($role === 'representative') {
    $uid = get_current_user_id();
    $meta_keys = ['bw_company_name','bw_industry','bw_employees','bw_contact_window','bw_discover','bw_phone'];
    foreach($meta_keys as $k) $save($k);

    // discover 기타 처리
    if (isset($_POST['bw_discover']) && $_POST['bw_discover']=='etc' && !empty($_POST['source_etc'])) {
      update_user_meta($uid,'bw_discover','etc: '.sanitize_text_field($_POST['source_etc']));
    }

    // 소셜
    foreach (bwf_social_fields() as $k=>$label) if(isset($_POST[$k])) update_user_meta($uid,$k,esc_url_raw($_POST[$k]));
  } else {
    foreach (['age_range','gender','experience','source'] as $k) $save($k);
    if (isset($_POST['source']) && $_POST['source']=='etc' && !empty($_POST['source_etc'])) {
      update_user_meta($uid,'source','etc: '.sanitize_text_field($_POST['source_etc']));
    }
  }

  // 성공 리디렉트: 대표는 /info-feedback 으로 이동
  $role  = sanitize_text_field($_POST['bwf_role'] ?? '');
  $dest = ($role==='representative') ? home_url('/info-feedback/?join=ok') : home_url('/?join=ok');
  wp_redirect($dest); exit;
});
