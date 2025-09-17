<?php
if (!defined('ABSPATH')) exit;

add_action('admin_menu', function(){
  add_menu_page('Berrywalk Feedback','Berrywalk Feedback','manage_options','bwf_crm','bwf_crm_page','dashicons-feedback',26);
});

$per = 20;
$total = count($all);
$paged = max(1, intval($_GET['paged'] ?? 1));
$start = ($paged-1)*$per;
$rows = array_slice($all, $start, $per);

if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['bwf_crm_bulk']) && check_admin_referer('bwf_crm_bulk')) {
  $ids = array_filter((array)($_POST['sel'] ?? []), 'is_string');
  if ($ids) {
    $all = array_values(array_filter(get_option('bwf_feedbacks', []), function($r) use ($ids){
      $rid = (string)($r['id'] ?? '');
      return $rid === '' || !in_array($rid, $ids, true); // id 없는 구데이터는 보호 → 개별삭제로 처리
    }));
    update_option('bwf_feedbacks', $all, false);
  }
  wp_safe_redirect( remove_query_arg(['paged']) ); exit;
}


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

// 테이블 폼 & 체크박스/삭제 버튼
echo '<form method="post">';
wp_nonce_field('bwf_crm_bulk');
echo '<table class="widefat striped"><thead><tr>';
echo '<th style="width:24px"><input type="checkbox" id="chkAll"></th>';
echo '<th>'.$link('t','시각').'</th><th>'.$link('rep','대표ID').'</th><th>'.$link('user','작성자').'</th><th>요약/보기</th>';
echo '</tr></thead><tbody>';

foreach($rows as $r){
  $rid = (string)($r['id'] ?? ''); // 구데이터면 빈문자
  // ... (요약 생성은 기존대로)
  echo '<tr>';
  echo '<td>';
  if ($rid!=='') echo '<input type="checkbox" name="sel[]" value="'.esc_attr($rid).'">';
  echo '</td>';
  echo '<td>'.esc_html($r['t']).'</td>';
  echo '<td>'.intval($r['rep']??0).'</td>';
  echo '<td>'.intval($r['user']??0).'</td>';
  echo '<td><div>'.esc_html($short).'</div>'.$view.'</td>';
  echo '</tr>';
}
echo '</tbody></table>';

echo '<p><button class="button button-secondary" name="bwf_crm_bulk" value="1" onclick="return confirm(\'선택 항목을 삭제할까요?\')">선택 삭제</button></p>';
echo '</form>';

echo '<div class="tablenav"><div class="tablenav-pages">';
$pages = max(1, ceil($total/$per));
if ($pages>1){
  $base = remove_query_arg('paged');
  echo '<span class="displaying-num">총 '.intval($total).'개</span> ';
  echo '<span class="pagination-links">';
  if ($paged>1) echo '<a class="tablenav-pages-navspan" href="'.esc_url(add_query_arg('paged',$paged-1,$base)).'">‹</a> ';
  echo '<span class="paging-input">'.$paged.' / '.$pages.'</span>';
  if ($paged<$pages) echo ' <a class="tablenav-pages-navspan" href="'.esc_url(add_query_arg('paged',$paged+1,$base)).'">›</a>';
  echo '</span>';
}
echo '</div></div>';

echo '<script>document.getElementById("chkAll")?.addEventListener("change",e=>{document.querySelectorAll(\'input[name="sel[]"]\').forEach(c=>c.checked=e.target.checked);});</script>';
