<?php
if (!defined('ABSPATH')) exit;

add_shortcode('bw_my_questions', function(){
  if (!is_user_logged_in()) return '<p>로그인 후 이용해주세요.</p>';
  $uid   = get_current_user_id();
  $now   = get_user_meta($uid,'bwf_questions', true);
  $hist  = get_user_meta($uid,'bwf_questions_history', true);
  if (!is_array($hist)) $hist = [];
  // 최신본을 맨 앞에
  $items = [];
  if (is_array($now) && !empty($now)) $items[] = $now;
  $items = array_merge($items, $hist);

  wp_enqueue_style('bwf-forms');

  ob_start();
  echo '<div class="wrap bwf-form"><h2>내 질문 저장본</h2>';
  if (empty($items)) { echo '<p>아직 저장한 질문이 없습니다.</p></div>'; return ob_get_clean(); }

  echo '<table class="widefat striped"><thead><tr><th>저장시각</th><th>요약</th><th>전체</th></tr></thead><tbody>';
    foreach($items as $it){
    $t = isset($it['_saved_at']) ? $it['_saved_at'] : '';
    $flat = wp_strip_all_tags(implode(' ', array_map('strval', $it)));
    $sum  = esc_html(mb_substr($flat,0,120)).(mb_strlen($flat)>120?'…':'');
    echo '<tr>';
    echo '<td>'.esc_html($t).'</td>';
    echo '<td>'.$sum.'</td>';
    $view_page = get_page_by_path('my-question-view');
    $view_url  = $view_page ? get_permalink($view_page) : home_url('/my-question-view/');
    $goto = add_query_arg(['qid'=>$it['_id'] ?? ''], $view_url);
    echo '<td><a class="button" href="'.esc_url($goto).'">보기</a></td>';
    echo '</tr>';
    }
  echo '</tbody></table>';
  echo '<p style="margin-top:12px;"><a class="button button-primary" href="'.esc_url(home_url('/owner-questions/')).'">새 질문 작성</a></p>';
  echo '</div>';
  return ob_get_clean();
});
