<?php
if (!defined('ABSPATH')) exit;

add_shortcode('bw_view_question', function(){
  $qid = sanitize_text_field($_GET['qid'] ?? '');
  $uid = get_current_user_id();
  if (!$qid) return '<p>잘못된 요청입니다.</p>';
  if (!$uid) return '<p>로그인 후 이용해주세요.</p>';

  // 내 최신본 + 히스토리에서 qid 찾기
  $now  = get_user_meta($uid,'bwf_questions', true);
  $hist = get_user_meta($uid,'bwf_questions_history', true); if (!is_array($hist)) $hist=[];
  $all  = [];
  if (is_array($now) && !empty($now)) $all[]=$now;
  $all = array_merge($all, $hist);

  $found = null;
  foreach($all as $it){ if (!empty($it['_id']) && $it['_id']===$qid){ $found=$it; break; } }
  if (!$found) return '<p>해당 항목을 찾을 수 없습니다.</p>';

  wp_enqueue_style('bwf-forms');

  ob_start(); ?>
  <div class="bwf-form">
    <div class="bwf-topwrap"><div class="bwf-top-title">대표 질문 상세(읽기 전용)</div></div>

    <?php
      $F = [
        'problem'=>'1. 현재 비즈니스에서 가장 큰 고민은 무엇인가요?',
        'value'=>'2. 우리 서비스가 고객의 ‘어떤 문제’를 해결하나요?',
        'ideal_customer'=>'3. 이 서비스를 ‘누가’ 이용해야 하나요? 왜 우리를 선택하나요?',
        'q1'=>'4-1. 맞춤 질문',
        'q2'=>'4-2. 맞춤 질문',
        'q3'=>'4-3. 맞춤 질문',
        'one_question'=>'5. 타겟 고객 1:1로 단 한 가지를 묻는다면?',
        'competitors'=>'6. 경쟁사와의 차별점은?'
      ];
      foreach($F as $k=>$lab){
        echo '<div class="bwf-col-full" style="margin:10px 0 14px">';
        echo '<label>'.$lab.'</label>';
        if ($k==='one_question'){
          echo '<input type="text" value="'.esc_attr($found[$k]??'').'" readonly>';
        } else {
          echo '<textarea readonly>'.esc_textarea($found[$k]??'').'</textarea>';
        }
        echo '</div>';
      }
    ?>
  </div>
  <?php
  return ob_get_clean();
});
