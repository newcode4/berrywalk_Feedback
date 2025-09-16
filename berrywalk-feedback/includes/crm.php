<?php
if (!defined('ABSPATH')) exit;

add_action('admin_menu', function(){
  add_menu_page('Berrywalk Feedback','Berrywalk Feedback','manage_options','bwf_crm','bwf_crm_page','dashicons-feedback',26);
});

function bwf_crm_page(){
  $all = get_option('bwf_feedbacks',[]);
  echo '<div class="wrap"><h1>피드백 CRM</h1>';

  if(!$all || !is_array($all)){
    echo '<p>피드백 데이터가 없습니다.</p></div>'; return;
  }

  // GET 필터
  $repFilter = isset($_GET['rep']) ? intval($_GET['rep']) : 0;
  if ($repFilter) {
    $all = array_values(array_filter($all, fn($r)=> intval($r['rep']??0) === $repFilter));
    echo '<p><strong>대표ID 필터:</strong> '.esc_html($repFilter).' <a href="'.esc_url(remove_query_arg('rep')).'">전체보기</a></p>';
  }

  // 요약
  $total = count($all);
  $byRep = [];
  foreach($all as $r){
    $rep = intval($r['rep'] ?? 0);
    $byRep[$rep] = ($byRep[$rep] ?? 0) + 1;
  }
  arsort($byRep);

  echo '<div style="display:flex; gap:16px; margin:12px 0 18px;">';
  echo '<div style="padding:12px 16px; background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px;">총 응답: <b>'.$total.'</b></div>';
  echo '<div style="padding:12px 16px; background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px;">대표 수: <b>'.count($byRep).'</b></div>';
  echo '</div>';

  // 검색 입력
  $q = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
  if ($q !== '') {
    $all = array_values(array_filter($all, function($r) use($q){
      $hay = wp_json_encode($r['answers'] ?? [], JSON_UNESCAPED_UNICODE);
      return (stripos($hay, $q) !== false);
    }));
  }
  echo '<form method="get" style="margin:8px 0 14px;">';
  echo '<input type="hidden" name="page" value="bwf_crm">';
  echo '<input type="search" name="s" value="'.esc_attr($q).'" placeholder="내용 검색" style="min-width:280px;"> ';
  echo '<button class="button">검색</button> ';
  echo '<a class="button" href="'.esc_url(admin_url('admin.php?page=bwf_crm')).'">초기화</a>';
  echo '</form>';

  // 정렬
  $orderby = $_GET['orderby'] ?? 't';
  $order   = strtolower($_GET['order'] ?? 'desc') === 'asc' ? 'asc' : 'desc';
  usort($all, function($a,$b) use($orderby,$order){
    $va = $a[$orderby] ?? '';
    $vb = $b[$orderby] ?? '';
    if ($va == $vb) return 0;
    $cmp = ($va < $vb) ? -1 : 1;
    return $order === 'asc' ? $cmp : -$cmp;
  });
  $link = function($key,$label) use($orderby,$order){
    $next = ($orderby===$key && $order==='asc') ? 'desc' : 'asc';
    $url = add_query_arg(['orderby'=>$key,'order'=>$next]);
    return '<a href="'.esc_url($url).'">'.$label.($orderby===$key?($order==='asc'?' ▲':' ▼'):'').'</a>';
  };

  echo '<table class="widefat striped"><thead><tr>';
  echo '<th>'.$link('t','시각').'</th><th>'.$link('rep','대표ID').'</th><th>'.$link('user','작성자').'</th><th>요약/보기</th>';
  echo '</tr></thead><tbody>';

  foreach($all as $r){
    $answers = $r['answers'] ?? [];
    // 짧은 요약(앞 120자)
    $flat = trim(wp_strip_all_tags(implode(' ', array_map('strval',$answers))));
    $short = mb_substr($flat, 0, 120) . (mb_strlen($flat) > 120 ? '…' : '');
    $view  = '<details><summary>전체 보기</summary><pre style="white-space:pre-wrap">'.esc_html(print_r($answers,true)).'</pre></details>';

    $repLink = add_query_arg(['rep'=>intval($r['rep']??0)], remove_query_arg(['orderby','order']));
    echo '<tr>';
    echo '<td>'.esc_html($r['t']).'</td>';
    echo '<td><a href="'.esc_url($repLink).'">'.intval($r['rep']??0).'</a></td>';
    echo '<td>'.intval($r['user']??0).'</td>';
    echo '<td><div>'.esc_html($short).'</div>'.$view.'</td>';
    echo '</tr>';
  }
  echo '</tbody></table></div>';
}
