<?php
if (!defined('ABSPATH')) exit;

add_action('acf/init', function(){
  if (!function_exists('acf_add_local_field_group')) return;

  acf_add_local_field_group([
    'key'=>'group_bwos_owner_v5','title'=>'대표 질문지',
    'fields'=>[
      ['key'=>'f_problem','label'=>'1) 지금, 우리 사업의 가장 큰 고민은 무엇인가요?','name'=>'_acf_problem','type'=>'textarea','required'=>1,'rows'=>8,'instructions'=>'최소 200자 권장'],
      ['key'=>'f_value','label'=>'2) 우리 서비스의 핵심 가치는 무엇인가요?','name'=>'_acf_value','type'=>'textarea','required'=>1,'rows'=>6],
      ['key'=>'f_whom','label'=>'3) 누가 이용해야 하나요? 왜 우리인가요?','name'=>'_acf_ideal_customer','type'=>'textarea','required'=>1,'rows'=>6],
      ['key'=>'f_ask1','label'=>'4-1) 고객에게 묻고 싶은 질문 #1','name'=>'_acf_cq1','type'=>'text'],
      ['key'=>'f_ask2','label'=>'4-2) 고객에게 묻고 싶은 질문 #2','name'=>'_acf_cq2','type'=>'text'],
      ['key'=>'f_ask3','label'=>'4-3) 고객에게 묻고 싶은 질문 #3','name'=>'_acf_cq3','type'=>'text'],
      ['key'=>'f_comp','label'=>'5) 경쟁사 대비 차별점','name'=>'_acf_competitors','type'=>'textarea','required'=>1,'rows'=>6],
    ],
    'location'=>[[['param'=>'post_type','operator'=>'==','value'=>'bwf_owner_answer']]],
    'position'=>'normal','style'=>'default','active'=>true,
  ]);
});


/** 200자 필드 서버 검증 */
add_filter('acf/validate_value', function($valid, $value, $field){
  if ($valid !== true) return $valid;
  $min200 = ['problem','core_value','target_customer','differentiation','sales_blockers','current_channels','growth_goal','next_action'];
  if (in_array($field['name'] ?? '', $min200, true)) {
    if (mb_strlen(trim((string)$value)) < 200) return '최소 200자 이상 작성해 주세요.';
  }
  return $valid;
}, 10, 3);
/* ------- ACF <-> bwf_answers 동기화 ------- */
function bwos_acf_meta_keys(){ // ACF 메타키(name) 매핑
  return [
    '_acf_problem'       => ['id'=>'problem'],
    '_acf_value'         => ['id'=>'value'],
    '_acf_ideal_customer'=> ['id'=>'ideal_customer'],
    '_acf_cq1'           => ['id'=>'ask3','sub'=>'q1'],
    '_acf_cq2'           => ['id'=>'ask3','sub'=>'q2'],
    '_acf_cq3'           => ['id'=>'ask3','sub'=>'q3'],
    '_acf_competitors'   => ['id'=>'competitors'],
  ];
}

/* 편집 화면 로드시 ACF 메타 채워넣기(관리자에서 비어 보이던 문제 해결) */
add_action('load-post.php', function(){
  $pid = isset($_GET['post']) ? (int)$_GET['post'] : 0;
  if(!$pid) return;
  $p = get_post($pid);
  if(!$p || $p->post_type!=='bwf_owner_answer') return;

  $ans = (array)get_post_meta($pid,'bwf_answers',true);
  foreach(bwos_acf_meta_keys() as $meta=>$m){
    $val = '';
    if(empty($m['sub'])) $val = (string)($ans[$m['id']] ?? '');
    else                 $val = (string)($ans[$m['id']][$m['sub']] ?? '');
    update_post_meta($pid, $meta, $val);
  }
});

/* 저장 시 ACF 값으로 bwf_answers 재생성(양방향 동기화) */
add_action('save_post_bwf_owner_answer', function($post_id){
  if(defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
  $ans = [];
  foreach(bwos_acf_meta_keys() as $meta=>$m){
    $v = (string)get_post_meta($post_id,$meta,true);
    if(empty($m['sub'])) $ans[$m['id']] = $v;
    else {
      if(empty($ans[$m['id']])) $ans[$m['id']] = [];
      $ans[$m['id']][$m['sub']] = $v;
    }
  }
  if($ans) update_post_meta($post_id,'bwf_answers',$ans);
}, 20);
