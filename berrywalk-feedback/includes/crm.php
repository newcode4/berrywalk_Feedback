<?php
if (!defined('ABSPATH')) exit;

/** ====== 관리자 메뉴 등록 ====== */
add_action('admin_menu', function(){
  add_menu_page(
    '베리워크 CRM',          // page title
    '베리워크 CRM',          // menu title
    'manage_options',        // capability
    'bwf-crm',               // slug
    'bwf_render_crm_page',   // callback
    'dashicons-list-view',
    56
  );
});

/** 안전하게 전체 피드백 로드 */
function bwf_get_all_feedbacks(){
  $all = get_option('bwf_feedbacks', []);
  if (!is_array($all)) $all = [];
  return $all;
}

/** ====== CRM 렌더 ====== */
function bwf_render_crm_page(){
  if (!current_user_can('manage_options')) return;

  // ---- 일괄 삭제 처리 ----
  if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bwf_crm_bulk'])) {
    check_admin_referer('bwf_crm_bulk');
    $ids = array_filter((array)($_POST['sel'] ?? []), 'is_string');

    if ($ids) {
      $all = bwf_get_all_feedbacks();
      // id가 있는 항목만 일괄삭제. 구데이터(id 없음)는 보호(개별삭제에서 처리 예정)
      $all = array_values(array_filter($all, function($r) use ($ids){
        $rid = (string)($r['id'] ?? '');
        return ($rid === '' || !in_array($rid, $ids, true));
      }));
      update_option('bwf_feedbacks', $all, false);
      echo '<div class="updated notice is-dismissible"><p>선택 항목을 삭제했습니다.</p></div>';
    }
  }

  // ---- 데이터/페이지네이션 ----
  $all   = bwf_get_all_feedbacks();
  $per   = 20;
  $total = count($all);
  $pages = max(1, (int)ceil($total / $per));
  $paged = max(1, (int)($_GET['paged'] ?? 1));
  $start = ($paged - 1) * $per;

  // 최신순으로 보이도록 역순 슬라이스
  $rows = array_slice(array_reverse($all), $start, $per);

  // ---- 렌더 ----
  echo '<div class="wrap"><h1>베리워크 CRM</h1>';

  echo '<form method="post">';
  wp_nonce_field('bwf_crm_bulk');
  echo '<table class="widefat striped">';
  echo '<thead><tr>';
  echo '<th style="width:24px"><input type="checkbox" id="bwfChkAll"></th>';
  echo '<th>시각(서울)</th><th>대표ID</th><th>작성자</th><th>요약</th>';
  echo '</tr></thead><tbody>';

  if (!$rows) {
    echo '<tr><td colspan="5">데이터가 없습니다.</td></tr>';
  } else {
    foreach ($rows as $r) {
      $rid     = (string)($r['id'] ?? '');
      $t       = (string)($r['t'] ?? '');           // 저장 시 wp_date 사용
      $rep     = (int)($r['rep'] ?? 0);
      $user    = (int)($r['user'] ?? 0);
      $answers = (array)($r['answers'] ?? []);

      // 요약 만들기(중첩 배열 평탄화)
      $flat=[]; $stack=[$answers];
      while ($stack) {
        $cur=array_pop($stack);
        foreach ($cur as $v) { is_array($v) ? $stack[]=$v : $flat[]=trim((string)$v); }
      }
      $short = mb_substr(implode(' ', array_filter($flat)), 0, 120) . (count($flat)?'…':'');

      echo '<tr>';
      echo '<td>'.($rid!=='' ? '<input type="checkbox" name="sel[]" value="'.esc_attr($rid).'">' : '').'</td>';
      echo '<td>'.esc_html($t).'</td>';
      echo '<td>'.esc_html($rep).'</td>';
      echo '<td>'.esc_html($user).'</td>';
      echo '<td>'.esc_html($short).'</td>';
      echo '</tr>';
    }
  }

  echo '</tbody></table>';

  echo '<p><button class="button button-secondary" name="bwf_crm_bulk" value="1" onclick="return confirm(\'선택 항목을 삭제할까요?\')">선택 삭제</button></p>';
  echo '</form>';

  // ---- 페이지네이션 ----
  if ($pages > 1) {
    $base = remove_query_arg('paged');
    echo '<div class="tablenav"><div class="tablenav-pages">';
    echo '<span class="displaying-num">총 '.intval($total).'개</span> ';
    echo '<span class="pagination-links">';
    if ($paged>1)   echo '<a class="tablenav-pages-navspan" href="'.esc_url(add_query_arg('paged',$paged-1,$base)).'">‹</a> ';
    echo '<span class="paging-input">'.$paged.' / '.$pages.'</span>';
    if ($paged<$pages) echo ' <a class="tablenav-pages-navspan" href="'.esc_url(add_query_arg('paged',$paged+1,$base)).'">›</a>';
    echo '</span></div></div>';
  }

  echo '</div>'; // .wrap

  // 체크박스 전체 선택
  echo '<script>document.getElementById("bwfChkAll")?.addEventListener("change",e=>{document.querySelectorAll(\'input[name="sel[]"]\').forEach(c=>c.checked=e.target.checked);});</script>';
}

$ts = isset($r['ts']) ? intval($r['ts']) : strtotime((string)($r['t'] ?? 'now'));
$t_local = wp_date('Y-m-d H:i:s', $ts);
echo '<td>'.esc_html($t_local).'</td>';
