<?php
if (!defined('ABSPATH')) exit;

add_action('admin_menu', function(){
  add_menu_page('Berrywalk Feedback','Berrywalk Feedback','manage_options','bwf_crm','bwf_crm_landing','dashicons-feedback',26);
  add_submenu_page('bwf_crm','대표 질문지','대표 질문지','manage_options','bwf_questions','bwf_questions_page');
});

function bwf_crm_landing(){
  echo '<div class="wrap"><h1>Berrywalk Feedback</h1><p>왼쪽 하위 메뉴에서 이동하세요.</p></div>';
}

function bwf_questions_page(){
  $q = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';

  // 대표(사용자) 조회: bwf_questions 메타가 있는 사용자만
  $users = new WP_User_Query([
    'meta_key'   => 'bwf_questions',
    'meta_compare' => 'EXISTS',
    'fields'     => ['ID','user_login','user_email','display_name'],
    'number'     => 2000,
  ]);

  $rows = [];
  foreach($users->get_results() as $u){
    $now  = get_user_meta($u->ID,'bwf_questions',true);
    $hist = get_user_meta($u->ID,'bwf_questions_history',true); if(!is_array($hist)) $hist=[];
    $items=[]; if(is_array($now)&&$now) $items[]=$now; $items=array_merge($items,$hist);

    foreach($items as $qs){
      if(!is_array($qs) || empty($qs)) continue;
      $flat = wp_strip_all_tags(implode(' ', array_map('strval',$qs)));
      if($q && mb_stripos($flat,$q)===false) continue;

      $rows[] = [
        'id'    => $u->ID,
        'name'  => $u->display_name ?: $u->user_login,
        'email' => $u->user_email,
        't'     => $qs['_saved_at'] ?? '',
        'data'  => $qs,
        'flat'  => $flat,
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

  echo '<table class="widefat striped"><thead><tr><th>저장시각</th><th>대표ID</th><th>대표명</th><th>질문 요약/전체</th></tr></thead><tbody>';
  foreach($rows as $r){
    $qs = $r['data'];
    $summary = esc_html(mb_substr($r['flat'],0,140)).(mb_strlen($r['flat'])>140?'…':'');
    $goto = esc_url(add_query_arg(['qid'=>($qs['_id'] ?? '')], $view_url));

    echo '<tr>';
    echo '<td>'.esc_html($r['t']).'</td>';
    echo '<td>'.intval($r['id']).'</td>';
    echo '<td>'.esc_html($r['name']).' <span style="color:#64748b">('.esc_html($r['email']).')</span></td>';
    echo '<td>';
      echo '<div style="margin-bottom:4px">'.$summary.'</div>';
      echo '<details><summary>전체 보기</summary><ul style="margin:8px 0 0 18px; list-style:disc">';
      $map = [
        'problem'=>'① 가장 큰 고민','value'=>'② 핵심 가치','ideal_customer'=>'③ 이상적 타겟',
        'q1'=>'④-1 질문','q2'=>'④-2 질문','q3'=>'④-3 질문',
        'one_question'=>'⑤ 1:1 한 가지','competitors'=>'⑥ 경쟁/차별'
      ];
      foreach($map as $k=>$title){ if(!empty($qs[$k])) echo '<li><b>'.$title.':</b> '.esc_html($qs[$k]).'</li>'; }
      echo '</ul></details>';
      echo '<div style="margin-top:6px"><a class="button" href="'.$goto.'" target="_blank">폼 레이아웃으로 보기</a></div>';
    echo '</td>';
    echo '</tr>';
  }
  echo '</tbody></table></div>';
}
