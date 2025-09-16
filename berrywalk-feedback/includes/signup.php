<?php
if (!defined('ABSPATH')) exit;

/** 공통 옵션: 업종/유입경로 */
function bwf_industry_options() {
  return [
    'food' => '외식/식품',
    'beauty' => '뷰티/헬스',
    'education' => '교육/강의',
    'it_saas' => 'IT / SaaS',
    'commerce' => '커머스/쇼핑몰',
    'local' => '지역/로컬서비스',
    'consulting' => '컨설팅/대행',
    'etc' => '기타',
  ];
}
function bwf_source_options() {
  return [
    'instagram' => '인스타그램',
    'youtube'   => '유튜브',
    'search'    => '검색광고/네이버/구글',
    'referral'  => '지인 추천',
    'blog'      => '블로그/커뮤니티',
    'event'     => '세미나/이벤트',
    'etc'       => '기타(직접 입력)',
  ];
}

/** 대표 회원가입 */
add_shortcode('bwf_signup_representative', function(){
  if (is_user_logged_in()) {
    $u = wp_get_current_user();
    return '<div class="bwf-form"><p><strong>'
      . esc_html($u->user_email)
      . '</strong> 계정으로 이미 로그인되어 있습니다.</p>'
      . '<p>대표 질문 등록 페이지로 이동하세요: <a href="' . esc_url(home_url('/owner-questions/')) . '">바로가기</a></p></div>';
  }

  // CSRF 토큰
  $nonce = wp_create_nonce('bwf_signup_rep');

  ob_start(); ?>
  <form method="post" class="bwf-form" novalidate>
    <input type="hidden" name="bwf_role" value="representative">
    <input type="hidden" name="bwf_nonce" value="<?php echo esc_attr($nonce); ?>">

    <input type="text" name="company_name" placeholder="회사명*" required>
    <select name="industry" required>
      <option value="">업종 선택*</option>
      <?php foreach(bwf_industry_options() as $k=>$v): ?>
        <option value="<?php echo esc_attr($k); ?>"><?php echo esc_html($v); ?></option>
      <?php endforeach; ?>
    </select>

    <input type="number" name="employees" placeholder="직원 수*" min="1" required>
    <input type="url" name="site_url" placeholder="홈페이지 URL">

    <select name="source" id="bwf-source" required>
      <option value="">알게 된 경로 선택*</option>
      <?php foreach(bwf_source_options() as $k=>$v): ?>
        <option value="<?php echo esc_attr($k); ?>"><?php echo esc_html($v); ?></option>
      <?php endforeach; ?>
    </select>
    <input type="text" name="source_etc" id="bwf-source-etc" placeholder="기타 경로 직접 입력" style="display:none;">

    <input type="tel" name="phone" placeholder="휴대폰 번호(숫자만)*" required pattern="[0-9]{9,12}">
    <input type="text" name="contact_time" placeholder="연락 가능한 시간대(예: 평일 13:00~18:00)*" required>

    <input type="email" name="user_email" placeholder="이메일*" required>
    <input type="password" name="user_pass" placeholder="비밀번호*" required>
    <button type="submit" name="bwf_register">회원가입</button>
  </form>
  <?php return ob_get_clean();
});

/** 피드백자 회원가입 (기존 유지, 필요 시 같은 패턴으로 강화) */
add_shortcode('bwf_signup_feedback', function(){
  if (is_user_logged_in()) {
    $u = wp_get_current_user();
    return '<div class="bwf-form"><p><strong>'
      . esc_html($u->user_email)
      . '</strong> 계정으로 이미 로그인되어 있습니다.</p></div>';
  }
  $nonce = wp_create_nonce('bwf_signup_fb');

  ob_start(); ?>
  <form method="post" class="bwf-form" novalidate>
    <input type="hidden" name="bwf_role" value="feedback_provider">
    <input type="hidden" name="bwf_nonce" value="<?php echo esc_attr($nonce); ?>">

    <input type="text" name="age_range" placeholder="연령대(예: 20대 초반)*" required>
    <select name="gender" required>
      <option value="">성별*</option>
      <option value="male">남성</option>
      <option value="female">여성</option>
      <option value="etc">기타/응답안함</option>
    </select>
    <select name="experience" required>
      <option value="">해당 카테고리 구매 경험*</option>
      <option value="yes">예</option>
      <option value="no">아니오</option>
    </select>
    <select name="source" id="bwf-source-fb" required>
      <option value="">알게 된 경로*</option>
      <?php foreach(bwf_source_options() as $k=>$v): ?>
        <option value="<?php echo esc_attr($k); ?>"><?php echo esc_html($v); ?></option>
      <?php endforeach; ?>
    </select>
    <input type="text" name="source_etc" id="bwf-source-etc-fb" placeholder="기타 경로 직접 입력" style="display:none;">

    <input type="email" name="user_email" placeholder="이메일*" required>
    <input type="password" name="user_pass" placeholder="비밀번호*" required>
    <button type="submit" name="bwf_register">회원가입</button>
  </form>
  <?php return ob_get_clean();
});

/** 폼 처리 */
add_action('init', function(){
  if (!isset($_POST['bwf_register'])) return;

  $role = sanitize_text_field($_POST['bwf_role'] ?? '');
  $nonce = $_POST['bwf_nonce'] ?? '';
  if ($role === 'representative' && !wp_verify_nonce($nonce, 'bwf_signup_rep')) return;
  if ($role === 'feedback_provider' && !wp_verify_nonce($nonce, 'bwf_signup_fb')) return;

  $email = sanitize_email($_POST['user_email'] ?? '');
  $pass  = $_POST['user_pass'] ?? '';
  if (!$email || !$pass || !$role) return;

  // 필수값 검증(대표)
  if ($role === 'representative') {
    foreach (['company_name','industry','employees','phone','contact_time','source'] as $req) {
      if (empty($_POST[$req])) wp_die('필수 항목 누락: '.$req);
    }
  }

  $user_id = wp_create_user($email, $pass, $email);
  if (is_wp_error($user_id)) wp_die($user_id->get_error_message());

  wp_update_user(['ID'=>$user_id,'role'=>$role]);

  // 메타 저장
  foreach($_POST as $k=>$v){
    if(in_array($k,['bwf_register','user_email','user_pass','bwf_role','bwf_nonce'])) continue;
    if ($k === 'source' && $v === 'etc' && !empty($_POST['source_etc'])) {
      $v = 'etc: '.sanitize_text_field($_POST['source_etc']);
    }
    update_user_meta($user_id,$k,sanitize_text_field($v));
  }

  // 로그인 시키고 리디렉트(대표는 질문 폼으로)
  wp_set_current_user($user_id);
  wp_set_auth_cookie($user_id);

  $redirect = ($role === 'representative') ? home_url('/owner-questions/') : home_url('/');
  wp_redirect($redirect); exit;
});
