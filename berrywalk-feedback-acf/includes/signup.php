<?php
if (!defined('ABSPATH')) exit;

/* 역할 보장 */
add_action('init', function(){
  if (!get_role('bw_owner')) add_role('bw_owner','대표',['read'=>true]);
});

/* --- 관리자 사용자 목록 컬럼 + 프로필 편집 영역 --- */
add_filter('manage_users_columns', function($cols){
  $cols['bw_owner_meta'] = '대표 인적사항';
  return $cols;
});
add_filter('manage_users_custom_column', function($out, $col, $user_id){
  if ($col !== 'bw_owner_meta') return $out;
  $u = get_userdata($user_id);
  $pairs = [
    '회사'     => get_user_meta($user_id,'bw_company_name',true),
    '업종'     => get_user_meta($user_id,'bw_industry',true),
    '직원 수'  => get_user_meta($user_id,'bw_employees',true),
    '휴대폰'   => get_user_meta($user_id,'bw_phone',true),
    '연락시간' => get_user_meta($user_id,'bw_contact_window',true),
    '유입경로' => get_user_meta($user_id,'bw_discover',true),
    '웹사이트' => $u ? $u->user_url : '',
  ];
  $html  = '<div style="line-height:1.5">';
  foreach($pairs as $k=>$v){
    $val = ($v === '' ? '—' : $v);
    $html .= '<div><strong>'.$k.'</strong> '.$val.'</div>';
  }
  $html .= '</div>';
  return $html;
}, 10, 3);

/* 프로필 편집 폼 */
add_action('show_user_profile','bwf_owner_profile_fields');
add_action('edit_user_profile','bwf_owner_profile_fields');
function bwf_owner_profile_fields($user){
  ?>
  <h2>대표 인적사항</h2>
  <table class="form-table" role="presentation">
    <tr><th><label>회사명</label></th><td><input type="text" name="bw_company_name" value="<?php echo esc_attr(get_user_meta($user->ID,'bw_company_name',true)); ?>" class="regular-text"></td></tr>
    <tr><th><label>업종</label></th><td><input type="text" name="bw_industry" value="<?php echo esc_attr(get_user_meta($user->ID,'bw_industry',true)); ?>" class="regular-text"></td></tr>
    <tr><th><label>직원 수</label></th><td><input type="number" name="bw_employees" value="<?php echo esc_attr(get_user_meta($user->ID,'bw_employees',true)); ?>" class="regular-text"></td></tr>
    <tr><th><label>휴대폰</label></th><td><input type="text" name="bw_phone" value="<?php echo esc_attr(get_user_meta($user->ID,'bw_phone',true)); ?>" class="regular-text"></td></tr>
    <tr><th><label>연락 가능한 시간대</label></th><td><input type="text" name="bw_contact_window" value="<?php echo esc_attr(get_user_meta($user->ID,'bw_contact_window',true)); ?>" class="regular-text"></td></tr>
    <tr><th><label>알게 된 경로</label></th><td><input type="text" name="bw_discover" value="<?php echo esc_attr(get_user_meta($user->ID,'bw_discover',true)); ?>" class="regular-text"></td></tr>
    <tr><th><label>웹사이트 URL</label></th><td><input type="url" name="user_url_bwf" value="<?php echo esc_attr(get_userdata($user->ID)->user_url); ?>" class="regular-text" placeholder="https://"></td></tr>
  </table>
  <?php
}
add_action('personal_options_update','bwf_owner_profile_save');
add_action('edit_user_profile_update','bwf_owner_profile_save');
function bwf_owner_profile_save($user_id){
  foreach(['bw_company_name','bw_industry','bw_employees','bw_phone','bw_contact_window','bw_discover'] as $k){
    if(isset($_POST[$k])) update_user_meta($user_id,$k, sanitize_text_field($_POST[$k]));
  }
  if(isset($_POST['user_url_bwf'])){
    wp_update_user(['ID'=>$user_id,'user_url'=>esc_url_raw($_POST['user_url_bwf'])]);
  }
}

/* --- 대표 회원가입 폼 --- */
add_shortcode('bwf_signup_representative', function () {
  wp_enqueue_style('bwf-forms');

  // 에러/이전값 표시
  $err = get_transient('bwf_signup_err'); delete_transient('bwf_signup_err');
  $old = get_transient('bwf_signup_old'); delete_transient('bwf_signup_old');

  if (is_user_logged_in()) {
    return '<div class="bwos-wrap" style="margin-top:40px;margin-bottom:24px">
      <p style="margin:0 0 18px;font-weight:700">로그인되었습니다.</p>
      <div class="bwf-actions" style="margin-top:0">
        <a class="bwf-btn" href="'.esc_url(home_url('/owner-questions/')).'">질문 등록하기</a>
        <a class="bwf-btn-secondary" href="'.esc_url(home_url('/my-questions/')).'">내 질문 목록 보기</a>
      </div>
    </div>';
  }

  ob_start(); ?>
  <form method="post" class="bwf-form bwf-grid" id="bwf-signup-rep" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" novalidate>
    <input type="hidden" name="action" value="bwf_signup_rep">
    <input type="hidden" name="bwf_role" value="bw_owner">

    <?php wp_nonce_field('bwf_signup_rep','bwf_nonce'); ?>

    <h2 class="bwf-title bwf-col-full" style="margin-top:8px;margin-bottom:10px">대표 회원가입</h2>
    <?php if($err): ?>
      <div class="bwf-card bwf-col-full" style="border-color:#fecaca;background:#fff1f2;color:#7f1d1d;margin-bottom:10px"><?php echo esc_html($err); ?></div>
    <?php endif; ?>
    <p class="bwf-required-note bwf-col-full" style="margin-top:0;margin-bottom:14px">* 표시는 필수 입력</p>

    <div><label style="margin-bottom:14px">아이디 *</label><input type="text" name="user_login" value="<?php echo esc_attr($old['user_login']??''); ?>" pattern="[A-Za-z0-9_\.\-]{4,32}" required></div>
    <div><label style="margin-bottom:14px">비밀번호 *</label><input type="password" name="user_pass" required></div>
    <div><label style="margin-bottom:14px">이메일 *</label><input type="email" name="user_email" value="<?php echo esc_attr($old['user_email']??''); ?>" required></div>
    <div><label style="margin-bottom:14px">이름 *</label><input type="text" name="first_name" value="<?php echo esc_attr($old['first_name']??''); ?>" required></div>

    <div><label style="margin-bottom:14px">회사명 *</label><input type="text" name="bw_company_name" value="<?php echo esc_attr($old['bw_company_name']??''); ?>" required></div>
    <div><label style="margin-bottom:14px">업종 *</label>
      <select name="bw_industry" required>
        <?php $sel = $old['bw_industry']??''; ?>
        <option value="">선택</option>
        <option value="IT/서비스" <?php selected($sel,'IT/서비스'); ?>>IT/서비스</option>
        <option value="커머스" <?php selected($sel,'커머스'); ?>>커머스</option>
        <option value="기타" <?php selected($sel,'기타'); ?>>기타</option>
      </select>
    </div>
    <div><label style="margin-bottom:14px">직원 수 *</label><input type="number" name="bw_employees" value="<?php echo esc_attr($old['bw_employees']??''); ?>" min="1" required></div>
    <div><label style="margin-bottom:14px">홈페이지 URL</label><input type="url" name="user_url" value="<?php echo esc_attr($old['user_url']??''); ?>" placeholder="https://"></div>

    <div class="bwf-col-full">
      <label style="margin-bottom:14px">알게 된 경로 *</label>
      <?php $srcSel = $old['bw_discover']??''; ?>
      <select name="bw_discover" id="bwf-source" required>
        <option value="">선택</option><option value="검색" <?php selected($srcSel,'검색'); ?>>검색</option><option value="SNS" <?php selected($srcSel,'SNS'); ?>>SNS</option><option value="지인추천" <?php selected($srcSel,'지인추천'); ?>>지인추천</option><option value="기타" <?php selected($srcSel,'기타'); ?>>기타</option>
      </select>
      <input type="text" name="source_etc" id="bwf-source-etc" placeholder="기타 경로" class="bwf-col-full <?php echo ($srcSel==='기타'?'':'bwf-hidden'); ?>" style="margin-top:8px" value="<?php echo esc_attr($old['source_etc']??''); ?>">
    </div>

    <div><label style="margin-bottom:14px">휴대폰 번호 *</label>
      <input type="tel" name="bw_phone" id="bwf-phone" inputmode="numeric" placeholder="010-1234-5678" maxlength="13" value="<?php echo esc_attr($old['bw_phone']??''); ?>" required>
    </div>
    <div><label style="margin-bottom:14px">연락 가능한 시간대 *</label><input type="text" name="bw_contact_window" value="<?php echo esc_attr($old['bw_contact_window']??''); ?>" placeholder="예: 평일 13:00~18:00" required></div>

      <div class="bwf-col-full">
  <label class="bwf-checkbox" style="display:flex;gap:.5rem;align-items:flex-start">
    <input type="checkbox" name="bwf_privacy" value="1" required>
    <span>
      <strong>(필수)</strong> 베리워크의 개인정보 수집·이용에 동의합니다.
      <small style="display:block;margin-top:.25rem">
        자세한 내용은
        <a href="<?php echo esc_url( function_exists('get_privacy_policy_url') ? get_privacy_policy_url() : home_url('/privacy-policy/') ); ?>"
           target="_blank" rel="noopener">개인정보처리방침</a>에서 확인할 수 있어요.
      </small>
      <details style="margin-top:.25rem">
        <summary>동의 내용 간단히 보기</summary>
        <ul style="margin:.5rem 0 0 1rem;list-style:disc">
          <li>수집 항목: 이름, 이메일, 휴대폰, 회사명, 웹사이트 주소 등 회원가입에 필요한 정보</li>
          <li>이용 목적: 회원관리, 서비스 제공 및 문의 응대</li>
          <li>보유 기간: 회원 탈퇴 시까지 또는 관련 법령에 따른 기간</li>
        </ul>
      </details>
    </span>
  </label>
</div>



    <div class="bwf-actions bwf-col-full" style="margin-top:18px">
      <button type="submit" name="bwf_register" class="bwf-btn">회원가입</button>
      <span id="bwf-hint" style="font-size:13px;color:#475569"></span>
    </div>

    <script>
    (function(){
      const f=document.getElementById('bwf-signup-rep'),
            src=f.querySelector('#bwf-source'),
            etc=f.querySelector('#bwf-source-etc'),
            tel=f.querySelector('#bwf-phone'),
            hint=document.getElementById('bwf-hint');

      function showEtc(){ if(src.value==='기타'){ etc.classList.remove('bwf-hidden'); } else { etc.classList.add('bwf-hidden'); etc.value=''; } }
      src.addEventListener('change',showEtc); showEtc();

      const dig=s=>String(s||'').replace(/\D/g,'');
      function fmt(raw){ const d=dig(raw).slice(0,11); if(d.length<=3) return d; if(d.length<=7) return d.replace(/^(\d{3})(\d{0,4})$/,'$1-$2'); return d.replace(/^(\d{3})(\d{4})(\d{0,4}).*$/,'$1-$2-$3'); }
      function norm(){ tel.value=fmt(tel.value); try{ tel.setSelectionRange(tel.value.length,tel.value.length);}catch(e){} }
      ['input','keyup','change','paste','blur','focus'].forEach(ev=> tel.addEventListener(ev,norm)); norm();

      f.addEventListener('submit',function(ev){
        norm();
        // 필수항목 검증
        const must=[...f.querySelectorAll('[required]')]; let ok=true; let first=null;
        must.forEach(el=>{
          el.classList.remove('bwf-invalid'); el.setCustomValidity('');
          if(el===tel && !/^010-\d{3,4}-\d{4}$/.test(tel.value)){ el.setCustomValidity('휴대폰 번호는 010-1234-5678 형식'); }
          if(el.name==='bw_discover' && src.value==='기타' && (etc.value||'').trim()===''){ el.setCustomValidity('기타 경로를 입력해 주세요'); }
          if(!el.checkValidity()){ ok=false; if(!first) first=el; el.classList.add('bwf-invalid'); }
        });
        if(!ok){ ev.preventDefault(); hint.textContent='입력이 필요한 항목이 있어요.'; hint.style.color='#ef4444'; first.scrollIntoView({behavior:'smooth',block:'center'}); }
        else   { hint.textContent='제출 중입니다…'; hint.style.color='#475569'; }
      });
    })();
    </script>
  </form>
  <?php
  return ob_get_clean();
});

/* 제출 처리 */
add_action('admin_post_nopriv_bwf_signup_rep','bwf_handle_signup_rep');
function bwf_handle_signup_rep(){
  $old = $_POST; unset($old['action'],$old['bwf_nonce'],$old['user_pass']);
  set_transient('bwf_signup_old',$old,60);

  $nonce = sanitize_text_field($_POST['bwf_nonce'] ?? '');
  if (!wp_verify_nonce($nonce,'bwf_signup_rep')) { set_transient('bwf_signup_err','유효하지 않은 요청입니다.',60); wp_safe_redirect(wp_get_referer()?:home_url('/')); exit; }

  $user_login=sanitize_user($_POST['user_login']??'');
  $user_email=sanitize_email($_POST['user_email']??'');
  $user_pass =$_POST['user_pass']??'';
  $first_name=sanitize_text_field($_POST['first_name']??'');

  if (!$user_login||!$user_email||!$user_pass||!$first_name){ set_transient('bwf_signup_err','필수 항목이 누락되었습니다.',60); wp_safe_redirect(wp_get_referer()?:home_url('/')); exit; }
  // (필수) 개인정보 동의 체크
if ( empty($_POST['bwf_privacy']) ) {
  set_transient('bwf_signup_err', '개인정보 수집·이용 동의(필수)를 체크해 주세요.', 60);
  wp_safe_redirect( wp_get_referer() ?: home_url('/') );
  exit;
}

  $uid = wp_insert_user([
    'user_login'=>$user_login,'user_email'=>$user_email,'user_pass'=>$user_pass,
    'first_name'=>$first_name,'role'=>'bw_owner','user_url'=>esc_url_raw($_POST['user_url']??'')
  ]);

  if (is_wp_error($uid)) {
    set_transient('bwf_signup_err', $uid->get_error_message(), 60);
    wp_safe_redirect(wp_get_referer()?:home_url('/')); exit;
  }

  foreach(['bw_company_name','bw_industry','bw_employees','bw_contact_window','bw_discover','bw_phone'] as $k){
    if(isset($_POST[$k])) update_user_meta($uid,$k,sanitize_text_field($_POST[$k]));
  }
  if (($_POST['bw_discover']??'')==='기타' && !empty($_POST['source_etc'])) {
    update_user_meta($uid,'bw_discover','etc: '.sanitize_text_field($_POST['source_etc']));
  }

  delete_transient('bwf_signup_old');
  update_user_meta( $uid, 'bwf_privacy_agreed_at', current_time('mysql') );

  wp_set_current_user($uid); wp_set_auth_cookie($uid);
  wp_safe_redirect(home_url('/owner-questions/')); exit;
}


/* ===== 관리자 > 대표 질문지( CPT ) 목록 컬럼/제목 ===== */
add_filter('manage_edit-bwf_owner_answer_columns', function($cols){
  $new = [];
  $new['cb']      = $cols['cb'];
  $new['title']   = '제목';
  $new['author']  = '작성자';
  $new['company'] = '회사명';
  $new['website'] = '웹사이트';
  $new['date']    = '저장 시각';
  return $new;
});
add_action('manage_bwf_owner_answer_posts_custom_column', function($col, $post_id){
  if($col==='company'){
    $author = (int)get_post_field('post_author',$post_id);
    echo esc_html( get_user_meta($author,'bw_company_name',true) ?: '—' );
  } elseif($col==='website'){
    $author = (int)get_post_field('post_author',$post_id);
    $url = get_userdata($author)->user_url;
    echo $url ? '<a href="'.esc_url($url).'" target="_blank" rel="noopener">열기</a>' : '—';
  }
},10,2);

/* 관리자 목록에서 예전 글도 보기 좋은 제목으로 표시 */
add_filter('the_title', function($title, $post_id){
  if (!is_admin()) return $title;
  $p = get_post($post_id);
  if(!$p || $p->post_type!=='bwf_owner_answer') return $title;

  // 이미 새 규칙이면 그대로
  if (strpos($title,'#')!==false && strpos($title,'피드백')!==false) return $title;

  $author  = (int)$p->post_author;
  $company = get_user_meta($author,'bw_company_name',true);
  if($company==='') $company = get_userdata($author)->display_name;

  // 사용자 기준 순번 계산 (오래된 순서)
  $ids = get_posts(['post_type'=>'bwf_owner_answer','author'=>$author,'post_status'=>'any','fields'=>'ids','numberposts'=>-1,'orderby'=>'date','order'=>'ASC']);
  $pos = array_search($post_id, $ids, true);
  $idx = ($pos===false? count($ids): $pos) + 1;

  return sprintf('%s - #%d 피드백', $company, $idx);
},10,2);

// ① 우리 대표 가입 폼으로 제출이면, 그 요청에 한해 '새 사용자 기본역할'을 bw_owner로 임시 변경
add_filter('pre_option_default_role', function($default){
  if (!empty($_POST['bwf_role']) && $_POST['bwf_role'] === 'bw_owner') {
    return 'bw_owner';
  }
  return $default;
});

// ② 어떤 경로로 가입되었든, 우리 폼 마커가 있으면 최종적으로 bw_owner로 강제
add_action('user_register', function($user_id){
  if (!empty($_POST['bwf_role']) && $_POST['bwf_role'] === 'bw_owner') {
    $u = new WP_User($user_id);
    $u->set_role('bw_owner');
  }
}, 10);
