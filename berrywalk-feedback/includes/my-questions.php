<?php
add_shortcode('bw_my_questions', function(){
  if(!is_user_logged_in()) return '<div class="bwf-form"><p>로그인 후 이용해주세요.</p></div>';

    wp_enqueue_style('bwf-forms');

  $uid = get_current_user_id();
  $now  = get_user_meta($uid,'bwf_questions', true);
  $hist = get_user_meta($uid,'bwf_questions_history', true); if(!is_array($hist)) $hist=[];
  $items=[]; if(is_array($now)&&$now) $items[]=$now; $items=array_merge($items,$hist);

  ob_start();
  echo '<div class="bwf-form"><h2>내 질문 저장본</h2>';
  if(empty($items)){ echo '<p>아직 저장한 질문이 없습니다.</p></div>'; return ob_get_clean(); }

  $view_page = get_page_by_path('my-question-view');
  $view_url  = $view_page ? get_permalink($view_page) : home_url('/my-question-view/');

  echo '<ul class="bwf-list">';
  foreach($items as $it){
    $t = esc_html($it['_saved_at'] ?? '');
    $flat = wp_strip_all_tags(implode(' ', array_map('strval',$it)));
    $sum  = esc_html(mb_substr($flat,0,140)).(mb_strlen($flat)>140?'…':'');
    $goto = esc_url(add_query_arg(['qid'=>$it['_id'] ?? ''],$view_url));
    echo "<li><span class='bwf-time'>{$t}</span> <span class='bwf-sum'>{$sum}</span> <a class='button' href='{$goto}'>보기</a></li>";
  }
  echo '</ul><p style="margin-top:14px"><a class="button button-primary' .
       "' href='".esc_url(home_url('/owner-questions/'))."'>새 질문 작성</a></p></div>";
  return ob_get_clean();
});
