<?php
if (!defined('ABSPATH')) exit;

add_shortcode('bw_view_question', function(){
  wp_enqueue_style('bwf-forms'); // 공용 스타일

  $qid = sanitize_text_field($_GET['qid'] ?? '');
  $uid = intval($_GET['uid'] ?? 0);
  if (!$qid || !$uid) return '<div class="bwf-form"><p>잘못된 접근입니다.</p></div>';

  $now  = get_user_meta($uid,'bwf_questions', true);
  $hist = get_user_meta($uid,'bwf_questions_history', true); if(!is_array($hist)) $hist=[];
  $items=[]; if(is_array($now)&&$now) $items[]=$now; $items=array_merge($items,$hist);

  $target = null;
  foreach($items as $it){ if(($it['_id'] ?? '') === $qid){ $target = $it; break; } }
  if (!$target) return '<div class="bwf-form"><p>대상을 찾을 수 없습니다.</p></div>';

  ob_start(); ?>
  <div class="bwf-view">
    <div class="bwf-card">
      <h2 class="bwf-title">대표 질문 보기</h2>
      <ul class="bwf-kv">
        <li><b>저장 시각</b><span><?=esc_html($target['_saved_at'] ?? '')?></span></li>
        <li><b>대표 ID</b><span><?=intval($uid)?></span></li>
      </ul>
    </div>

    <div class="bwf-card">
      <h3>내용</h3>
      <ul class="bwf-bullets">
        <?php
        $map = [
          'problem'=>'① 가장 큰 고민','value'=>'② 핵심 가치','ideal_customer'=>'③ 이상적 타겟',
          'q1'=>'④-1 질문','q2'=>'④-2 질문','q3'=>'④-3 질문',
          'one_question'=>'⑤ 1:1 한 가지','competitors'=>'⑥ 경쟁/차별'
        ];
        foreach($map as $k=>$title){
          if(!empty($target[$k])){
            echo '<li><b>'.$title.':</b> '.esc_html($target[$k]).'</li>';
          }
        }
        ?>
      </ul>
      <div class="bwf-actions">
        <a href="javascript:history.back()" class="bwf-btn-secondary">목록으로</a>
      </div>
    </div>
  </div>
  <?php
  return ob_get_clean();
});
