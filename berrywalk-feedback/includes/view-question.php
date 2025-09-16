<?php if (!defined('ABSPATH')) exit;
add_shortcode('bw_view_question', function(){
  if (!is_user_logged_in()) return '<p>로그인 후 이용해주세요.</p>';
  $uid = get_current_user_id();
  $qid = sanitize_text_field($_GET['qid'] ?? '');

  $now  = get_user_meta($uid,'bwf_questions', true);
  $hist = get_user_meta($uid,'bwf_questions_history', true); if (!is_array($hist)) $hist=[];
  $items = [];
  if (is_array($now) && !empty($now)) $items[]=$now;
  $items = array_merge($items, $hist);

  // qid로 찾고, 없으면 최신본
  $found = null;
  if ($qid){
    foreach($items as $it){ if (!empty($it['_id']) && $it['_id']===$qid){ $found=$it; break; } }
  }
  if (!$found && !empty($items)) $found = $items[0];
  if (!$found) return '<p>표시할 항목이 없습니다.</p>';

  wp_enqueue_style('bwf-forms');
  ob_start(); ?>
  <div class="bwf-form">
    <h3>대표 질문 상세(읽기 전용)</h3>
    <?php
    $F = [
      'problem'=>'1. 현재 비즈니스에서 가장 큰 고민은 무엇인가요?',
      'value'=>'2. 우리 서비스가 고객의 ‘어떤 문제’를 해결하나요?',
      'ideal_customer'=>'3. 이 서비스를 ‘누가’ 이용해야 하나요? 왜 우리를 선택하나요?',
      'q1'=>'4-1. 맞춤 질문', 'q2'=>'4-2. 맞춤 질문', 'q3'=>'4-3. 맞춤 질문',
      'one_question'=>'5. 1:1 한 가지', 'competitors'=>'6. 경쟁/차별'
    ];
    foreach($F as $k=>$lab){
      echo '<label>'.$lab.'</label>';
      if($k==='one_question'){
        echo '<input type="text" value="'.esc_attr($found[$k]??'').'" readonly>';
      }else{
        echo '<textarea readonly>'.esc_textarea($found[$k]??'').'</textarea>';
      }
    } ?>
  </div>
  <?php return ob_get_clean();
});
