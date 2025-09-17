<?php
if (!defined('ABSPATH')) exit;

/**
 * 대표 질문지: 개별 저장(CPT) + 필수 검증 + 중복 방지 + 보기 템플릿
 * - CPT: bwf_owner_answer (비공개, 관리자에서만 목록 확인)
 * - Shortcode: [bwf_owner_questions]  (작성 폼)
 * - Shortcode: [bwf_owner_view id="123"] (보기 화면)
 * - 저장 성공 후 리다이렉트 URL: BWF_OWNER_AFTER_SAVE_URL 상수로 제어
 */

if (!defined('BWF_OWNER_AFTER_SAVE_URL')) {
  define('BWF_OWNER_AFTER_SAVE_URL', home_url('/')); // ← 저장 후 보낼 URL(원하면 바꿔)
}

/* --------------------------------------------------------------------------
   0) 질문지 구성 로드 (관리자 빌더 옵션 사용, 없으면 기본값)
-------------------------------------------------------------------------- */
function bwf_owner_default_config_array(){
  return [
    "title" => "대표님 핵심 질문지",
    "intro_html" => "사업의 본질을 파악하고 고객에게 정말 묻고 싶은 질문을 구체화합니다. 각 문항은 최소 <strong>{MIN}</strong>자입니다. <em>(단, <strong>“고객에게 물어보고 싶은 3가지”</strong>는 글자수 제한 없음)</em>",
    "min_length" => 200,
    "questions" => [
      [
        "id"=>"problem","type"=>"textarea","label"=>"1. 지금, 우리 사업의 가장 큰 고민은 무엇인가요?","required"=>true,"minlength"=>200,"count_as"=>1,
        "desc_html"=>"설명: 현재 가장 답답하거나 성장이 정체되었다고 느끼는 구체적인 상황을 한두 문장으로 설명해 주세요. 예: “광고는 많이 했는데…” 등 숫자/행동 기반.",
        "examples_html"=>"<ul><li>“최근 인스타그램 광고 효율이 너무 안 나와요. 클릭은 많은데, 저희 웹사이트에 1분도 머물지 않고 나가는 사람이 많아요.”</li><li>“기존 고객들의 재구매율이 낮아서, 항상 새로운 고객을 찾아야 하는 부담이 큽니다.”</li></ul>"
      ],
      [
        "id"=>"value","type"=>"textarea","label"=>"2. 우리 서비스는 고객의 ‘어떤 문제’를 해결해주고 있나요?","required"=>true,"minlength"=>200,"count_as"=>1,
        "desc_html"=>"설명: 고객의 전/후 변화를 떠올려 가장 중요한 <strong>가치</strong>를 한 문장으로.",
        "examples_html"=>"<ol><li>“서류 작업을 5분 만에 자동화 → 본업 집중.”</li><li>“바쁜 직장인에게 집밥 같은 건강한 한 끼 제공.”</li></ol>"
      ],
      [
        "id"=>"ideal_customer","type"=>"textarea","label"=>"3. 우리 서비스를 ‘누가’ 이용해야 하나요? 왜 우리를 선택하나요?","required"=>true,"minlength"=>200,"count_as"=>1,
        "desc_html"=>"설명: 가장 잘 맞는 사람의 특징 + 우리를 고를 <strong>결정적 이유</strong>.",
        "examples_html"=>"<p>“시간·비용이 부족한 <strong>20대 대학생</strong> → 15분 홈트 영상으로 해결.”</p>"
      ],
      [
        "id"=>"ask3","type"=>"group","label"=>"4. 고객에게 물어보고 싶은 3가지","required"=>true,"count_as"=>1,
        "desc_html"=>"대표님이 고객에게 직접 묻고 싶은 핵심 질문 3가지를 작성하세요. 경험한 내용을 바탕으로 구체적으로.",
        "examples_html"=>"<p><strong>상품</strong></p><ul><li>“처음 받았을 때 어떤 느낌이었나요? (포장/디자인/첫 경험)”</li><li>“가장 만족/아쉬웠던 점은?”</li></ul><p><strong>서비스</strong></p><ul><li>“무료체험 때 최고의 포인트/아쉬웠던 점?”</li></ul>",
        "sub" => [
          ["id"=>"q1","label"=>"질문 1","placeholder"=>"예: 우리 서비스를 알게 된 경로는 무엇이었나요?","required"=>true,"minlength"=>0],
          ["id"=>"q2","label"=>"질문 2","placeholder"=>"예: 결심 포인트는 무엇이었나요?","required"=>true,"minlength"=>0],
          ["id"=>"q3","label"=>"질문 3","placeholder"=>"예: 사용 중 가장 불편했던 점은?","required"=>true,"minlength"=>0]
        ]
      ],
      [
        "id"=>"competitors","type"=>"textarea","label"=>"5. 현재 경쟁사는 어디이며, 그들과의 차별점은 무엇이라고 생각하시나요?","required"=>true,"minlength"=>200,"count_as"=>1,
        "desc_html"=>"설명: 주요 경쟁사 1~2곳과 비교해 강·약점을 정리.",
        "examples_html"=>"<p>“A는 저렴하지만 품질이 낮고, B는 비싸지만 품질이 높다 → 우리는 <strong>적정 가격 + 높은 만족</strong>.”</p>"
      ]
    ]
  ];
}
function bwf_owner_get_config(){
  $raw = get_option('bwf_owner_builder_json', '');
  if (!$raw) return bwf_owner_default_config_array();
  $arr = json_decode($raw, true);
  if (!is_array($arr) || empty($arr['questions'])) return bwf_owner_default_config_array();
  return $arr;
}



/** 저장 후 알림 (메일/텔레그램) */
function bwf_notify_owner_save($post_id, $answers){
  // 관리자/작성자 메일
  $admin = get_option('admin_email');
  $url   = add_query_arg(['id'=>$post_id], home_url('/my-question-view/'));
  $sub   = '대표 질문지 저장 완료: #'.$post_id;
  $body  = "새 저장본이 생성되었습니다.\n보기: {$url}\n\n요약:\n".mb_substr(wp_strip_all_tags(print_r($answers,true)),0,800);
  @wp_mail($admin, $sub, $body);

  // 텔레그램(환경에서 상수 정의 시에만 전송)
  if (defined('BWF_TG_BOT') && defined('BWF_TG_CHAT')) {
    wp_remote_post("https://api.telegram.org/bot".BWF_TG_BOT."/sendMessage",[
      'timeout'=>5,
      'body'=>['chat_id'=>BWF_TG_CHAT,'text'=>$sub."\n".$url]
    ]);
  }
}

/* --------------------------------------------------------------------------
   1) CPT 등록: bwf_owner_answer (개별 저장)
-------------------------------------------------------------------------- */
add_action('init', function(){
  register_post_type('bwf_owner_answer', [
    'labels' => ['name'=>'대표 질문','singular_name'=>'대표 질문'],
    'public' => false,
    'show_ui' => true,
    'show_in_menu' => true,
    'menu_icon' => 'dashicons-feedback',
    'supports' => ['title','author'],
    'capability_type' => 'post',
    'map_meta_cap' => true,
  ]);
});

/* --------------------------------------------------------------------------
   2) 단건 보기: [bwf_owner_view id="123"]
   - 예시는 숨기고 질문/답변만 표시 (폼과 동일 톤)
-------------------------------------------------------------------------- */
add_shortcode('bwf_owner_view', function($atts){
  $id = intval($atts['id'] ?? ($_GET['id'] ?? 0)); // ← GET?id= 도 허용
  if (!$id) return '';
  $post = get_post($id);
  if (!$post || $post->post_type!=='bwf_owner_answer') return '';
  wp_enqueue_style('bwf-forms');


  $cfg = bwf_owner_get_config();
  $answers = (array) get_post_meta($id, 'bwf_answers', true);

  ob_start(); ?>
  <div class="bwf-form bwf-owner">
    <h2 class="bwf-title"><?php echo esc_html($cfg['title'] ?? '대표 질문 보기'); ?></h2>

    <?php foreach(($cfg['questions'] ?? []) as $q): ?>
      <div class="bwf-field">
        <label><?php echo esc_html($q['label']); ?></label>
        <?php if(($q['type'] ?? 'textarea') === 'group'): ?>
          <?php foreach(($q['sub'] ?? []) as $sub): 
            $v = trim((string)($answers[$q['id']][$sub['id']] ?? '')); ?>
            <div class="bwf-sub">
              <div style="font-weight:600;margin:8px 0 6px;"><?php echo esc_html($sub['label']); ?></div>
              <div class="bwf-card"><?php echo nl2br(esc_html($v)); ?></div>
            </div>
          <?php endforeach; ?>
        <?php else: 
          $v = trim((string)($answers[$q['id']] ?? '')); ?>
          <div class="bwf-card"><?php echo nl2br(esc_html($v)); ?></div>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
  </div>
  <?php
  return ob_get_clean();
});

/* --------------------------------------------------------------------------
   3) 작성 폼: [bwf_owner_questions]
   - 새 글은 항상 빈 값(이전 값 자동채움 없음)
   - 필수/글자수 미충족 시 저장 차단 + 모달 + 빨간 테두리 + 스크롤
   - 중복 제출 방지(트랜지언트 락 + 버튼 비활성)
-------------------------------------------------------------------------- */

add_shortcode('bwf_owner_questions', function(){
  if (!is_user_logged_in()) return '<div class="bwf-form">로그인이 필요합니다.</div>';

  $cfg = bwf_owner_get_config();
  $min = intval($cfg['min_length'] ?? 200);

  // 에러 뒤 되돌아온 값(트랜지언트)
  $uid = get_current_user_id();
  $old = get_transient('bwf_owner_old_'.$uid) ?: [];
  delete_transient('bwf_owner_old_'.$uid);
  $err = get_transient('bwf_owner_err_'.$uid) ?: [];
  delete_transient('bwf_owner_err_'.$uid);

  wp_enqueue_style('bwf-forms');
  wp_enqueue_script('bwf-owner'); // ✅ owner.js

  ob_start(); ?>
  <form id="bwfOwnerForm" method="post" action="<?php echo esc_url( admin_url('admin-post.php') ); ?>">
    <input type="hidden" name="action" value="bwf_owner_save">
    <?php wp_nonce_field('bwf_owner_save','bwf_owner_nonce'); ?>

    <div class="bwf-topwrap">
      <div class="bwf-topcount">작성 <span class="done">0</span>/<span class="total"><?php echo count($cfg['questions']); ?></span>문항</div>
      <div id="bwf-progress"><div class="bar"></div><span class="label"></span></div>
    </div>

    <h2 class="bwf-title"><?php echo esc_html($cfg['title']); ?></h2>
    <p class="bwf-intro"><?php echo str_replace('{MIN}', intval($min), $cfg['intro_html']); ?></p>

    <?php foreach($cfg['questions'] as $q): 
      $qid = $q['id']; $type = $q['type'] ?? 'textarea';
      $required = !empty($q['required']);
      $minlen = ($q['minlength']===''||$q['minlength']==null) ? $min : intval($q['minlength']);
      $has_err = !empty($err[$qid]); ?>
      <div class="bwf-field <?php echo $has_err?'bwf-error':''; ?>">
        <label><?php echo esc_html($q['label']); ?> <?php if($required): ?><span class="bwf-required">*</span><?php endif; ?></label>
        <?php if($type==='group'): ?>
          <?php foreach(($q['sub']??[]) as $sub):
            $sid = $sub['id']; $sv = $old[$qid][$sid] ?? ''; ?>
            <div class="bwf-sub <?php echo !empty($err[$qid][$sid])?'bwf-error':''; ?>">
              <div class="bwf-sub-label"><?php echo esc_html($sub['label']); ?><?php if(!empty($sub['required'])): ?><span class="bwf-required">*</span><?php endif; ?></div>
              <textarea name="q[<?php echo esc_attr($qid); ?>][<?php echo esc_attr($sid); ?>]" rows="4" 
                        data-minlength="<?php echo intval($sub['minlength']??0); ?>" data-group="ask3"
                        placeholder="<?php echo esc_attr($sub['placeholder']??''); ?>"><?php echo esc_textarea($sv); ?></textarea>
              <div class="bwf-helper"><span class="bwf-counter"></span></div>
            </div>
          <?php endforeach; ?>
        <?php else:
          $v = $old[$qid] ?? ''; ?>
          <textarea name="q[<?php echo esc_attr($qid); ?>]" rows="5" data-minlength="<?php echo intval($minlen); ?>"><?php echo esc_textarea($v); ?></textarea>
          <div class="bwf-helper"><span class="bwf-guide">최소 <?php echo intval($minlen); ?>자</span><span class="bwf-counter"></span></div>
        <?php endif; ?>

        <?php if(!empty($q['desc_html'])): ?><div class="bwf-desc"><?php echo $q['desc_html']; ?></div><?php endif; ?>
        <?php if(!empty($q['examples_html'])): ?><div class="bwf-ex"><?php echo $q['examples_html']; ?></div><?php endif; ?>
      </div>
    <?php endforeach; ?>

    <div class="bwf-actions">
      <button type="submit" class="bwf-btn">저장</button>
      <p class="bwf-hint">* 필수 항목과 최소 글자수를 채우면 저장됩니다.</p>
    </div>
  </form>
  <?php return ob_get_clean();
});

/** 저장 핸들러 */
add_action('admin_post_bwf_owner_save', function(){
  if (!is_user_logged_in()) wp_safe_redirect( home_url('/') );

  $uid = get_current_user_id();
  if (!isset($_POST['bwf_owner_nonce']) || !wp_verify_nonce($_POST['bwf_owner_nonce'],'bwf_owner_save')){
    wp_safe_redirect( wp_get_referer() ?: home_url('/') ); exit;
  }

  // 중복 제출 락(10초)
  $lock = 'bwf_owner_lock_'.$uid;
  if (get_transient($lock)) { wp_safe_redirect( home_url('/my-questions/') ); exit; }
  set_transient($lock, 1, 10);

  $cfg = bwf_owner_get_config();
  $min = intval($cfg['min_length'] ?? 200);
  $src = $_POST['q'] ?? [];
  $answers = []; $err = [];

  foreach ($cfg['questions'] as $q){
    $qid = $q['id']; $type = $q['type'] ?? 'textarea';
    $required = !empty($q['required']);
    $minlen = ($q['minlength']===''||$q['minlength']==null) ? $min : intval($q['minlength']);

    if ($type==='group'){
      $answers[$qid]=[];
      foreach (($q['sub']??[]) as $sub){
        $sid = $sub['id'];
        $val = trim((string)($src[$qid][$sid] ?? ''));
        $answers[$qid][$sid] = sanitize_textarea_field($val);
        $need = !empty($sub['required']);
        $smin = intval($sub['minlength'] ?? 0);
        if ($need && mb_strlen($val) < $smin){ $err[$qid][$sid]=true; }
      }
      // 최소 요건: required 그룹은 모든 필수 소문항 충족해야 함
      if ($required && !empty($err[$qid])) { /* 그룹 에러 표시용 */ }
    }else{
      $val = trim((string)($src[$qid] ?? ''));
      $answers[$qid] = sanitize_textarea_field($val);
      if ($required && mb_strlen($val) < $minlen){ $err[$qid]=true; }
    }
  }

  if ($err){
    set_transient('bwf_owner_old_'.$uid, $src, 60);
    set_transient('bwf_owner_err_'.$uid, $err, 60);
    delete_transient($lock);
    wp_safe_redirect( wp_get_referer() ?: home_url('/owner-questions/') ); exit;
  }

  // 개별 저장(CPT)
  $post_id = wp_insert_post([
    'post_type'=>'bwf_owner_answer',
    'post_status'=>'private',
    'post_title'=>'대표 질문지 - '.wp_date('Y-m-d H:i'),
    'post_author'=>$uid,
  ], true);

  if (!is_wp_error($post_id)){
    update_post_meta($post_id, 'bwf_answers', $answers);
    bwf_notify_owner_save($post_id, $answers);
    delete_transient($lock);
    wp_safe_redirect( add_query_arg(['id'=>$post_id,'saved'=>1], home_url('/my-question-view/')) ); exit;
  }

  delete_transient($lock);
  wp_safe_redirect( home_url('/my-questions/') ); exit;
});
add_action('admin_post_bwf_owner_save', 'bwf_owner_handle_save');
add_action('admin_post_nopriv_bwf_owner_save', 'bwf_owner_handle_save');
