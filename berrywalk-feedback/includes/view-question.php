<?php
if (!defined('ABSPATH')) exit;

add_shortcode('bw_view_question', function($atts){
  if(!is_user_logged_in()) return '<div class="bwf-form"><p>로그인 후 이용해주세요.</p></div>';
  $uid = get_current_user_id();
  $qid = sanitize_text_field($_GET['qid'] ?? '');

  // 현재본 + 히스토리에서 해당 qid 찾기
  $cands = [];
  $now  = get_user_meta($uid,'bwf_questions', true);
  $hist = get_user_meta($uid,'bwf_questions_history', true); if(!is_array($hist)) $hist=[];
  if(is_array($now) && $now) $cands[]=$now; $cands=array_merge($cands,$hist);

  $found = null;
  foreach($cands as $it){ if(($it['_id'] ?? '') === $qid){ $found = $it; break; } }
  if(!$found) return '<div class="bwf-form"><p>해당 데이터가 없습니다.</p></div>';

  ob_start();
  echo '<div class="bwf-form"><h2>저장된 대표 질문</h2><div class="grid-1">';

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
      echo '<input type="text" readonly value="'.esc_attr($found[$k]??'').'">';
    } else {
      echo '<textarea readonly>'.esc_textarea($found[$k]??'').'</textarea>';
    }
    echo '</div>';
  }
  echo '</div></div>';
  return ob_get_clean();
});
