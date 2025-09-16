<?php
if (!defined('ABSPATH')) exit;

add_action('admin_menu', function(){
  add_menu_page('Berrywalk Feedback','Berrywalk Feedback','manage_options','bwf_crm','bwf_crm_landing','dashicons-feedback',26);
 add_submenu_page('bwf_crm','대표 질문 보기','대표 질문 보기','manage_options','bwf_question_view','bwf_question_view_page');

  // 기존 CRM 페이지(피드백 수집은 나중에): 필요 시 유지
  // add_submenu_page('bwf_crm','피드백 CRM','피드백 CRM','manage_options','bwf_crm_list','bwf_crm_page');
});

function bwf_crm_landing(){
  echo '<div class="wrap"><h1>Berrywalk Feedback</h1><p>왼쪽 하위 메뉴에서 이동하세요.</p></div>';
}

function bwf_questions_page(){
  // 검색어
  $q = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';

  // 대표(사용자) 조회: bwf_questions 메타가 있는 사용자만
  $args = [
    'meta_key'   => 'bwf_questions',
    'meta_compare' => 'EXISTS',
    'number' => 2000, // 충분히 크게
    'fields' => ['ID','user_login','user_email','display_name']
  ];
 

    $qs_now = get_user_meta($u->ID,'bwf_questions',true);
    $hist   = get_user_meta($u->ID,'bwf_questions_history',true);
    if (!is_array($hist)) $hist = [];
    $items = [];
    if (is_array($qs_now) && !empty($qs_now)) $items[]=$qs_now;
    $items = array_merge($items, $hist);

    foreach($items as $qs){
    if (!is_array($qs) || empty($qs)) continue;
    $rows = [];
    $q = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';

    $users = new WP_User_Query([
    'meta_key' => 'bwf_questions',
    'meta_compare' => 'EXISTS',
    'fields' => ['ID','user_login','user_email','display_name'],
    'number' => 2000,
    ]);
    foreach($users->get_results() as $u){
    $now  = get_user_meta($u->ID,'bwf_questions',true);
    $hist = get_user_meta($u->ID,'bwf_questions_history',true); if (!is_array($hist)) $hist=[];
    $items = [];
    if (is_array($now) && !empty($now)) $items[]=$now;
    $items = array_merge($items,$hist);

    foreach($items as $qs){
        if (!is_array($qs) || empty($qs)) continue;
        $flat = wp_strip_all_tags(implode(' ', array_map('strval',$qs)));
        if ($q!=='' && stripos($flat,$q)===false) continue;

        $rows[] = [
        'id'    => $u->ID,
        'name'  => $u->display_name ?: $u->user_login,
        'email' => $u->user_email,
        't'     => $qs['_saved_at'] ?? '',
        'qid'   => $qs['_id'] ?? '',
        'flat'  => $flat,
        'data'  => $qs,
        ];
    }
    }


  // 정렬
  $orderby = $_GET['orderby'] ?? 't';
  $order   = strtolower($_GET['order'] ?? 'desc')==='asc' ? 'asc' : 'desc';
  usort($rows, function($a,$b) use($orderby,$order){
    $va = $a[$orderby] ?? ''; $vb = $b[$orderby] ?? '';
    if ($va==$vb) return 0;
    $cmp = ($va < $vb) ? -1 : 1;
    return $order==='asc' ? $cmp : -$cmp;
  });

  $lnk = function($key,$label) use($orderby,$order){
    $next = ($orderby===$key && $order==='asc') ? 'desc' : 'asc';
    $url = add_query_arg(['orderby'=>$key,'order'=>$next]);
    return '<a href="'.esc_url($url).'">'.$label.($orderby===$key?($order==='asc'?' ▲':' ▼'):'').'</a>';
  };

  echo '<div class="wrap"><h1>대표 질문지</h1>';
  echo '<form method="get" style="margin:8px 0 14px;">';
  echo '<input type="hidden" name="page" value="bwf_questions">';
  echo '<input type="search" name="s" value="'.esc_attr($q).'" placeholder="내용 검색" style="min-width:280px;"> ';
  echo '<button class="button">검색</button> ';
  echo '<a class="button" href="'.esc_url(admin_url('admin.php?page=bwf_questions')).'">초기화</a>';
  echo '</form>';

  if (empty($rows)) { echo '<p>저장된 대표 질문이 없습니다.</p></div>'; return; }

  echo '<table class="widefat striped"><thead><tr>';
  echo '<th>'.$lnk('t','저장시각').'</th><th>'.$lnk('id','대표ID').'</th><th>'.$lnk('name','대표명').'</th>';
  $view = add_query_arg(['page'=>'bwf_question_view','uid'=>$r['id'],'qid'=>$r['qid']], admin_url('admin.php'));
    echo '<td><a class="button" href="'.esc_url($view).'">폼 레이아웃으로 보기</a></td>';


  echo '</tr></thead><tbody>';

  foreach($rows as $r){
    $qs = $r['data'];
    $summary = esc_html(mb_substr($r['flat'],0,140)).(mb_strlen($r['flat'])>140?'…':'');
    echo '<tr>';
    echo '<td>'.esc_html($r['t']).'</td>';
    echo '<td>'.intval($r['id']).'</td>';
    echo '<td>'.esc_html($r['name']).' <span style="color:#64748b">('.esc_html($r['email']).')</span></td>';
    echo '<td>';
    echo '<div>'.$summary.'</div>';
    echo '<details><summary>전체 보기</summary>';
    echo '<ul style="margin:8px 0 0 18px; list-style:disc">';
    $map = [
      'problem'=>'① 가장 큰 고민', 'value'=>'② 핵심 가치', 'ideal_customer'=>'③ 이상적 타겟',
      'q1'=>'④-1 질문', 'q2'=>'④-2 질문', 'q3'=>'④-3 질문',
      'one_question'=>'⑤ 1:1 한 가지', 'competitors'=>'⑥ 경쟁/차별'
    ];
    foreach($map as $k=>$title){
      if (!empty($qs[$k])) echo '<li><b>'.$title.':</b> '.esc_html($qs[$k]).'</li>';
    }
    echo '</ul></details>';
    echo '</td>';
    echo '</tr>';
  }
  echo '</tbody></table></div>';
}

function bwf_question_view_page(){
  $uid = intval($_GET['uid'] ?? 0);
  $qid = sanitize_text_field($_GET['qid'] ?? '');
  if (!$uid || !$qid){ echo '<div class="wrap"><h1>잘못된 요청</h1></div>'; return; }

  $now  = get_user_meta($uid,'bwf_questions',true);
  $hist = get_user_meta($uid,'bwf_questions_history',true); if (!is_array($hist)) $hist=[];
  $items = [];
  if (is_array($now) && !empty($now)) $items[]=$now;
  $items = array_merge($items,$hist);

  $found=null;
  foreach($items as $it){ if (!empty($it['_id']) && $it['_id']===$qid){ $found=$it; break; } }
  echo '<div class="wrap"><h1>대표 질문 보기</h1>';
  if(!$found){ echo '<p>해당 항목을 찾을 수 없습니다.</p></div>'; return; }

  echo '<div class="bwf-form" style="max-width:900px">';
  $F = [
    'problem'=>'1. 현재 비즈니스에서 가장 큰 고민은 무엇인가요?',
    'value'=>'2. 어떤 문제를 해결하나요?',
    'ideal_customer'=>'3. 누가 이용/왜 선택?',
    'q1'=>'4-1. 맞춤 질문','q2'=>'4-2. 맞춤 질문','q3'=>'4-3. 맞춤 질문',
    'one_question'=>'5. 1:1 한 가지','competitors'=>'6. 경쟁/차별'
  ];
  foreach($F as $k=>$lab){
    echo '<div class="bwf-col-full" style="margin:10px 0 14px">';
    echo '<label>'.$lab.'</label>';
    if ($k==='one_question'){
      echo '<input type="text" value="'.esc_attr($found[$k]??'').'" readonly>';
    }else{
      echo '<textarea readonly>'.esc_textarea($found[$k]??'').'</textarea>';
    }
    echo '</div>';
  }
  echo '</div></div>';
}