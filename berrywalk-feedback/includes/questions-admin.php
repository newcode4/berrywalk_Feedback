<?php
if (!defined('ABSPATH')) exit;

require_once __DIR__ . '/helper.php';

add_action('admin_menu', function(){
  add_menu_page('Berrywalk Feedback','Berrywalk Feedback','manage_options','bwf_crm','bwf_crm_landing','dashicons-feedback',26);
  add_submenu_page('bwf_crm','대표 질문지','대표 질문지','manage_options','bwf_questions','bwf_questions_page');
});

function bwf_crm_landing(){
  echo '<div class="wrap"><h1>Berrywalk Feedback</h1><p>왼쪽 하위 메뉴에서 이동하세요.</p></div>';
}

function bwf_questions_page(){
  $q = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';

  // bwf_questions 메타가 있는 사용자만
  $users = new WP_User_Query([
    'meta_key'     => 'bwf_questions',
    'meta_compare' => 'EXISTS',
    'fields'       => ['ID','user_login','user_email','display_name'],
    'number'       => 2000,
  ]);

  $rows = [];
  foreach($users->get_results() as $u){
    $now  = get_user_meta($u->ID,'bwf_questions',true);
    $hist = get_user_meta($u->ID,'bwf_questions_history',true);
    if(!is_array($hist)) $hist=[];
    $items=[]; if(is_array($now)&&$now) $items[]=$now; $items=array_merge($items,$hist);

    foreach($items as $qs){
      if(!is_array($qs) || empty($qs)) continue;
      $flat = wp_strip_all_tags(implode(' ', array_map('strval',$qs)));
      if($q && mb_stripos($flat,$q)===false) continue;

      // 대표 메타
      $company = get_user_meta($u->ID,'bw_company_name',true);
      $industry_key = get_user_meta($u->ID,'bw_industry',true);
      $industry_map = bwf_industry_options();
      $industry = $industry_map[$industry_key] ?? $industry_key;
      $phone    = get_user_meta($u->ID,'bw_phone',true);

      $rows[] = [
        'id'      => $u->ID,
        'name'    => $u->display_name ?: $u->user_login,
        'email'   => $u->user_email,
        't'       => $qs['_saved_at'] ?? '',
        'company' => $company,
        'industry'=> $industry,
        'phone'   => $phone,
        'data'    => $qs,
      ];
    }
  }

  echo '<div class="wrap"><h1>대표 질문지</h1>';
  echo '<form method="get" style="margin:12px 0">';
  echo '<input type="hidden" name="page" value="bwf_questions">';
  echo '<input type="search" name="s" value="'.esc_attr($q).'" placeholder="내용 검색" style="min-width:280px;"> ';
  echo '<button class="button">검색</button> ';
  echo '<a class="button" href="'.esc_url(admin_url('admin.php?page=bwf_questions')).'">초기화</a>';
  echo '</form>';

  if (empty($rows)) { echo '<p>저장된 대표 질문이 없습니다.</p></div>'; return; }

  // 보기 페이지(URL)
  $view_page = get_page_by_path('my-question-view');
  $view_url  = $view_page ? get_permalink($view_page) : home_url('/my-question-view/');

  echo '<table class="widefat striped"><thead><tr>';
  echo '<th>저장시각</th><th>대표ID</th><th>대표명(이메일)</th><th>회사명</th><th>업종</th><th>휴대폰</th><th>보기</th>';
  echo '</tr></thead><tbody>';

  foreach($rows as $r){
    $qs = $r['data'];
    $goto = esc_url(add_query_arg([
      'qid' => ($qs['_id'] ?? ''),
      'uid' => $r['id'],
    ], $view_url));

    echo '<tr>';
    echo '<td>'.esc_html($r['t']).'</td>';
    echo '<td>'.intval($r['id']).'</td>';
    echo '<td>'.esc_html($r['name']).' <span style="color:#64748b">('.esc_html($r['email']).')</span></td>';
    echo '<td>'.esc_html($r['company']).'</td>';
    echo '<td>'.esc_html($r['industry']).'</td>';
    echo '<td>'.esc_html($r['phone']).'</td>';
    echo '<td><a class="button button-secondary" href="'.$goto.'" target="_blank">보기</a></td>';
    echo '</tr>';
  }
  echo '</tbody></table></div>';
}
