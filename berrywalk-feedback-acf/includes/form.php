<?php
/* /includes/form.php (핵심 변경만) */
if (!defined('ABSPATH')) exit;

/** 공용 URL (중복 선언 방지) */
if (!function_exists('bwf_owner_view_url'))  { function bwf_owner_view_url(){ return home_url('/my-question-view/'); } }
if (!function_exists('bwf_owner_edit_url'))  { function bwf_owner_edit_url(){ return home_url('/owner-edit/'); } }
if (!function_exists('bwf_owner_list_url'))  { function bwf_owner_list_url(){ return home_url('/my-questions/'); } }


function bwf_owner_cfg(){
  return [
    'title'=>'대표님 핵심 질문지',
    'min'=>200,
    'intro'=>'사업의 본질을 파악하고 고객에게 정말 묻고 싶은 질문을 구체화합니다. 각 문항은 최소 <strong>{MIN}</strong>자입니다. <em>(단, <strong>“고객에게 물어보고 싶은 3가지”</strong>는 글자수 제한 없음)</em>',
    'qs'=>[
      [
        'id'=>'problem','label'=>'1. 지금, 우리 사업의 가장 큰 고민은 무엇인가요?','required'=>true,'min'=>200,
        'desc'=>'성장이 정체되었다고 느낀 **구체적 상황**을 한두 문장으로. 숫자/행동 기반이면 좋아요.',
        'ex'=>'<ul><li>“클릭은 많은데 체류 30초 미만 이탈이 많아요.”</li><li>“재구매율이 낮아 신규 유입 의존도가 큽니다.”</li></ul>'
      ],
      [
        'id'=>'value','label'=>'2. 우리 서비스는 고객의 어떤 문제를 해결하나요?','required'=>true,'min'=>200,
        'desc'=>'고객의 **전/후 변화**를 한 문장으로 요약.',
        'ex'=>'<ol><li>“반복 서류작업을 5분 자동화 → 본업 집중”</li><li>“바쁜 직장인도 집밥처럼 건강한 한 끼 확보”</li></ol>'
      ],
      [
        'id'=>'ideal_customer','label'=>'3. 누가 이용해야 하나요? 왜 우리여야 하나요?','required'=>true,'min'=>200,
        'desc'=>'핵심 타깃의 특징 + 우리를 선택할 **결정적 이유**.',
        'ex'=>'<p>“시간/비용이 부족한 20대 대학생 → 15분 홈트 영상으로 꾸준함 확보.”</p>'
      ],
      [
        'id'=>'ask3','type'=>'group','label'=>'4. 고객에게 물어보고 싶은 3가지','required'=>true,
        'desc'=>'실제 경험 기반으로 **구체적인 질문 3개**를 작성.',
        'ex'=>'<p><strong>예)</strong> 첫인상/결심포인트/아쉬운점</p>',
        'sub'=>[
          ['id'=>'q1','label'=>'질문 1','ph'=>'예: 어떻게 알게 되었나요?','required'=>true],
          ['id'=>'q2','label'=>'질문 2','ph'=>'예: 결심 포인트는 무엇이었나요?','required'=>true],
          ['id'=>'q3','label'=>'질문 3','ph'=>'예: 가장 아쉬웠던 점은?','required'=>true],
        ]
      ],
      [
        'id'=>'competitors','label'=>'5. 경쟁사와의 차별점은?','required'=>true,'min'=>200,
        'desc'=>'주요 경쟁사 1~2곳과 비교해 강/약점 정리.',
        'ex'=>'<p>“A는 저렴하지만 CS 느림 / B는 비싸지만 품질 우수 → 우리는 적정가+빠른 CS”</p>'
      ],
    ]
  ];
}

function bwf_owner_view_url(){ return home_url('/my-question-view/'); }
function bwf_owner_edit_url(){ return home_url('/owner-edit/'); }
function bwf_owner_list_url(){ return home_url('/my-questions/'); }

function bwf_render_owner_form($args=[]){
  $cfg=bwf_owner_cfg(); $min=(int)$cfg['min']; $mode=$args['mode']??'create';
  $post=$args['post']??null; $saved=$args['saved']??[]; $errs=$args['errs']??[];
  if($mode==='edit' && $post){ $existing=(array)get_post_meta($post->ID,'bwf_answers',true); $saved=$saved ?: $existing; }
  wp_enqueue_style('bwf-forms'); wp_enqueue_script('bwos-form');

  ob_start(); ?>
  <form id="bwfOwnerForm" class="bwf-form bwos-form" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" novalidate>
    <input type="hidden" name="action" value="bwf_owner_save">
    <?php wp_nonce_field('bwf_owner_save','bwf_owner_nonce'); ?>
    <input type="hidden" name="post_id" value="<?php echo $post ? (int)$post->ID : 0; ?>">

    <div class="bwos-progress"><div class="bar"><span></span></div><div class="status">작성 0/0 (0%)</div></div>

    <h2 class="bwf-title"><?php echo esc_html($cfg['title']); ?></h2>
    <p class="bwf-help"><?php echo str_replace('{MIN}', $min, $cfg['intro']); ?></p>

    <?php foreach($cfg['qs'] as $q): $type=$q['type']??'textarea'; $id=$q['id']; ?>
      <div class="bwf-field bwos-field bwos-required" data-min="<?php echo (int)($q['min']??$min); ?>">
        <label><?php echo esc_html($q['label']); ?><?php if(!empty($q['required'])): ?><span class="bwf-required">*</span><?php endif; ?></label>

        <?php if(!empty($q['desc'])): ?><div class="bwf-desc"><?php echo wp_kses_post($q['desc']); ?></div><?php endif; ?>
        <?php if(!empty($q['ex'])):   ?><div class="bwf-examples"><?php echo wp_kses_post($q['ex']); ?></div><?php endif; ?>

        <?php if($type==='group'): ?>
          <?php foreach(($q['sub']??[]) as $sub): $sid=$sub['id']; $v=$saved[$id][$sid]??''; ?>
            <div class="bwf-sub">
              <div class="bwf-help" style="margin:6px 0 6px;"><?php echo esc_html($sub['label']); ?></div>
              <textarea name="q[<?php echo esc_attr($id); ?>][<?php echo esc_attr($sid); ?>]" rows="4" placeholder="<?php echo esc_attr($sub['ph']??''); ?>"><?php echo esc_textarea($v); ?></textarea>
            </div>
          <?php endforeach; ?>
        <?php else: $v=$saved[$id]??''; ?>
          <textarea name="q[<?php echo esc_attr($id); ?>]" rows="6"><?php echo esc_textarea($v); ?></textarea>
          <small class="bwos-count">0/<?php echo (int)($q['min']??$min); ?></small>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>

    <div class="bwf-actions">
      <button type="submit" class="bwf-btn bwos-submit"><?php echo $mode==='edit'?'수정 저장':'저장'; ?></button>
      <!-- 마이페이지 버튼 제거 요청 반영 -->
    </div>
  </form>
  <?php return ob_get_clean();
}

add_shortcode('bwf_owner_questions', function(){
  wp_enqueue_style('bwf-forms');

  if (!is_user_logged_in()){
    wp_enqueue_style('bwf-forms');
    $signup = esc_url('https://berrywalk.co.kr/bwf-owner-signup/');
    $login  = esc_url( wp_login_url( home_url('/owner-questions/') ) );
    return '<div class="bwos-wrap"><div class="bwf-need-login"><p>대표님 핵심 질문지를 작성하려면 로그인이 필요합니다.</p><div class="bwf-login-actions"><a class="bwf-btn" href="'.$login.'">로그인하러 가기</a><a class="bwf-btn-secondary" href="'.$signup.'">회원가입하러 가기</a></div></div></div>';
  }
  $uid=get_current_user_id();
  $old=get_transient('bwf_owner_old_'.$uid)?:[]; delete_transient('bwf_owner_old_'.$uid);
  $err=get_transient('bwf_owner_err_'.$uid)?:[]; delete_transient('bwf_owner_err_'.$uid);
  return bwf_render_owner_form(['mode'=>'create','saved'=>$old,'errs'=>$err]);
});
add_shortcode('bw_owner_form', fn()=>do_shortcode('[bwf_owner_questions]'));
add_shortcode('bw_owner_edit', function($atts){
  if (!is_user_logged_in()) return '<div class="bwf-form">로그인이 필요합니다.</div>';
  $id=(int)($atts['id']??($_GET['id']??0));
  $post=get_post($id);
  if(!$post || $post->post_type!=='bwf_owner_answer') return '<div class="bwf-form">데이터가 없습니다.</div>';
  if((int)$post->post_author!==get_current_user_id() && !current_user_can('edit_post',$id)) return '<div class="bwf-form">권한이 없습니다.</div>';
  $uid=get_current_user_id();
  $old=get_transient('bwf_owner_old_'.$uid)?:[]; delete_transient('bwf_owner_old_'.$uid);
  $err=get_transient('bwf_owner_err_'.$uid)?:[]; delete_transient('bwf_owner_err_'.$uid);
  return bwf_render_owner_form(['mode'=>'edit','post'=>$post,'saved'=>$old,'errs'=>$err]);
});
