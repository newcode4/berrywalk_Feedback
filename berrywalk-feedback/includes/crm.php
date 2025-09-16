<?php
if (!defined('ABSPATH')) exit;

add_action('admin_menu', function(){
  add_menu_page('Berrywalk Feedback','Berrywalk Feedback','manage_options','bwf_crm','bwf_crm_page','dashicons-feedback',26);
});

function bwf_crm_page(){
  $all = get_option('bwf_feedbacks',[]);
  echo '<div class="wrap"><h1>피드백 CRM</h1>';
  if(!$all){ echo '<p>피드백 데이터가 없습니다.</p>'; return; }

  echo '<table class="widefat"><thead><tr>
    <th>시각</th><th>대표ID</th><th>작성자</th><th>답변</th></tr></thead><tbody>';

  foreach($all as $r){
    echo '<tr>';
    echo '<td>'.esc_html($r['t']).'</td>';
    echo '<td>'.intval($r['rep']).'</td>';
    echo '<td>'.intval($r['user']).'</td>';
    echo '<td><details><summary>보기</summary><pre>'.esc_html(print_r($r['answers'],true)).'</pre></details></td>';
    echo '</tr>';
  }
  echo '</tbody></table></div>';
}
