<?php
if (!defined('ABSPATH')) exit;

/**
 * 대표님 핵심 질문지 (1열 레이아웃 / 예시·설명 포함 / 글자수 카운트 / 진행률)
 * 필드 키: problem, value, ideal_customer, q1, q2, q3, one_question, competitors
 */

add_shortcode('bw_owner_form', function () {
  if (!is_user_logged_in()) return '<div class="bwf-form"><p>로그인 후 이용해주세요.</p></div>';

  $uid   = get_current_user_id();
  $saved = get_user_meta($uid, 'bwf_questions', true) ?: [];
  $S = function($k){ return isset($_POST[$k]) ? wp_unslash($_POST[$k]) : ($saved[$k] ?? ''); };

  // 저장
  if (isset($_POST['bwf_save_questions']) && isset($_POST['bwf_nonce']) && wp_verify_nonce($_POST['bwf_nonce'],'bwf_owner_form')) {
    $data = [
      'problem'        => sanitize_textarea_field($_POST['problem'] ?? ''),
      'value'          => sanitize_textarea_field($_POST['value'] ?? ''),
      'ideal_customer' => sanitize_textarea_field($_POST['ideal_customer'] ?? ''),
      'q1'             => sanitize_textarea_field($_POST['q1'] ?? ''),
      'q2'             => sanitize_textarea_field($_POST['q2'] ?? ''),
      'q3'             => sanitize_textarea_field($_POST['q3'] ?? ''),
      'one_question'   => sanitize_text_field($_POST['one_question'] ?? ''),
      'competitors'    => sanitize_textarea_field($_POST['competitors'] ?? ''),
      '_saved_at'      => current_time('mysql'),
      '_id'            => $saved['_id'] ?? uniqid('q_', true),
    ];
    update_user_meta($uid, 'bwf_questions', $data);

    // 이력도 적재
    $hist = get_user_meta($uid, 'bwf_questions_history', true);
    if (!is_array($hist)) $hist = [];
    $hist[] = $data;
    update_user_meta($uid, 'bwf_questions_history', $hist);
  }

  // CSS 로드
  wp_enqueue_style('bwf-forms');

  ob_start(); ?>
  <form method="post" class="bwf-form bwf-owner" novalidate>
    <?php wp_nonce_field('bwf_owner_form','bwf_nonce'); ?>

    <h2 class="bwf-title">대표님 핵심 질문지</h2>
    <p class="bwf-help">
      사업의 본질을 파악하고 고객에게 정말 묻고 싶은 질문을 구체화합니다. 각 문항은 <strong>최소 50자</strong>입니다.
      <em>(단, <strong>“고객에게 물어보고 싶은 3가지”</strong>는 글자수 제한 없음)</em>
    </p>

    <!-- 상단 진행 현황 -->
    <div class="bwf-topwrap">
      <div class="bwf-top-title">작성 <span class="done">0</span>/<span class="total">6</span>문항</div>
      <div id="bwf-progress"><div class="bar"></div><span class="label"></span></div>
    </div>

    <!-- 1 -->
    <div class="bwf-field">
      <label>1. 지금, 우리 사업의 가장 큰 고민은 무엇인가요? <span class="bwf-required">*</span></label>
      <p class="bwf-desc">설명: 현재 가장 답답하거나 성장이 정체되었다고 느끼는 구체적인 상황을 한두 문장으로 설명해 주세요.
        예를 들어, “광고는 많이 했는데, 실제 구매로 이어지는 비율이 너무 낮습니다” 또는 “고객 문의는 많은데, 대부분 가격만 물어보고 결제하지 않아요”
        와 같이 <strong>숫자</strong>나 <strong>행동</strong>을 기반으로 고민을 말씀해 주시면 좋습니다.</p>
      <div class="bwf-examples">
        <strong>예시:</strong>
        <ul>
          <li>“최근 인스타그램 광고 효율이 너무 안 나와요. 클릭은 많은데, 저희 웹사이트에 1분도 머물지 않고 나가는 사람이 많아요.”</li>
          <li>“기존 고객들의 재구매율이 낮아서, 항상 새로운 고객을 찾아야 하는 부담이 큽니다. 단골 고객을 어떻게 만들어야 할지 모르겠습니다.”</li>
        </ul>
      </div>
      <textarea name="problem" data-minlength="50" required><?php echo esc_textarea($S('problem')); ?></textarea>
      <div class="bwf-helper"><span class="bwf-counter">0</span> / 50자</div>
    </div>

    <!-- 2 -->
    <div class="bwf-field">
      <label>2. 우리 서비스는 고객의 ‘어떤 문제’를 해결해주고 있나요? <span class="bwf-required">*</span></label>
      <p class="bwf-desc">설명: 고객이 우리 서비스를 만나기 전에는 어떤 어려움을 겪었고, 이용한 후에는 어떤 변화가 있었는지 떠올려 보세요.
        이 서비스를 통해 고객이 얻게 되는 <strong>가장 중요한 가치</strong>를 한 문장으로 압축해 주세요.</p>
      <div class="bwf-examples">
        <strong>예시:</strong>
        <ol>
          <li>“번거로운 서류 작업을 단 5분 만에 자동화하여, 자영업자들이 본업에만 집중할 수 있도록 돕습니다.”</li>
          <li>“매번 배달 음식에 지쳐있던 바쁜 직장인에게 집에서 갓 만든 것 같은 건강한 한 끼를 제공합니다.”</li>
        </ol>
      </div>
      <textarea name="value" data-minlength="50" required><?php echo esc_textarea($S('value')); ?></textarea>
      <div class="bwf-helper"><span class="bwf-counter">0</span> / 50자</div>
    </div>

    <!-- 3 -->
    <div class="bwf-field">
      <label>3. 우리 서비스를 ‘누가’ 이용해야 하나요? 왜 우리를 선택하나요? <span class="bwf-required">*</span></label>
      <p class="bwf-desc">설명: 우리 서비스가 가장 완벽하게 해결해 줄 수 있는 사람은 누구인가요? 그들의 특징(나이, 직업, 관심사)을 구체적으로 설명하고,
        그들이 여러 선택지 중 우리를 골라야 하는 <strong>결정적인 이유</strong>를 한 가지 생각해 보세요.</p>
      <div class="bwf-examples">
        <strong>예시:</strong>
        <p>“운동은 하고 싶지만 헬스장에 갈 시간과 돈이 부족한 <strong>집순이 20대 대학생</strong>입니다. 왜냐하면 저렴한 구독료로 집에서 15분 만에 끝낼 수 있는 홈트레이닝 영상을 제공하기 때문입니다.”</p>
      </div>
      <textarea name="ideal_customer" data-minlength="50" required><?php echo esc_textarea($S('ideal_customer')); ?></textarea>
      <div class="bwf-helper"><span class="bwf-counter">0</span> / 50자</div>
    </div>

    <!-- 4: ask3 (글자수 제한 X) -->
    <div class="bwf-field">
      <h3>4. 고객에게 물어보고 싶은 3가지</h3>
      <p class="bwf-desc">
        이 질문은 대표님께서 고객에게 직접적으로 가장 궁금해하는 점을 파악하여, 사업 성장을 위한 핵심 인사이트를 얻는 과정입니다.
        아래 예시를 참고하여, 현재 가장 해결하고 싶은 고민에 대한 질문 3가지를 직접 작성해 주세요.
        핵심은 고객이 실제로 <strong>경험한 내용</strong>에 대해 질문하는 것입니다. 피드백 제공자가 질문에 답하기 위해 별도의 시간이나 노력을 들이지 않고,
        직접 경험한 내용을 바탕으로 솔직하게 답변할 수 있도록 <strong>구체적인 상황</strong>을 제시하는 것이 중요합니다.
      </p>
      <div class="bwf-examples">
        <strong>피드백 대상이 ‘상품’일 경우</strong>
        <ul>
          <li>“저희가 보내드린 상품을 처음 받으셨을 때 어떤 느낌이셨나요? (포장, 디자인, 첫 사용 경험 등)”</li>
          <li>“상품을 사용하시면서 가장 만족스러웠던 부분은 무엇이었나요? 반대로, ‘이 점은 조금 아쉽다’고 생각했던 부분이 있다면 무엇인가요?”</li>
        </ul>
        <strong>피드백 대상이 ‘서비스’일 경우</strong>
        <ul>
          <li>“저희 서비스의 [무료체험 기간]을 이용하셨을 때, 가장 좋았던 점과 아쉬웠던 점은 무엇이었나요?”</li>
        </ul>
        <strong>고민별 질문 예시 (참고)</strong>
        <ul>
          <li>광고 효율 낮음 → “최근 저희가 진행한 <strong>[인스타그램 광고]</strong>를 보셨을 때, 어떤 점이 가장 인상적이었나요? 그리고 저희 웹사이트에 방문하셨다가 구매를 망설이게 한 이유가 있다면 무엇이었나요?”</li>
          <li>재구매율 낮음 → “저희 서비스를 이용하신 후, 다른 서비스를 다시 이용하셨다면 그 이유는 무엇이었나요? 저희 서비스가 제공하지 못했던 가치는 무엇이었나요?”</li>
          <li>차별점 불분명 → “저희와 비슷한 다른 서비스(예: A사)를 이용해 보셨다면, 그 서비스에 비해 저희 서비스의 어떤 부분이 가장 좋거나 아쉬웠나요?”</li>
        </ul>
      </div>

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
      <label>5. 타겟 고객 1:1로 단 한 가지를 묻는다면? <span class="bwf-required">*</span></label>
      <input type="text" name="one_question" data-minlength="50" required placeholder="예: 우리의 어떤 점이 당신 문제를 가장 잘 해결했나요?" value="<?php echo esc_attr($S('one_question')); ?>">
      <div class="bwf-helper"><span class="bwf-counter">0</span> / 50자</div>
    </div>

    <!-- 6 -->
    <div class="bwf-field">
      <label>6. 현재 경쟁사는 어디이며, 그들과의 차별점은 무엇이라고 생각하시나요? <span class="bwf-required">*</span></label>
      <p class="bwf-desc">설명: 시장에 존재하는 주요 경쟁사 1~2곳을 언급하고, 그들과 비교했을 때 우리 서비스의 강점과 약점은 무엇이라고 생각하시나요?</p>
      <div class="bwf-examples">
        <strong>예시:</strong>
        <p>“A사는 가격이 저렴하지만 품질이 낮고, B사는 품질은 좋지만 가격이 너무 비쌉니다. 저희는 A와 B 사이에서 적정한 가격에 높은 만족도를 제공합니다.”</p>
      </div>
      <textarea name="competitors" data-minlength="50" required rows="4"><?php echo esc_textarea($S('competitors')); ?></textarea>
      <div class="bwf-helper"><span class="bwf-counter">0</span> / 50자</div>
    </div>

    <div class="bwf-actions">
      <button type="submit" class="bwf-btn" name="bwf_save_questions">저장</button>
    </div>

    <script>
    (function(){
      const form = document.currentScript.closest('form');

      // 진행률 계산 규칙
      const minFields = ['problem','value','ideal_customer','one_question','competitors']; // 5개(각 50자 이상)
      const ask3 = ['q1','q2','q3'];                                                      // 1개 그룹(세 칸 모두 값 존재해야 카운트)

      const total = 6; // 5(개별) + 1(ask3)
      const doneEl = form.querySelector('.bwf-topwrap .done');
      const totEl  = form.querySelector('.bwf-topwrap .total');
      const bar    = form.querySelector('#bwf-progress .bar');
      const label  = form.querySelector('#bwf-progress .label');
      totEl.textContent = total;

      // 헬퍼 텍스트 업데이트
      function setCounter(el, min){
        const helper = el.nextElementSibling && el.nextElementSibling.classList.contains('bwf-helper') ? el.nextElementSibling : null;
        if (!helper) return;
        const c = helper.querySelector('.bwf-counter');
        const len = (el.value || '').trim().length;
        if (c) c.textContent = String(len);
        if (min){
          helper.classList.toggle('ok', len >= min);
          el.setCustomValidity(len >= min ? '' : (min + '자 이상 입력해주세요'));
        } else {
          // ask3: 글자수 제한 없음, 값이 있으면 ok(색 변화는 주지 않음)
          el.setCustomValidity(len > 0 ? '' : '필수 입력입니다.');
        }
      }

      function currentDone(){
        let ok = 0;

        // 개별 5개(각 50자)
        minFields.forEach(name=>{
          const el = form.querySelector('[name="'+name+'"]');
          const min = (name === 'one_question') ? 50 : 50;
          if (el){
            const len = (el.value || '').trim().length;
            if (len >= min) ok++;
          }
        });

        // ask3
        const aok = ask3.every(name=>{
          const el = form.querySelector('[name="'+name+'"]');
          return el && (el.value||'').trim().length > 0;
        });
        if (aok) ok++;

        return ok;
      }

      function renderProgress(){
        const ok = currentDone();
        const pct = Math.round((ok/total)*100);
        doneEl.textContent = ok;
        bar.style.width = pct + '%';
        label.textContent = pct + '%';
      }

      // 모든 필드에 이벤트 바인딩
      const inputs = Array.from(form.querySelectorAll('textarea, input[type="text"]'));
      inputs.forEach(el=>{
        const min = el.hasAttribute('data-minlength') ? parseInt(el.getAttribute('data-minlength'),10) : 0;
        setCounter(el, min);
        el.addEventListener('input', ()=>{ setCounter(el, min); renderProgress(); });
      });

      // 초기 렌더
      renderProgress();
    })();
    </script>
  </form>
  <?php
  return ob_get_clean();
});
