<?php
if (!defined('ABSPATH')) exit;

/**
 * 대표님 핵심 질문지 (관리자 설정 기반)
 * 저장 키: problem, value, ideal_customer, q1, q2, q3, competitors
 * one_question 제거됨
 */

add_shortcode('bw_owner_form', function(){
  if (!is_user_logged_in()) return '<div class="bwf-form"><p>로그인 후 이용해주세요.</p></div>';

  $cfg = get_option('bwf_owner_config', function_exists('bwf_owner_default_config') ? bwf_owner_default_config() : []);
  $min = intval($cfg['min_length'] ?? 200);
  $title = $cfg['title'] ?? '대표님 핵심 질문지';
  $intro = str_replace('{MIN}', $min, $cfg['intro'] ?? '');

  $uid   = get_current_user_id();
  $saved = get_user_meta($uid,'bwf_questions', true) ?: [];
  $S = function($k){ return isset($_POST[$k]) ? wp_unslash($_POST[$k]) : ($saved[$k] ?? ''); };

  if (isset($_POST['bwf_save_questions']) && isset($_POST['bwf_nonce']) && wp_verify_nonce($_POST['bwf_nonce'],'bwf_owner_form')) {
    $data = [
      'problem'        => sanitize_textarea_field($_POST['problem'] ?? ''),
      'value'          => sanitize_textarea_field($_POST['value'] ?? ''),
      'ideal_customer' => sanitize_textarea_field($_POST['ideal_customer'] ?? ''),
      'q1'             => sanitize_textarea_field($_POST['q1'] ?? ''),
      'q2'             => sanitize_textarea_field($_POST['q2'] ?? ''),
      'q3'             => sanitize_textarea_field($_POST['q3'] ?? ''),
      'competitors'    => sanitize_textarea_field($_POST['competitors'] ?? ''),
      '_saved_at'      => current_time('mysql'),
      '_id'            => $saved['_id'] ?? uniqid('q_', true),
    ];
    update_user_meta($uid,'bwf_questions',$data);

    $hist = get_user_meta($uid,'bwf_questions_history', true); if(!is_array($hist)) $hist=[];
    $hist[] = $data; update_user_meta($uid,'bwf_questions_history',$hist);
  }

  wp_enqueue_style('bwf-forms');

  $Q = [
    'problem' => $cfg['q_problem'] ?? [],
    'value'   => $cfg['q_value'] ?? [],
    'ideal'   => $cfg['q_ideal'] ?? [],
    'ask3'    => $cfg['q_ask3'] ?? [],
    'compet'  => $cfg['q_competitors'] ?? [],
  ];

  ob_start(); ?>
  <form method="post" class="bwf-form bwf-owner" novalidate>
    <?php wp_nonce_field('bwf_owner_form','bwf_nonce'); ?>

    <h2 class="bwf-title"><?php echo esc_html($title); ?></h2>
    <p class="bwf-help"><?php echo wp_kses_post($intro); ?></p>

    <!-- 진행 현황 -->
    <div class="bwf-topwrap">
      <div class="bwf-top-title">작성 <span class="done">0</span>/<span class="total">5</span>문항</div>
      <div id="bwf-progress"><div class="bar"></div><span class="label"></span></div>
    </div>

    <!-- 1 -->
    <div class="bwf-field">
      <label><?php echo esc_html($Q['problem']['label'] ?? '1. 질문'); ?> <span class="bwf-required">*</span></label>
      <?php if(!empty($Q['problem']['desc'])) echo '<p class="bwf-desc">'.wp_kses_post($Q['problem']['desc']).'</p>'; ?>
      <?php if(!empty($Q['problem']['examples'])) echo '<div class="bwf-examples">'.wp_kses_post($Q['problem']['examples']).'</div>'; ?>
      <textarea name="problem" required data-minlength="<?php echo $min; ?>"><?php echo esc_textarea($S('problem')); ?></textarea>
      <div class="bwf-helper"><span class="bwf-counter">0</span> / <?php echo $min; ?>자</div>
    </div>

    <!-- 2 -->
    <div class="bwf-field">
      <label><?php echo esc_html($Q['value']['label'] ?? '2. 질문'); ?> <span class="bwf-required">*</span></label>
      <?php if(!empty($Q['value']['desc'])) echo '<p class="bwf-desc">'.wp_kses_post($Q['value']['desc']).'</p>'; ?>
      <?php if(!empty($Q['value']['examples'])) echo '<div class="bwf-examples">'.wp_kses_post($Q['value']['examples']).'</div>'; ?>
      <textarea name="value" required data-minlength="<?php echo $min; ?>"><?php echo esc_textarea($S('value')); ?></textarea>
      <div class="bwf-helper"><span class="bwf-counter">0</span> / <?php echo $min; ?>자</div>
    </div>

    <!-- 3 -->
    <div class="bwf-field">
      <label><?php echo esc_html($Q['ideal']['label'] ?? '3. 질문'); ?> <span class="bwf-required">*</span></label>
      <?php if(!empty($Q['ideal']['desc'])) echo '<p class="bwf-desc">'.wp_kses_post($Q['ideal']['desc']).'</p>'; ?>
      <?php if(!empty($Q['ideal']['examples'])) echo '<div class="bwf-examples">'.wp_kses_post($Q['ideal']['examples']).'</div>'; ?>
      <textarea name="ideal_customer" required data-minlength="<?php echo $min; ?>"><?php echo esc_textarea($S('ideal_customer')); ?></textarea>
      <div class="bwf-helper"><span class="bwf-counter">0</span> / <?php echo $min; ?>자</div>
    </div>

    <!-- 4: ask3 -->
    <div class="bwf-field">
      <h3 class="bwf-h3"><?php echo esc_html($Q['ask3']['label'] ?? '4. 고객에게 물어볼 3가지'); ?></h3>
      <?php if(!empty($Q['ask3']['desc'])) echo '<p class="bwf-desc">'.wp_kses_post($Q['ask3']['desc']).'</p>'; ?>
      <?php if(!empty($Q['ask3']['examples'])) echo '<div class="bwf-examples">'.wp_kses_post($Q['ask3']['examples']).'</div>'; ?>

      <label>질문 1 <span class="bwf-required">*</span></label>
      <textarea name="q1" data-group="ask3" required rows="4" placeholder="예: 우리 서비스를 알게 된 경로는 무엇이었나요?"><?php echo esc_textarea($S('q1')); ?></textarea>
      <div class="bwf-helper"><span class="bwf-counter">0</span></div>

      <label>질문 2 <span class="bwf-required">*</span></label>
      <textarea name="q2" data-group="ask3" required rows="4" placeholder="예: 결심 포인트는 무엇이었나요?"><?php echo esc_textarea($S('q2')); ?></textarea>
      <div class="bwf-helper"><span class="bwf-counter">0</span></div>

      <label>질문 3 <span class="bwf-required">*</span></label>
      <textarea name="q3" data-group="ask3" required rows="4" placeholder="예: 사용 중 가장 불편했던 점은?"><?php echo esc_textarea($S('q3')); ?></textarea>
      <div class="bwf-helper"><span class="bwf-counter">0</span></div>
    </div>

    <!-- 5 -->
    <div class="bwf-field">
      <label><?php echo esc_html($Q['compet']['label'] ?? '5. 질문'); ?> <span class="bwf-required">*</span></label>
      <?php if(!empty($Q['compet']['desc'])) echo '<p class="bwf-desc">'.wp_kses_post($Q['compet']['desc']).'</p>'; ?>
      <?php if(!empty($Q['compet']['examples'])) echo '<div class="bwf-examples">'.wp_kses_post($Q['compet']['examples']).'</div>'; ?>
      <textarea name="competitors" required data-minlength="<?php echo $min; ?>" rows="4"><?php echo esc_textarea($S('competitors')); ?></textarea>
      <div class="bwf-helper"><span class="bwf-counter">0</span> / <?php echo $min; ?>자</div>
    </div>

    <div class="bwf-actions">
      <button type="submit" class="bwf-btn" name="bwf_save_questions">저장</button>
    </div>

    <script>
    (function(){
      const f = document.currentScript.closest('form');
      const min = <?php echo $min; ?>;

      // 진행 카운트: 개별 4(문항1,2,3,5) + ask3 그룹 1
      const total = 5;
      const doneEl = f.querySelector('.bwf-topwrap .done');
      const totEl  = f.querySelector('.bwf-topwrap .total');
      const bar    = f.querySelector('#bwf-progress .bar');
      const label  = f.querySelector('#bwf-progress .label');
      totEl.textContent = total;

      const individual = ['problem','value','ideal_customer','competitors'].map(n=>f.querySelector('[name="'+n+'"]'));
      const ask3 = ['q1','q2','q3'].map(n=>f.querySelector('[name="'+n+'"]'));

      function setCounter(el, minlen){
        const helper = el.nextElementSibling && el.nextElementSibling.classList.contains('bwf-helper') ? el.nextElementSibling : null;
        const len = (el.value||'').trim().length;
        if(helper){
          const c = helper.querySelector('.bwf-counter');
          if (c) c.textContent = String(len);
          helper.classList.toggle('ok', minlen ? len>=minlen : !!len);
        }
        if (minlen){
          el.setCustomValidity(len>=minlen ? '' : (minlen + '자 이상 입력해주세요'));
        } else {
          el.setCustomValidity(len>0 ? '' : '필수 입력입니다.');
        }
      }

      function calcDone(){
        let ok = 0;
        individual.forEach(el => { if(!el) return; const len=(el.value||'').trim().length; if(len>=min) ok++; });
        const aok = ask3.every(el => (el.value||'').trim().length>0);
        if (aok) ok++;
        return ok;
      }

      function render(){
        const done = calcDone();
        const pct = Math.round(done/total*100);
        doneEl.textContent = done;
        bar.style.width = pct+'%';
        label.textContent = pct+'%';
      }

      [...individual, ...ask3].forEach(el=>{
        if(!el) return;
        setCounter(el, el.hasAttribute('data-minlength') ? min : 0);
        el.addEventListener('input', ()=>{ setCounter(el, el.hasAttribute('data-minlength') ? min : 0); render(); });
      });
      render();
    })();
    </script>
  </form>
  <?php return ob_get_clean();
});
