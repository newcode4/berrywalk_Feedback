<?php
if (!defined('ABSPATH')) exit;

/**
 * 대표 질문지 빌더 (동적 문항/소문항/글자수/순서)
 * 옵션은 JSON 형태로 options 테이블에 저장: option_name = bwf_owner_builder_json
 */

function bwf_owner_builder_default_json(){
  $json = [
    "title" => "대표님 핵심 질문지",
    "intro_html" => "사업의 본질을 파악하고 고객에게 정말 묻고 싶은 질문을 구체화합니다. 각 문항은 <strong>최소 {MIN}자</strong>입니다. <em>(단, <strong>“고객에게 물어보고 싶은 3가지”</strong>는 글자수 제한 없음)</em>",
    "min_length" => 200,
    "questions" => [
      [
        "id"=>"problem","type"=>"textarea","label"=>"1. 지금, 우리 사업의 가장 큰 고민은 무엇인가요?","required"=>true,"minlength"=>200,"count_as"=>1,
        "desc_html"=>"설명: 현재 가장 답답하거나 성장이 정체되었다고 느끼는 구체적인 상황을 한두 문장으로 설명해 주세요. 예: “광고는 많이 했는데…” 등 <strong>숫자</strong>/<strong>행동</strong> 기반.",
        "examples_html"=>"<ul>
<li>“최근 인스타그램 광고 효율이 너무 안 나와요. 클릭은 많은데, 저희 웹사이트에 1분도 머물지 않고 나가는 사람이 많아요.”</li>
<li>“기존 고객들의 재구매율이 낮아서, 항상 새로운 고객을 찾아야 하는 부담이 큽니다.”</li>
</ul>"
      ],
      [
        "id"=>"value","type"=>"textarea","label"=>"2. 우리 서비스는 고객의 ‘어떤 문제’를 해결해주고 있나요?","required"=>true,"minlength"=>200,"count_as"=>1,
        "desc_html"=>"설명: 서비스 사용 전/후의 변화. 고객이 얻는 <strong>가장 중요한 가치</strong>를 한 문장으로.",
        "examples_html"=>"<ol>
<li>“번거로운 서류 작업을 단 5분 만에 자동화하여 본업에 집중.”</li>
<li>“바쁜 직장인에게 집밥 같은 건강한 한 끼 제공.”</li>
</ol>"
      ],
      [
        "id"=>"ideal_customer","type"=>"textarea","label"=>"3. 우리 서비스를 ‘누가’ 이용해야 하나요? 왜 우리를 선택하나요?","required"=>true,"minlength"=>200,"count_as"=>1,
        "desc_html"=>"설명: 가장 잘 맞는 사람 특징(나이/직업/관심사) + 우리를 고를 <strong>결정적 이유</strong>.",
        "examples_html"=>"<p>“운동은 하고 싶지만 시간이 부족한 <strong>집순이 20대 대학생</strong> — 15분 홈트 영상으로 해결.”</p>"
      ],
      [
        "id"=>"ask3","type"=>"group","label"=>"4. 고객에게 물어보고 싶은 3가지","required"=>true,"count_as"=>1,
        "desc_html"=>"대표님이 고객에게 직접 묻고 싶은 핵심 질문 3가지를 작성하세요. <strong>경험한 내용</strong>을 바탕으로 <strong>구체적</strong>으로.",
        "examples_html"=>"<p><strong>상품</strong></p><ul><li>“처음 받았을 때 어떤 느낌이었나요? (포장/디자인/첫 경험)”</li><li>“가장 만족/아쉬웠던 점은?”</li></ul>
<p><strong>서비스</strong></p><ul><li>“무료체험 때 가장 좋았던 점/아쉬웠던 점?”</li></ul>
<p><strong>고민별 예시</strong></p><ul><li>광고 효율 낮음 → “최근 <strong>[인스타그램 광고]</strong> 보셨을 때 인상적이었던 점/구매 망설임 이유는?”</li><li>재구매율 낮음 → “다른 서비스를 다시 이용하셨다면 이유는?”</li><li>차별점 불분명 → “A사 대비 장단점은?”</li></ul>",
        "sub" => [
          ["id"=>"q1","label"=>"질문 1","placeholder"=>"예: 우리 서비스를 알게 된 경로는 무엇이었나요?","required"=>true,"minlength"=>0],
          ["id"=>"q2","label"=>"질문 2","placeholder"=>"예: 결심 포인트는 무엇이었나요?","required"=>true,"minlength"=>0],
          ["id"=>"q3","label"=>"질문 3","placeholder"=>"예: 사용 중 가장 불편했던 점은?","required"=>true,"minlength"=>0]
        ]
      ],
      [
        "id"=>"competitors","type"=>"textarea","label"=>"5. 현재 경쟁사는 어디이며, 그들과의 차별점은 무엇이라고 생각하시나요?","required"=>true,"minlength"=>200,"count_as"=>1,
        "desc_html"=>"설명: 주요 경쟁사 1~2곳을 언급하고 우리 서비스의 강/약점 정리.",
        "examples_html"=>"<p>“A사는 싸지만 품질이 낮고, B사는 비싸지만 품질이 높다 → 우리는 <strong>적정 가격 + 높은 만족</strong>.”</p>"
      ]
    ]
  ];
  return wp_json_encode($json, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT);
}

/* 옵션 등록 */
add_action('admin_init', function(){
  register_setting('bwf_owner_builder_group','bwf_owner_builder_json', [
    'type'=>'string',
    'sanitize_callback'=>function($str){
      // 빈 값이면 기본 JSON
      if (trim((string)$str)==='') return bwf_owner_builder_default_json();
      // JSON 유효성 체크
      json_decode(stripslashes($str));
      return (json_last_error()===JSON_ERROR_NONE) ? $str : bwf_owner_builder_default_json();
    }
  ]);
});

/* 메뉴 등록 */
add_action('admin_menu', function(){
  add_submenu_page('bwf_crm','질문지 빌더','질문지 빌더','manage_options','bwf_owner_builder','bwf_owner_builder_page');
});

/* 페이지 렌더 */
function bwf_owner_builder_page(){
  $raw = get_option('bwf_owner_builder_json');
  if(!$raw) $raw = bwf_owner_builder_default_json();
  ?>
  <div class="wrap">
    <h1>대표 질문지 빌더</h1>
    <p>문항을 추가/삭제/이동하고, 그룹(소문항)도 구성할 수 있습니다. 저장 시 아래 UI가 JSON으로 직렬화되어 옵션에 저장됩니다.</p>

    <style>
      /* ===== Admin Builder Styles ===== */
      .bwf-bui-wrap { max-width: 980px; }
      .bwf-bui-row { background:#fff;border:1px solid #e5e7eb;border-radius:10px;padding:12px 14px;margin:10px 0; }
      .bwf-bui-row h3 { margin:0 0 8px; display:flex; align-items:center; gap:8px; }
      .bwf-bui-grid { display:grid; grid-template-columns: 1fr 1fr; gap:12px; }
      .bwf-bui-grid label { display:block; font-weight:600; margin:6px 0; }
      .bwf-bui-grid input[type="text"], .bwf-bui-grid input[type="number"], .bwf-bui-grid textarea, .bwf-bui-grid select { width:100%; }
      .bwf-bui-actions { display:flex; gap:6px; }
      .bwf-bui-sub { background:#f8fafc;border:1px dashed #cbd5e1;border-radius:8px;padding:10px;margin:8px 0; }
      .bwf-bui-sub h4 { margin:0 0 6px; display:flex; align-items:center; gap:6px; }
      .bwf-bui-sublist .item{ background:#fff;border:1px solid #e2e8f0;border-radius:8px;padding:8px;margin:6px 0; }
      .bwf-bui-sublist .row{ display:grid; grid-template-columns:1fr 1fr 120px; gap:8px; }
      .bwf-bui-sublist label{ display:block; font-weight:600; margin:4px 0; }
      .bwf-bui-json { font-family: ui-monospace, SFMono-Regular, Menlo, monospace; width:100%; height:220px; }
      .bwf-bui-note{ color:#475569; }
      .button.gray{ background:#f3f4f6;border-color:#e5e7eb;color:#111827; }
    </style>

    <div class="bwf-bui-wrap" id="bwfBui"></div>

    <form method="post" action="options.php" id="bwfBuiForm">
      <?php settings_fields('bwf_owner_builder_group'); ?>
      <input type="hidden" name="bwf_owner_builder_json" id="bwfOwnerJson" value="<?php echo esc_attr($raw); ?>">
      <?php submit_button('저장'); ?>
    </form>

    <script>
      (function(){
        const container = document.getElementById('bwfBui');
        const hidden = document.getElementById('bwfOwnerJson');
        let state;
        try { state = JSON.parse(hidden.value || "{}"); } catch(e){ state = {}; }
        if (!state || !state.questions) {
          try { state = JSON.parse(`<?php echo addslashes(bwf_owner_builder_default_json()); ?>`);}catch(e){state={};}
        }

        function uid(prefix){ return (prefix||'q') + '_' + Math.random().toString(36).slice(2,8); }

        function render(){
          container.innerHTML = '';
          const wrap = document.createElement('div');

          // global
          const g = document.createElement('div');
          g.className='bwf-bui-row';
          g.innerHTML = `
            <h3>공통 설정</h3>
            <div class="bwf-bui-grid">
              <div>
                <label>폼 제목</label>
                <input type="text" id="b_title" value="${state.title||''}">
              </div>
              <div>
                <label>최소 글자수(기본, 문항별 개별 설정시 해당값 무시)</label>
                <input type="number" id="b_min" value="${state.min_length||200}" min="0">
              </div>
              <div class="bwf-bui-grid" style="grid-column:1/-1">
                <label>인트로 문구(HTML 가능) — <span class="bwf-bui-note">{MIN}은 최소 글자수로 치환</span></label>
                <textarea id="b_intro" rows="3">${state.intro_html||''}</textarea>
              </div>
            </div>
          `;
          wrap.appendChild(g);

          // questions
          state.questions = state.questions || [];
          state.questions.forEach((q, idx)=>{
            const row = document.createElement('div');
            row.className='bwf-bui-row';
            row.dataset.idx = idx;

            const isGroup = q.type==='group';
            row.innerHTML = `
              <h3>
                <span>문항 ${idx+1}</span>
                <span class="bwf-bui-actions">
                  <button class="button button-small moveUp">위</button>
                  <button class="button button-small moveDown">아래</button>
                  <button class="button button-small delete">삭제</button>
                </span>
              </h3>
              <div class="bwf-bui-grid">
                <div>
                  <label>라벨(제목)</label>
                  <input type="text" class="q_label" value="${q.label||''}">
                </div>
                <div>
                  <label>타입</label>
                  <select class="q_type">
                    <option value="textarea" ${q.type==='textarea'?'selected':''}>텍스트영역</option>
                    <option value="text" ${q.type==='text'?'selected':''}>한 줄 입력</option>
                    <option value="group" ${q.type==='group'?'selected':''}>그룹(소문항)</option>
                  </select>
                </div>
                <div>
                  <label>필수</label>
                  <select class="q_required">
                    <option value="1" ${q.required!==false?'selected':''}>예</option>
                    <option value="0" ${q.required===false?'selected':''}>아니오</option>
                  </select>
                </div>
                <div>
                  <label>최소 글자수(빈칸=공통값)</label>
                  <input type="number" class="q_min" value="${(q.minlength||'')}">
                </div>
                <div>
                  <label>진행률 카운트(몇 문항으로 계산?)</label>
                  <input type="number" class="q_count" min="0" value="${q.count_as==null?1:q.count_as}">
                </div>
                <div style="grid-column:1/-1">
                  <label>설명(HTML 가능)</label>
                  <textarea rows="3" class="q_desc">${q.desc_html||''}</textarea>
                </div>
                <div style="grid-column:1/-1">
                  <label>예시(HTML 가능)</label>
                  <textarea rows="4" class="q_examples">${q.examples_html||''}</textarea>
                </div>
              </div>
              ${isGroup?`
                <div class="bwf-bui-sub">
                  <h4>소문항 <button class="button button-small addSub">추가</button></h4>
                  <div class="bwf-bui-sublist">
                    ${(q.sub||[]).map((s,si)=>`
                      <div class="item" data-si="${si}">
                        <div class="row">
                          <div>
                            <label>라벨</label>
                            <input type="text" class="s_label" value="${s.label||''}">
                          </div>
                          <div>
                            <label>플레이스홀더</label>
                            <input type="text" class="s_ph" value="${s.placeholder||''}">
                          </div>
                          <div>
                            <label>필수</label>
                            <select class="s_req">
                              <option value="1" ${(s.required!==false)?'selected':''}>예</option>
                              <option value="0" ${(s.required===false)?'selected':''}>아니오</option>
                            </select>
                          </div>
                        </div>
                        <div class="row">
                          <div>
                            <label>서브 ID</label>
                            <input type="text" class="s_id" value="${s.id||('s_'+si)}">
                          </div>
                          <div>
                            <label>최소 글자수(빈칸=제한 없음)</label>
                            <input type="number" class="s_min" value="${s.minlength||''}">
                          </div>
                          <div class="bwf-bui-actions" style="align-items:end;">
                            <button class="button button-small gray subUp">위</button>
                            <button class="button button-small gray subDown">아래</button>
                            <button class="button button-small deleteSub">삭제</button>
                          </div>
                        </div>
                      </div>
                    `).join('')}
                  </div>
                </div>
              `:''}
            `;
            wrap.appendChild(row);
          });

          const add = document.createElement('p');
          add.innerHTML = `<button class="button button-primary" id="addQ">문항 추가</button>`;
          wrap.appendChild(add);

          container.appendChild(wrap);

          // events
          wrap.addEventListener('click', function(e){
            const t = e.target;

            // add question
            if (t.id==='addQ'){ e.preventDefault();
              state.questions.push({ id:uid('q'), type:'textarea', label:'새 문항', required:true, minlength:'', count_as:1, desc_html:'', examples_html:'' });
              render(); return;
            }

            // row buttons
            const row = t.closest('.bwf-bui-row'); if(!row) return;
            const idx = parseInt(row.dataset.idx||'0',10);
            const q = state.questions[idx];

            if (t.classList.contains('delete')){ e.preventDefault(); state.questions.splice(idx,1); render(); return; }
            if (t.classList.contains('moveUp')){ e.preventDefault(); if(idx>0){ const tmp=state.questions[idx-1]; state.questions[idx-1]=q; state.questions[idx]=tmp; render(); } return; }
            if (t.classList.contains('moveDown')){ e.preventDefault(); if(idx<state.questions.length-1){ const tmp=state.questions[idx+1]; state.questions[idx+1]=q; state.questions[idx]=tmp; render(); } return; }
            if (t.classList.contains('addSub')){ e.preventDefault(); q.sub = q.sub||[]; q.sub.push({id:uid(q.id||'s'),label:'소문항',placeholder:'',required:true,minlength:''}); render(); return; }

            // sub item buttons
            const item = t.closest('.item');
            if(item){
              const si = parseInt(item.dataset.si||'0',10);
              if (t.classList.contains('deleteSub')){ e.preventDefault(); q.sub.splice(si,1); render(); return; }
              if (t.classList.contains('subUp')){ e.preventDefault(); if(si>0){ const tmp=q.sub[si-1]; q.sub[si-1]=q.sub[si]; q.sub[si]=tmp; render(); } return; }
              if (t.classList.contains('subDown')){ e.preventDefault(); if(si<q.sub.length-1){ const tmp=q.sub[si+1]; q.sub[si+1]=q.sub[si]; q.sub[si]=tmp; render(); } return; }
            }
          });

          // bind inputs
          // global
          g.querySelector('#b_title').addEventListener('input', e=> state.title = e.target.value);
          g.querySelector('#b_min').addEventListener('input', e=> state.min_length = Math.max(0,parseInt(e.target.value||'0',10)));
          g.querySelector('#b_intro').addEventListener('input', e=> state.intro_html = e.target.value);

          // per question
          wrap.querySelectorAll('.bwf-bui-row').forEach((row, i)=>{
            if(!row.dataset.idx) return;
            const q = state.questions[i];
            row.querySelector('.q_label').addEventListener('input', e=> q.label = e.target.value);
            row.querySelector('.q_type').addEventListener('change', e=> { q.type = e.target.value; if(q.type!=='group') delete q.sub; render(); });
            row.querySelector('.q_required').addEventListener('change', e=> q.required = (e.target.value==='1'));
            row.querySelector('.q_min').addEventListener('input', e=> q.minlength = e.target.value===''? '' : Math.max(0,parseInt(e.target.value,10)));
            row.querySelector('.q_count').addEventListener('input', e=> q.count_as = Math.max(0, parseInt(e.target.value||'0',10)));
            row.querySelector('.q_desc').addEventListener('input', e=> q.desc_html = e.target.value);
            row.querySelector('.q_examples').addEventListener('input', e=> q.examples_html = e.target.value);

            // sub items
            row.querySelectorAll('.bwf-bui-sublist .item').forEach((it,si)=>{
              const s = q.sub[si];
              it.querySelector('.s_label').addEventListener('input', e=> s.label=e.target.value);
              it.querySelector('.s_ph').addEventListener('input', e=> s.placeholder=e.target.value);
              it.querySelector('.s_req').addEventListener('change', e=> s.required=(e.target.value==='1'));
              it.querySelector('.s_id').addEventListener('input', e=> s.id=e.target.value);
              it.querySelector('.s_min').addEventListener('input', e=> s.minlength = e.target.value===''? '' : Math.max(0,parseInt(e.target.value,10)));
            });
          });
        }

        render();

        // serialize on submit
        document.getElementById('bwfBuiForm').addEventListener('submit', function(){
          hidden.value = JSON.stringify(state);
        });
      })();
    </script>
  </div>
  <?php
}
