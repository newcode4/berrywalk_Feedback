<?php
if (!defined('ABSPATH')) exit;

define('BWF_FEEDBACK_PAGE_SLUG','customer-feedback');

add_shortcode('bw_owner_form', function(){
  if (!is_user_logged_in()) return '<div class="bwf-form"><p>로그인 후 이용해주세요.</p></div>';

  $uid   = get_current_user_id();
  $saved = get_user_meta($uid,'bwf_questions', true) ?: [];
  $S = function($k){ return isset($_POST[$k]) ? $_POST[$k] : ($saved[$k] ?? ''); };

  if (isset($_POST['bwf_save_questions']) && wp_verify_nonce($_POST['bwf_nonce'],'bwf_owner_form')) {
    $data = [
      'problem'        => sanitize_textarea_field($_POST['problem'] ?? ''),
      'value'          => sanitize_textarea_field($_POST['value'] ?? ''),
      'ideal_customer' => sanitize_textarea_field($_POST['ideal_customer'] ?? ''),
      // ask3: 글자수 제한 없이 required만 유지
      'q1'             => sanitize_textarea_field($_POST['q1'] ?? ''),
      'q2'             => sanitize_textarea_field($_POST['q2'] ?? ''),
      'q3'             => sanitize_textarea_field($_POST['q3'] ?? ''),
      'one_question'   => sanitize_text_field($_POST['one_question'] ?? ''),
      'competitors'    => sanitize_textarea_field($_POST['competitors'] ?? ''),
      '_saved_at'      => current_time('mysql'),
      '_id'            => uniqid('q_', true),
    ];
    update_user_meta($uid,'bwf_questions',$data);
    $hist = get_user_meta($uid,'bwf_questions_history', true); if (!is_array($hist)) $hist = [];
    $hist[] = $data;
    update_user_meta($uid,'bwf_questions_history',$hist);
  }

  wp_enqueue_style('bwf-forms');

  ob_start(); ?>
  <div class="bwf-form">
    <h3>대표님 핵심 질문지</h3>
    <p class="bwf-hint">사업의 본질을 파악하고 고객에게 정말 묻고 싶은 질문을 구체화합니다. 각 문항은 <strong>최소 50자</strong>입니다. <em>(단, “고객에게 물어보고 싶은 3가지”는 글자수 제한 없음)</em></p>

    <form method="post" class="bwf-grid bwf-onecol" novalidate>
      <?php wp_nonce_field('bwf_owner_form','bwf_nonce'); ?>

      <div class="bwf-sticky bwf-col-full">
        <div class="bwf-topcount">작성 <span class="done">0</span>/<span class="total">0</span>문항</div>
        <div id="bwf-progress"><div class="bar"></div><span class="label"></span></div>
      </div>

      <!-- ① -->
      <label>1. 현재 비즈니스에서 가장 큰 고민은 무엇인가요? <span class="bwf-required">*</span></label>
      <textarea name="problem" required data-minlength="50"></textarea>
      <div class="bwf-helper"><span class="bwf-counter">0</span> / 50자</div>

      <!-- ② -->
      <label>2. 우리 서비스가 고객의 ‘어떤 문제’를 해결하나요? <span class="bwf-required">*</span></label>
      <textarea name="value" required data-minlength="50"></textarea>
      <div class="bwf-helper"><span class="bwf-counter">0</span> / 50자</div>

      <!-- ③ -->
      <label>3. 이 서비스를 ‘누가’ 이용해야 하나요? 왜 우리를 선택하나요? <span class="bwf-required">*</span></label>
      <textarea name="ideal_customer" required data-minlength="50"></textarea>
      <div class="bwf-helper"><span class="bwf-counter">0</span> / 50자</div>

      <!-- ④ ask3: 글자수 제한 없음(= data-minlength 제거), 모두 비어있지 않으면 1문항으로 카운트 -->
      <h3 class="bwf-col-full">4. 고객에게 물어보고 싶은 3가지</h3>

      <label>질문 1 <span class="bwf-required">*</span></label>
      <textarea name="q1" data-group="ask3" required rows="4" placeholder="예: 우리 서비스를 알게 된 경로는 무엇이었나요?"></textarea>
      <div class="bwf-helper"><span class="bwf-counter">0</span></div>

      <label>질문 2 <span class="bwf-required">*</span></label>
      <textarea name="q2" data-group="ask3" required rows="4" placeholder="예: 결심 포인트는 무엇이었나요?"></textarea>
      <div class="bwf-helper"><span class="bwf-counter">0</span></div>

      <label>질문 3 <span class="bwf-required">*</span></label>
      <textarea name="q3" data-group="ask3" required rows="4" placeholder="예: 사용 중 가장 불편했던 점은?"></textarea>
      <div class="bwf-helper"><span class="bwf-counter">0</span></div>

      <!-- ⑤ -->
      <label>5. 타겟 고객 1:1로 단 한 가지를 묻는다면? <span class="bwf-required">*</span></label>
      <input type="text" name="one_question" data-minlength="50" required placeholder="예: 우리의 어떤 점이 당신 문제를 가장 잘 해결했나요?">
      <div class="bwf-helper"><span class="bwf-counter">0</span> / 50자</div>

      <!-- ⑥ -->
      <label>6. 경쟁사와의 차별점은? <span class="bwf-required">*</span></label>
      <textarea name="competitors" data-minlength="50" required rows="4" placeholder="예: A는 싸지만 품질 낮음… 우리는 적정 가격에 높은 만족도…"></textarea>
      <div class="bwf-helper"><span class="bwf-counter">0</span> / 50자</div>

      <div class="bwf-col-full bwf-actions">
        <button type="submit" class="bwf-btn" name="bwf_save_questions">저장</button>
      </div>

      <script>
      document.addEventListener('DOMContentLoaded', function(){
        const form   = document.currentScript.closest('form');
        const sticky = form.querySelector('.bwf-sticky');
        const progress = sticky.querySelector('#bwf-progress .bar');
        const label    = sticky.querySelector('#bwf-progress .label');
        const doneEl   = sticky.querySelector('.bwf-topcount .done');
        const totEl    = sticky.querySelector('.bwf-topcount .total');

        const fields = Array.from(form.querySelectorAll('textarea[data-minlength], input[data-minlength], textarea[data-group="ask3"]'));
        const groups = { ask3: Array.from(form.querySelectorAll('textarea[data-group="ask3"]')) };

        // 총 문항: 개별(데이터 최소길이 있는 것) + ask3(그룹 1)
        const individual = fields.filter(el => el.hasAttribute('data-minlength') && !el.hasAttribute('data-group'));
        const total = individual.length + 1; // ask3 = 1문항
        totEl.textContent = total;

        function setCounter(el, len, min){
          const helper = el.nextElementSibling && el.nextElementSibling.classList.contains('bwf-helper') ? el.nextElementSibling : null;
          if (!helper) return;
          const c = helper.querySelector('.bwf-counter');
          if (c) c.textContent = String(len);
          helper.classList.toggle('ok', min ? (len >= min) : (len > 0));
        }

        function validate(){
          let ok = 0;

          // 개별(최소 글자수)
          individual.forEach(el=>{
            const min = parseInt(el.getAttribute('data-minlength')||'0',10);
            const len = (el.value||'').trim().length;
            setCounter(el, len, min);
            el.setCustomValidity(len>=min ? '' : (min+'자 이상 입력해주세요'));
            if (len>=min) ok++;
          });

          // 그룹 ask3: 각 칸이 "비어있지 않으면" 통과 (글자수 제한 없음)
          const filled = groups.ask3.every(el => (el.value||'').trim().length > 0);
          groups.ask3.forEach(el=>{
            const len = (el.value||'').trim().length;
            setCounter(el, len, 0);
            el.setCustomValidity(len>0 ? '' : '필수 입력입니다.');
          });
          if (filled) ok++;

          const pct = Math.round((ok/total)*100);
          progress.style.width = pct+'%';
          label.textContent    = pct+'%';
          doneEl.textContent   = ok;
        }

        fields.forEach(el=>el.addEventListener('input', validate));
        validate();
      });
      </script>
    </form>
  </div>
  <?php return ob_get_clean();
});
