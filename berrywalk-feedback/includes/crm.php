<?php
if (!defined('ABSPATH')) exit;

/** 메뉴 */
add_action('admin_menu', function(){
  add_menu_page('베리워크 CRM','베리워크 CRM','manage_options','bwf-crm','bwf_render_crm_page','dashicons-list-view',56);
});

/** 데이터 로더 */
function bwf_get_all_feedbacks(){
  $all = get_option('bwf_feedbacks', []);
  return is_array($all) ? $all : [];
}

/** 화면 */
function bwf_render_crm_page(){
  if (!current_user_can('manage_options')) return;

  /* 일괄 삭제 */
  if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['bwf_crm_bulk'])){
    check_admin_referer('bwf_crm_bulk');
    $ids = array_filter((array)($_POST['sel'] ?? []), 'is_string');
    if ($ids){
      $all = bwf_get_all_feedbacks();
      $all = array_values(array_filter($all, function($r) use($ids){
        $rid = (string)($r['id'] ?? '');
        return ($rid==='' || !in_array($rid,$ids,true));
      }));
      update_option('bwf_feedbacks', $all, false);
      echo '<div class="updated notice is-dismissible"><p>선택 항목을 삭제했습니다.</p></div>';
    }
  }

  /* 검색 */
  $keyword = sanitize_text_field($_GET['s'] ?? '');
  $raw = array_reverse(bwf_get_all_feedbacks());
  $rowsAll = array_filter($raw, function($r) use($keyword){
    if ($keyword==='') return true;
    $u   = get_userdata((int)($r['rep'] ?? 0));
    $hay = implode(' ', array_filter([
      $u? $u->display_name : '',
      $u? $u->user_email   : '',
      wp_json_encode($r['answers'] ?? [], JSON_UNESCAPED_UNICODE)
    ]));
    return mb_stripos($hay, $keyword) !== false;
  });

  /* 페이징 */
  $per=20; $total=count($rowsAll);
  $pages=max(1,(int)ceil($total/$per));
  $paged=max(1,(int)($_GET['paged']??1));
  $start=($paged-1)*$per;
  $rows=array_slice($rowsAll,$start,$per);

  echo '<div class="wrap"><h1>베리워크 CRM</h1>';

  /* 검색폼 */
  echo '<form method="get" style="margin:8px 0 14px"><input type="hidden" name="page" value="bwf-crm">';
  echo '<input type="search" name="s" value="'.esc_attr($keyword).'" placeholder="대표명/이메일/내용 검색"> ';
  echo '<button class="button">검색</button></form>';

  /* 테이블 */
  echo '<form method="post">';
  wp_nonce_field('bwf_crm_bulk');
  echo '<table class="widefat striped"><thead><tr>';
  echo '<th style="width:24px"><input type="checkbox" id="bwfChkAll"></th>';
  echo '<th>시각(서울)</th><th>대표</th><th>작성자ID</th><th>요약</th><th>동작</th>';
  echo '</tr></thead><tbody>';

  if (!$rows){
    echo '<tr><td colspan="6">데이터가 없습니다.</td></tr>';
  } else {
    foreach ($rows as $r){
      $rid = (string)($r['id'] ?? '');
      $rep = (int)($r['rep'] ?? 0);
      $usr = (int)($r['user'] ?? 0);
      $ans = (array)($r['answers'] ?? []);

      // 요약
      $flat=[]; $stack=[$ans];
      while ($stack){ $cur=array_pop($stack); foreach($cur as $v){ is_array($v)?$stack[]=$v:$flat[]=trim((string)$v);} }
      $short = mb_substr(implode(' ', array_filter($flat)),0,120) . (count($flat)?'…':'');

      // 시간(서울)
      $ts = isset($r['ts']) ? (int)$r['ts'] : strtotime((string)($r['t'] ?? 'now'));
      $t_local = function_exists('bwf_fmt') ? bwf_fmt($ts) : wp_date('Y-m-d H:i:s',$ts);

      // 대표명
      $repname=''; if($rep){ $u=get_userdata($rep); if($u){ $repname=$u->display_name.' ('.$u->user_email.')'; } }

      // 개별 삭제
      $del = $rid!=='' ? wp_nonce_url(admin_url('admin-post.php?action=bwf_crm_del&id='.$rid),'bwf_crm_del_'.$rid) : '';

      echo '<tr>';
      echo '<td>'.($rid!==''?'<input type="checkbox" name="sel[]" value="'.esc_attr($rid).'">':'').'</td>';
      echo '<td>'.esc_html($t_local).'</td>';
      echo '<td>'.esc_html($repname).'</td>';
      echo '<td>'.esc_html($usr).'</td>';
      echo '<td>'.esc_html($short).'</td>';
      echo '<td>'.($rid!==''?'<a class="button button-small" href="'.$del.'" onclick="return confirm(\'삭제할까요?\')">삭제</a>':'').'</td>';
      echo '</tr>';
    }
  }
  echo '</tbody></table>';
  echo '<p><button class="button button-secondary" name="bwf_crm_bulk" value="1" onclick="return confirm(\'선택 항목을 삭제할까요?\')">선택 삭제</button></p>';
  echo '</form>';

  /* 페이지네이션 */
  if ($pages>1){
    $base = remove_query_arg('paged');
    echo '<div class="tablenav"><div class="tablenav-pages">';
    echo '<span class="displaying-num">총 '.intval($total).'개</span> <span class="pagination-links">';
    if ($paged>1) echo '<a class="tablenav-pages-navspan" href="'.esc_url(add_query_arg('paged',$paged-1,$base)).'">‹</a> ';
    echo '<span class="paging-input">'.$paged.' / '.$pages.'</span>';
    if ($paged<$pages) echo ' <a class="tablenav-pages-navspan" href="'.esc_url(add_query_arg('paged',$paged+1,$base)).'">›</a>';
    echo '</span></div></div>';
  }
  echo '</div>';

  /* 전체선택 스크립트 */
  echo '<script>document.getElementById("bwfChkAll")?.addEventListener("change",e=>{document.querySelectorAll(\'input[name="sel[]"]\').forEach(c=>c.checked=e.target.checked);});</script>';
}

/** 개별 삭제 핸들러 */
add_action('admin_post_bwf_crm_del', function(){
  if (!current_user_can('manage_options')) wp_die('forbidden');
  $id = sanitize_text_field($_GET['id'] ?? '');
  $ok = isset($_GET['_wpnonce']) && wp_verify_nonce($_GET['_wpnonce'], 'bwf_crm_del_'.$id);
  if (!$ok || !$id){ wp_safe_redirect(admin_url('admin.php?page=bwf-crm')); exit; }
  $all = bwf_get_all_feedbacks();
  $all = array_values(array_filter($all, fn($r)=>(string)($r['id']??'')!==$id));
  update_option('bwf_feedbacks', $all, false);
  wp_safe_redirect(admin_url('admin.php?page=bwf-crm')); exit;
});
