<?php
if (!defined('ABSPATH')) exit;

/**
 * 대표님 핵심 질문지 – 빌더(JSON) 기반 동적 폼
 * 저장 구조: 사용자 메타 bwf_questions (연결 키는 질문 id)
 *   - 단일문항: answers[id] = string
 *   - 그룹문항: answers[id][subId] = string
 */

function bwf_owner_builder_get(){
  $raw = get_option('bwf_owner_builder_json');
  if(!$raw) $raw = function_exists('bwf_owner_builder_default_json') ? bwf_owner_builder_default_json() : '';
  $cfg = json_decode($raw, true);
  if (!is_array($cfg) || empty($cfg['questions'])) {
    // 안전망
    $cfg = json_decode(bwf_owner_builder_default_json(), true);
  }
  return $cfg;
}

add_shortcode('bw_owner_form', function(){
  if (!is_user_logged_in()) return '<div class="bwf-form"><p>로그인 후 이용해주세요.</p></div>';

  $cfg   = bwf_owner_builder_get();
  $title = $cfg['title'] ?? '대표님 핵심 질문지';
  $intro = str_replace('{MIN}', intval($cfg['min_length'] ?? 200), $cfg['intro_html'] ?? '');
  $globalMin = intval($cfg['min_length'] ?? 200);

  $uid   = get_current_user_id();
  $saved = get_user_meta($uid, 'bwf_questions', true);
  if (!is_array($saved)) $saved = [];

  // 저장
  if (isset($_POST['bwf_save_questions']) && isset($_POST['bwf_nonce']) && wp_verify_nonce($_POST['bwf_nonce'],'bwf_owner_form')) {
    $answers = [];
    foreach(($cfg['questions'] ?? []) as $q){
      $qid = $q['id'];
      if ($q['type']==='group'){
        $subAns = [];
        foreach(($q['sub'] ?? []) as $s){
          $sid = $s['id'];
          $subAns[$sid] = sanitize_textarea_field($_POST["{$qid}__{$sid}"] ?? '');
        }
        $answers[$qid] = $subAns;
      } else {
        $answers[$qid] = sanitize_textarea_field($_POST[$qid] ?? '');
      }
    }
    $data = $answers + [
      '_saved_at'=> current_time('mysql'),
      '_id'      => $saved['_id'] ?? uniqid('q_', true),
    ];
    update_user_meta($uid,'bwf_questions',$data);

    $hist = get_user_meta($uid,'bwf_questions_history', true); if(!is_array($hist)) $hist=[];
    $hist[] = $data; update_user_meta($uid,'bwf_questions_history',$hist);
  }

  wp_enqueue_style('bwf-forms');

  // 진행률 총합
  $totalUnits = 0;
  foreach(($cfg['questions'] ?? []) as $q){ $totalUnits += intval($q['count_as'] ?? 1); }

  ob_start(); ?>
  <form method="post" class="bwf-form bwf-owner" novalidate id="bwf-owner-form">
    <?php wp_nonce_field('bwf_owner_form','bwf_nonce'); ?>

    <h2 class="bwf-title"><?php echo esc_html($title); ?></h2>
    <p class="bwf-help"><?php echo wp_kses_post($intro); ?></p>

    <!-- 진행 현황 -->
    <div class="bwf-topwrap">
      <div class="bwf-top-title">작성 <span class="done">0</span>/<span class="total"><?php echo intval($totalUnits); ?></span>문항</div>
      <div id="bwf-progress"><div class="bar"></div><span class="label"></span></div>
    </div>

    <?php foreach(($cfg['questions'] ?? []) as $q): ?>
      <?php
        $qid  = $q['id'];
        $type = $q['type'];
        $label= $q['label'] ?? '';
        $desc = $q['desc_html'] ?? '';
        $ex   = $q['examples_html'] ?? '';
        $req  = ($q['required']!==false);
        $min  = ($q['minlength'] === '' || $q['minlength'] === null) ? ($type==='group'?0:$globalMin) : intval($q['minlength']);
      ?>
      <div class="bwf-field bwf-q" data-qid="<?php echo esc_attr($qid); ?>" data-type="<?php echo esc_attr($type); ?>" data-count="<?php echo intval($q['count_as']??1); ?>">
        <?php if($type!=='group'): ?>
          <label><?php echo esc_html($label); ?> <?php if($req): ?><span class="bwf-required">*</span><?php endif; ?></label>
          <?php if($desc): ?><p class="bwf-desc"><?php echo wp_kses_post($desc); ?></p><?php endif; ?>
          <?php if($ex): ?><div class="bwf-examples"><?php echo wp_kses_post($ex); ?></div><?php endif; ?>

          <?php if($type==='text'): ?>
            <input type="text" name="<?php echo esc_attr($qid); ?>" <?php echo $req?'required':''; ?> <?php echo $min>0?'data-minlength="'.$min.'"':''; ?> value="<?php echo isset($saved[$qid])?esc_attr($saved[$qid]):''; ?>">
          <?php else: // textarea ?>
            <textarea name="<?php echo esc_attr($qid); ?>" <?php echo $req?'required':''; ?> <?php echo $min>0?'data-minlength="'.$min.'"':''; ?>><?php echo isset($saved[$qid])?esc_textarea($saved[$qid]):''; ?></textarea>
          <?php endif; ?>

          <div class="bwf-helper"><span class="bwf-counter">0</span><?php echo $min>0?' / '.$min.'자':''; ?></div>

        <?php else: // group ?>
          <h3 class="bwf-h3"><?php echo esc_html($label); ?> <?php if($req): ?><span class="bwf-required">*</span><?php endif; ?></h3>
          <?php if($desc): ?><p class="bwf-desc"><?php echo wp_kses_post($desc); ?></p><?php endif; ?>
          <?php if($ex): ?><div class="bwf-examples"><?php echo wp_kses_post($ex); ?></div><?php endif; ?>

          <?php foreach(($q['sub'] ?? []) as $s): 
            $sid = $s['id']; $slab = $s['label'] ?? ''; $ph = $s['placeholder'] ?? '';
            $sreq = ($s['required']!==false); $smin = intval($s['minlength'] ?? 0);
            $name = "{$qid}__{$sid}";
            $val  = (isset($saved[$qid]) && is_array($saved[$qid]) && isset($saved[$qid][$sid])) ? $saved[$qid][$sid] : '';
          ?>
            <label><?php echo esc_html($slab); ?> <?php if($sreq): ?><span class="bwf-required">*</span><?php endif; ?></label>
            <textarea name="<?php echo esc_attr($name); ?>" rows="4" placeholder="<?php echo esc_attr($ph); ?>" <?php echo $sreq?'required':''; ?> <?php echo $smin>0?'data-minlength="'.$smin.'"':''; ?>><?php echo esc_textarea($val); ?></textarea>
            <div class="bwf-helper"><span class="bwf-counter">0</span><?php echo $smin>0?' / '.$smin.'자':''; ?></div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>

    <div class="bwf-actions">
      <button type="submit" class="bwf-btn" name="bwf_save_questions">저장</button>
    </div>

    <script>
    (function(){
      const f = document.getElementById('bwf-owner-form');
      const total = Array.from(f.querySelectorAll('.bwf-q')).reduce((n,q)=> n + (parseInt(q.dataset.count||'1',10)||1), 0);
      f.querySelector('.total').textContent = total;
      const doneEl = f.querySelector('.done');
      const bar    = f.querySelector('#bwf-progress .bar');
      const label  = f.querySelector('#bwf-progress .label');

      function setCounter(el, min){
        const helper = el.nextElementSibling && el.nextElementSibling.classList.contains('bwf-helper') ? el.nextElementSibling : null;
        const len = (el.value||'').trim().length;
        if (helper){
          const c = helper.querySelector('.bwf-counter'); if(c) c.textContent = String(len);
          helper.classList.toggle('ok', min ? len>=min : !!len);
        }
        if (min){
          el.setCustomValidity(len>=min ? '' : (min+'자 이상 입력해주세요'));
        } else {
          if (el.required) el.setCustomValidity(len>0 ? '' : '필수 입력입니다.');
        }
      }

      function isQuestionDone(q){
        const fields = q.querySelectorAll('textarea, input[type="text"]');
        let allOk = true;
        fields.forEach(el=>{
          const min = parseInt(el.getAttribute('data-minlength')||'0',10);
          const len = (el.value||'').trim().length;
          const req = el.hasAttribute('required');
          if (min>0){ if(len<min) allOk=false; }
          else if (req){ if(len===0) allOk=false; }
        });
        return allOk;
      }

      function render(){
        let done = 0;
        const qs = Array.from(f.querySelectorAll('.bwf-q'));
        qs.forEach(q=>{
          if (isQuestionDone(q)) done += (parseInt(q.dataset.count||'1',10)||1);
        });
        const pct = Math.round(done/total*100);
        doneEl.textContent = done;
        bar.style.width = pct+'%';
        label.textContent = pct+'%';
      }

      const inputs = Array.from(f.querySelectorAll('textarea, input[type="text"]'));
      inputs.forEach(el=>{
        const min = parseInt(el.getAttribute('data-minlength')||'0',10);
        setCounter(el, min);
        el.addEventListener('input', ()=>{ setCounter(el, min); render(); });
      });
      render();
    })();
    </script>
  </form>
  <?php
  return ob_get_clean();
});
