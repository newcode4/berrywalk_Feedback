<?php
if (!defined('ABSPATH')) exit;

add_action('init', function(){
  register_post_type('bwf_owner_answer', [
    'label'=>'대표 질문지','labels'=>['name'=>'대표 질문지','add_new_item'=>'새 대표 질문 생성','edit_item'=>'대표 질문 편집'],
    'public'=>false,'exclude_from_search'=>true,'publicly_queryable'=>false,'show_ui'=>true,'show_in_menu'=>true,
    'menu_icon'=>'dashicons-editor-help','supports'=>['author','custom-fields'],'map_meta_cap'=>true,
  ]);
});

add_filter('manage_bwf_owner_answer_posts_columns', function($c){
  return ['cb'=>$c['cb'],'title'=>'제목','author'=>'작성자','created'=>'저장 시각'];
});
add_action('manage_bwf_owner_answer_posts_custom_column', function($col,$post_id){
  if($col==='created'){
    $ts = get_post_timestamp($post_id,'date');
    echo esc_html( wp_date('Y-m-d H:i', $ts) );
  }
}, 10, 2);
