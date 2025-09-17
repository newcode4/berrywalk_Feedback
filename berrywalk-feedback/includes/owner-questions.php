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
  $id = intval($atts['id'] ?? 0);
  if (!$id) return '';
  $post = get_post($id);
  if (!$post || $post->post_type!=='bwf_owner_answer') return '';

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
  $nonce = wp_create_nonce('bwf_owner_save');
  $err  = isset($_POST['bwf_q_error']) ? sanitize_text_field($_POST['bwf_q_error']) : '';
  $old  = isset($_POST['q']) ? (array) $_POST['q'] : []; // 검증 실패 후 재표시용

  ob_start(); ?>
  <form class="bwf-form bwf-owner" method="post" id="bwfOwnerForm" novalidate>
    <input type="hidden" name="bwf_owner_action" value="save">
    <input type="hidden" name="bwf_owner_nonce" value="<?php echo esc_attr($nonce); ?>">
    <input type="hidden" name="bwf_submission" value="<?php echo esc_attr( wp_generate_uuid4() ); ?>">


    <h2 class="bwf-title"><?php echo esc_html($cfg['title'] ?? '대표님 핵심 질문지'); ?></h2>
    <p class="bwf-help">
      <?php
        $intro = (string)($cfg['intro_html'] ?? '');
        $intro = str_replace('{MIN}', $min, $intro);
        echo wp_kses_post($intro);
      ?>
    </p>

    <div class="bwf-topwrap">
      <div class="bwf-top-title"><b>작성</b> <span class="done">0</span>/<span class="total"><?php
        $t = 0; foreach(($cfg['questions'] ?? []) as $q){ $t += intval($q['count_as'] ?? 1); } echo intval($t);
      ?></span>문항</div>
      <div id="bwf-progress"><div class="bar" style="width:0%"></div></div>
    </div>

    <?php foreach(($cfg['questions'] ?? []) as $q): 
      $qid = esc_attr($q['id']);
      $req = !isset($q['required']) || $q['required'];     // 기본 필수
      $ml  = $q['minlength'] ?? $min;
      $type = $q['type'] ?? 'textarea';
      $desc = $q['desc_html'] ?? '';
      $ex   = $q['examples_html'] ?? '';
    ?>
      <div class="bwf-field">
        <label><?php echo esc_html($q['label']); ?><?php if($req): ?> <span class="bwf-required">*</span><?php endif; ?></label>
        <?php if($desc): ?><div class="bwf-desc"><?php echo wp_kses_post($desc); ?></div><?php endif; ?>
        <?php if($ex): ?><div class="bwf-examples"><?php echo wp_kses_post($ex); ?></div><?php endif; ?>

        <?php if($type==='group'): ?>
          <?php foreach(($q['sub'] ?? []) as $sub):
            $sid = esc_attr($sub['id']); $pl = esc_attr($sub['placeholder'] ?? '');
            $sreq = !isset($sub['required']) || $sub['required'];
            $val = isset($old[$qid][$sid]) ? (string)$old[$qid][$sid] : '';
          ?>
            <div class="bwf-sub">
              <div style="font-weight:600;margin:8px 0 6px;"><?php echo esc_html($sub['label'] ?? ''); ?><?php if($sreq): ?> <span class="bwf-required">*</span><?php endif; ?></div>
              <textarea name="q[<?php echo $qid; ?>][<?php echo $sid; ?>]" rows="3" <?php echo $sreq?'required':''; ?> placeholder="<?php echo $pl; ?>"><?php echo esc_textarea($val); ?></textarea>
              <div class="bwf-helper"><span class="bwf-counter">0</span><?php /* 소문항은 글자수 제한 없음 */ ?></div>
            </div>
          <?php endforeach; ?>
        <?php else:
          $val = isset($old[$qid]) ? (string)$old[$qid] : '';
        ?>
          <textarea name="q[<?php echo $qid; ?>]" rows="5" <?php echo $req?'required':''; ?> minlength="<?php echo intval($ml); ?>" placeholder="내용을 입력하세요."><?php echo esc_textarea($val); ?></textarea>
          <div class="bwf-helper">
            <span class="bwf-guide"><?php echo intval($ml); ?>자 이상</span>
            <span class="bwf-counter">0 / <?php echo intval($ml); ?>자</span>
          </div>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>

    <div class="bwf-actions">
      <button type="submit" class="bwf-btn" id="bwfOwnerSubmit">저장하기</button>
    </div>

    <!-- 에러 모달 -->
    <div class="bwf-modal" id="bwf-modal" aria-hidden="true">
      <div class="bwf-modal__card">
        <h3>입력이 필요한 항목이 있어요</h3>
        <p class="bwf-modal__msg"><?php echo $err ? esc_html($err) : '필수 항목을 확인해 주세요.'; ?></p>
        <div class="bwf-modal__actions">
          <button type="button" class="bwf-btn" id="bwf-modal-ok">확인</button>
        </div>
      </div>
    </div>

    <script>
    (function(){
      const wrap  = document.querySelector('.bwf-owner');
      const bar   = wrap.querySelector('#bwf-progress .bar');
      const done  = wrap.querySelector('.done');
      const total = parseInt(wrap.querySelector('.total').textContent,10) || 0;
      const btn   = document.getElementById('bwfOwnerSubmit');
      const modal = document.getElementById('bwf-modal');
      const okBtn    = document.getElementById('bwf-modal-ok');
      const form  = document.getElementById('bwfOwnerForm');
      

       function openModal(msg){
            modal.querySelector('.bwf-modal__msg').textContent = msg;
            modal.classList.add('is-open'); modal.setAttribute('aria-hidden','false');
        }
        okBtn.addEventListener('click', ()=>{ modal.classList.remove('is-open'); modal.setAttribute('aria-hidden','true'); });

        function markError(el){
            el.classList.add('bwf-error');
            el.closest('.bwf-field')?.classList.add('bwf-error');
        }
        function clearErrors(){
            form.querySelectorAll('.bwf-error').forEach(x=>x.classList.remove('bwf-error'));
        }

      // 글자수 카운터 + 진행률
      function recalc(){
        // 질문형(글자수 있는 애들만 카운트)
        const groups = Array.from(wrap.querySelectorAll('.bwf-field'));
        let okCount = 0;
        groups.forEach(g=>{
          const ta = g.querySelector('textarea[minlength]');
          if(!ta) return; // 소문항 그룹 제외
          const need = parseInt(ta.getAttribute('minlength') || '0', 10);
          const now  = (ta.value || '').trim().length;
          const counter = g.querySelector('.bwf-counter');
          if(counter) counter.textContent = `${now} / ${need}자`;
          if(need === 0 ? (now>0) : (now >= need)) okCount++;
        });
        done.textContent = okCount;
        const pct = Math.min(100, Math.round((okCount/Math.max(1,total))*100));
        bar.style.width = pct + '%';
      }
      wrap.addEventListener('input', recalc);
      recalc();

      // 제출 시 검증(필수/최소 글자)
      form.addEventListener('submit', function(e){
            clearErrors();
            let firstBad = null, msgs = [];

            // 일반 문항(필수+minlength)
            form.querySelectorAll('textarea[required]').forEach(el=>{
            const v = (el.value||'').trim();
            const ml = parseInt(el.getAttribute('minlength')||'0',10);
            if (!v) { markError(el); msgs.push('필수 항목을 입력해주세요.'); if(!firstBad) firstBad=el; return; }
            if (ml > 0 && v.length < ml) { markError(el); msgs.push(`최소 ${ml}자 이상 작성해주세요.`); if(!firstBad) firstBad=el; }
            });

            // 그룹 문항(모든 소문항 필수이지만 글자수 제한은 없음)
            form.querySelectorAll('.bwf-sub textarea[required]').forEach(el=>{
            const v=(el.value||'').trim();
            if(!v){ markError(el); if(!firstBad) firstBad=el; msgs.push('소문항을 모두 입력해주세요.'); }
            });

            if (msgs.length){
            e.preventDefault();
            btn.disabled = false; btn.removeAttribute('aria-busy');
            openModal('필수 항목을 확인해주세요.');
            firstBad?.scrollIntoView({behavior:'smooth', block:'center'});
            firstBad?.focus();
            } else {
            // 더블클릭 방지
            btn.disabled = true; btn.setAttribute('aria-busy','true');
            }
        });
        })();
        </script>
  </form>
  <?php
  return ob_get_clean();
});

/* --------------------------------------------------------------------------
   4) 저장 처리: 서버 검증 + 중복제출 방지 + 개별 포스트 생성
-------------------------------------------------------------------------- */
add_action('init', function(){
  if (!isset($_POST['bwf_owner_action']) || $_POST['bwf_owner_action']!=='save') return;

  if (!is_user_logged_in()) return;
  if (!wp_verify_nonce($_POST['bwf_owner_nonce'] ?? '', 'bwf_owner_save')) return;

  // 중복 제출 락(사용자+nonce 기준, 30초)
  $uid = get_current_user_id();
  $nonce = sanitize_text_field($_POST['bwf_owner_nonce']);
  $lock_key = "bwf_owner_lock_{$uid}_".md5($nonce);
  if (get_transient($lock_key)) return;
  set_transient($lock_key, 1, 30);

  $cfg = bwf_owner_get_config();
  $min = intval($cfg['min_length'] ?? 200);
  $q   = isset($_POST['q']) ? (array) $_POST['q'] : [];

  // 서버 검증
  $errors = [];
  foreach(($cfg['questions'] ?? []) as $qq){
    $id = $qq['id']; $type = $qq['type'] ?? 'textarea';
    $required = !isset($qq['required']) || $qq['required'];
    if ($type === 'group'){
      foreach(($qq['sub'] ?? []) as $sub){
        $sid = $sub['id']; $sreq = !isset($sub['required']) || $sub['required'];
        $val = trim((string)($q[$id][$sid] ?? ''));
        if ($sreq && $val===''){ $errors[] = "{$qq['label']} - {$sub['label']}을(를) 입력해 주세요."; }
      }
    } else {
      $val = trim((string)($q[$id] ?? ''));
      $ml  = intval($qq['minlength'] ?? $min);
      if ($required && $val===''){ $errors[] = "{$qq['label']}을(를) 입력해 주세요."; }
      if ($ml > 0 && mb_strlen($val) < $ml){ $errors[] = "{$qq['label']}은(는) 최소 {$ml}자 이상 작성해 주세요."; }
    }
  }

  if (!empty($errors)){
    $_POST['bwf_q_error'] = implode(' / ', $errors); // 폼에서 모달로 표시
    return;
  }

  // 저장(항상 새 글 생성 → 기존 글 덮어쓰기/일괄변경 방지)
  $post_id = wp_insert_post([
    'post_type'   => 'bwf_owner_answer',
    'post_title'  => '대표 질문 - '. current_time('Y-m-d H:i:s'),
    'post_status' => 'publish',
    'post_author' => $uid,
  ], true);
  if (is_wp_error($post_id)){
    $_POST['bwf_q_error'] = $post_id->get_error_message();
    return;
  }
  // JSON 그대로 저장
  update_post_meta($post_id, 'bwf_answers', bwf_sanitize_deep($q));

  // 완료 → 리다이렉트
  wp_redirect(BWF_OWNER_AFTER_SAVE_URL);
  exit;
});

/* 배열 value 전체 sanitize */
function bwf_sanitize_deep($val){
  if (is_array($val)){ foreach($val as $k=>$v){ $val[$k] = bwf_sanitize_deep($v); } return $val; }
  return sanitize_textarea_field((string)$val);
}

add_action('template_redirect', function(){
  if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
  if (($_POST['bwf_owner_action'] ?? '') !== 'save') return;

  if (!is_user_logged_in()) {
    wp_safe_redirect( home_url('/login/') ); exit;
  }

  $uid   = get_current_user_id();
  $nonce = $_POST['bwf_owner_nonce'] ?? '';
  if (!wp_verify_nonce($nonce, 'bwf_owner_save')) {
    wp_safe_redirect( add_query_arg('err','nonce', wp_get_referer() ?: home_url('/') ) ); exit;
  }

  // 2중 저장 방지: (유저ID+제출토큰)로 5분 락
  $sub  = sanitize_text_field($_POST['bwf_submission'] ?? '');
  $lock = "bwf_owner_lock_{$uid}_{$sub}";
  if (empty($sub) || get_transient($lock)) {
    wp_safe_redirect( add_query_arg('err','dup', wp_get_referer() ?: home_url('/') ) ); exit;
  }
  set_transient($lock, 1, 5*MINUTE_IN_SECONDS);

  // 검증
  $cfg   = bwf_owner_get_config();
  $min   = intval($cfg['min_length'] ?? 200);
  $qs    = (array)($cfg['questions'] ?? []);
  $in    = (array)($_POST['q'] ?? []);
  $errs  = [];
  $clean = [];

  foreach ($qs as $q) {
    $id   = $q['id'] ?? '';
    $type = $q['type'] ?? 'textarea';
    $req  = !isset($q['required']) || $q['required'];
    $ml   = isset($q['minlength']) && $q['minlength'] !== '' ? intval($q['minlength']) : $min;

    if ($type === 'group') {
      $subs = (array)($q['sub'] ?? []);
      $clean[$id] = [];
      foreach ($subs as $s) {
        $sid  = $s['id'] ?? ''; if (!$sid) continue;
        $sreq = !isset($s['required']) || $s['required'];
        $val  = trim((string)($in[$id][$sid] ?? ''));
        $clean[$id][$sid] = sanitize_textarea_field($val);
        if ($sreq && $val === '') {
          $errs[] = "{$q['label']} - {$s['label']} 입력 필요";
        }
      }
    } else {
      $val = trim((string)($in[$id] ?? ''));
      $clean[$id] = sanitize_textarea_field($val);
      if ($req) {
        if ($val === '') { $errs[] = "{$q['label']} 입력 필요"; }
        elseif (mb_strlen($val) < $ml) { $errs[] = "{$q['label']} 최소 {$ml}자 이상"; }
      }
    }
  }

  if ($errs) {
    // 폼 재표시를 위해 POST 값 유지
    $_POST['bwf_q_error'] = implode(' / ', $errs);
    return; // shortcode가 같은 요청 내에서 에러를 표시
  }

  // 저장: CPT 단건
  $title   = '대표 질문 ' . wp_date('Y-m-d H:i:s'); // 서울시간
  $post_id = wp_insert_post([
    'post_type'    => 'bwf_owner_answer',
    'post_title'   => $title,
    'post_status'  => 'publish',
    'post_author'  => $uid,
  ], true);

  if (!is_wp_error($post_id)) {
    update_post_meta($post_id, 'bwf_answers', $clean);
    update_post_meta($post_id, 'bwf_submission', $sub);
    wp_safe_redirect( add_query_arg(['saved'=>1,'id'=>$post_id], BWF_OWNER_AFTER_SAVE_URL) ); exit;
  }

  wp_safe_redirect( add_query_arg('err','save', wp_get_referer() ?: home_url('/') ) ); exit;
});
