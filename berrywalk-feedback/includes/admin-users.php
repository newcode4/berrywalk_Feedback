<?php
if (!defined('ABSPATH')) exit;

/** Users 목록 컬럼 */
add_filter('manage_users_columns', function($cols){
  $cols['bw_company_name']='회사명';
  $cols['bw_industry']='업종';
  $cols['bw_phone']='휴대폰';
  $cols['bw_contact']='연락 가능 시간';
  $cols['bw_discover']='유입 경로';
  return $cols;
});
add_filter('manage_users_custom_column', function($val,$col,$user_id){
  switch($col){
    case 'bw_company_name': return esc_html(get_user_meta($user_id,'bw_company_name',true));
    case 'bw_industry':     return esc_html(get_user_meta($user_id,'bw_industry',true));
    case 'bw_phone':        return esc_html(get_user_meta($user_id,'bw_phone',true));
    case 'bw_contact':      return esc_html(get_user_meta($user_id,'bw_contact_window',true));
    case 'bw_discover':     return esc_html(get_user_meta($user_id,'bw_discover',true));
  }
  return $val;
},10,3);

/** 프로필 화면(수정 가능) */
function bwf_admin_profile_block($user){
  $f = function($key,$label) use($user){
    $v = get_user_meta($user->ID,$key,true);
    echo '<tr><th>'.$label.'</th><td><input name="'.$key.'" type="text" class="regular-text" value="'.esc_attr($v).'"></td></tr>';
  };
  echo '<h2>Berrywalk 가입 정보</h2><table class="form-table">';
  $f('bw_company_name','회사명'); $f('bw_industry','업종');
  $f('bw_employees','직원 수');   $f('bw_phone','휴대폰');
  $f('bw_contact_window','연락 가능 시간'); $f('bw_discover','유입 경로');
  echo '</table>';
}
add_action('show_user_profile','bwf_admin_profile_block');
add_action('edit_user_profile','bwf_admin_profile_block');

/** 저장 */
function bwf_save_profile($user_id){
  foreach(['bw_company_name','bw_industry','bw_employees','bw_phone','bw_contact_window','bw_discover'] as $k){
    if (isset($_POST[$k])) update_user_meta($user_id, $k, sanitize_text_field($_POST[$k]));
  }
}
add_action('personal_options_update','bwf_save_profile');
add_action('edit_user_profile_update','bwf_save_profile');

// WP-Members 추가 필드 섹션 임시 숨김(UX 보호)
add_action('admin_print_footer_scripts', function(){
  ?>
  <script>
  jQuery(function($){
    $("h2:contains('WP-Members 추가 필드')").nextUntil("h2").hide().end().hide();
  });
  </script>
  <?php
});
