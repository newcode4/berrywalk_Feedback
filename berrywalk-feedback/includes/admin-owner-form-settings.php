<?php
if (!defined('ABSPATH')) exit;

/* ------------------------------
   대표 질문지 빌더 – 옵션(JSON) 저장
   option_name: bwf_owner_builder_json
   ------------------------------ */

/** 기본 JSON */
function bwf_owner_builder_default_json(){
  $json = [
    "title" => "대표님 핵심 질문지",
    "intro_html" => "사업의 본질을 파악하고 고객에게 정말 묻고 싶은 질문을 구체화합니다. 각 문항은 <strong>최소 {MIN}자</strong>입니다. <em>(단, <strong>“고객에게 물어보고 싶은 3가지”</strong>는 글자수 제한 없음)</em>",
    "min_length" => 200,
    "questions" => [
      [
        "id"=>"problem","type"=>"textarea","label"=>"1. 지금, 우리 사업의 가장 큰 고민은 무엇인가요?","required"=>true,"minlength"=>200,"count_as"=>1,
        "desc_html"=>"설명: 현재 가장 답답하거나 성장이 정체되었다고 느끼는 구체적인 상황을 한두 문장으로 설명해 주세요. 예: “광고는 많이 했는데…” 등 <strong>숫자</strong>/<strong>행동</strong> 기반.",
        "examples_html"=>"<ul><li>“최근 인스타그램 광고 효율이 너무 안 나와요. 클릭은 많은데, 웹사이트 체류가 1분도 안 돼요.”</li><li>“재구매율이 낮아 신규 고객 유입 부담이 큽니다.”</li></ul>"
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
        "desc_html"=>"고객의 <strong>실제 경험</strong>을 바탕으로 구체적으로 묻는 3개의 질문을 작성하세요.",
        "examples_html"=>"<p><strong>상품</strong></p><ul><li>“첫인상(포장/디자인/첫 사용)?”</li><li>“가장 만족/아쉬웠던 점?”</li></ul><p><strong>서비스</strong></p><ul><li>“무료체험 때 최고의 포인트/아쉬움?”</li></ul>",
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
  return wp_json_encode($json, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT);
}

/* 옵션 등록(저장 시 JSON 정합성 보정) */
add_action('admin_init', function(){
  register_setting('bwf_owner_builder_group','bwf_owner_builder_json', [
    'type'=>'string',
    'sanitize_callback'=>function($raw){
      $raw = wp_unslash($raw);               // 1) 슬래시 제거
      $try = json_decode($raw, true);
      if (json_last_error() !== JSON_ERROR_NONE) {
        // 깨졌으면 기본값으로 되돌림(안전)
        return bwf_owner_builder_default_json();
      }
      // 2) 정렬/정상화해서 저장
      return wp_json_encode($try, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT);
    }
  ]);
});

/* 메뉴 */
add_action('admin_menu', function(){
  add_submenu_page('bwf_crm','질문지 빌더','질문지 빌더','manage_options','bwf_owner_builder','bwf_owner_builder_page');
});

/* 관리자 페이지 */
function bwf_owner_builder_page(){
  $raw = get_option('bwf_owner_builder_json');
  if(!$raw) $raw = bwf_owner_builder_default_json();
  ?>
  <div class="wrap">
    <h1>대표 질문지 빌더</h1>
    <p>문항 추가/삭제/이동, 타입 변경, 그룹(소문항) 구성까지 지원합니다.</p>

    <style>
      /* 빌더 전용 스타일(관리자) */
      .bwf-bui-wrap{max-width:980px}
      .bwf-bui-row{background:#fff;border:1px solid #e5e7eb;border-radius:10px;padding:12px 14px;margin:10px 0}
      .bwf-bui-row h3{margin:0 0 8px;display:flex;align-items:center;gap:8px}
      .bwf-bui-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px}
      .bwf-bui-grid label{display:block;font-weight:600;margin:6px 0}
      .bwf-bui-grid input[type=text],.bwf-bui-grid input[type=number],.bwf-bui-grid textarea,.bwf-bui-grid select{width:100%}
      .bwf-bui-actions{display:flex;gap:6px}
      .bwf-bui-sub{background:#f8fafc;border:1px dashed #cbd5e1;border-radius:8px;padding:10px;margin:8px 0}
      .bwf-bui-sub h4{margin:0 0 6px;display:flex;align-items:center;gap:6px}
      .bwf-bui-sublist .item{background:#fff;border:1px solid #e2e8f0;border-radius:8px;padding:8px;margin:6px 0}
      .bwf-bui-sublist .row{display:grid;grid-template-columns:1fr 1fr 120px;gap:8px}
      .bwf-bui-sublist label{display:block;font-weight:600;margin:4px 0}
      .bwf-bui-note{color:#475569}
      .button.gray{background:#f3f4f6;border-color:#e5e7eb;color:#111827}
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

      // 안전 파서
      function parseJSON(s, fallback){ try{ return JSON.parse(s); }catch(e){ return fallback; } }
      let state = parseJSON(hidden.value, null);
      if(!state || !state.questions){ state = parseJSON(`<?php echo addslashes(bwf_owner_builder_default_json()); ?>`, {}); }

      // 도우미
      const uid = p => (p||'q')+'_'+Math.random().toString(36).slice(2,8);
      const syncHidden = () => hidden.value = JSON.stringify(state); // ★ 모든 변경 즉시 반영

      // 공통 렌더
      function render(){
        container.innerHTML = '';
        const wrap = document.createElement('div');

        // 공통 설정
        const g = document.createElement('div');
        g.className='bwf-bui-row';
        g.innerHTML = `
          <h3>공통 설정</h3>
          <div class="bwf-bui-grid">
            <div>
              <label>폼 제목</label>
              <input type="text" data-global="title" value="${state.title||''}">
            </div>
            <div>
              <label>최소 글자수(기본값)</label>
              <input type="number" min="0" data-global="min_length" value="${state.min_length||200}">
            </div>
            <div style="grid-column:1/-1">
              <label>인트로(HTML 가능) <span class="bwf-bui-note">※ {MIN}은 최소 글자수로 치환</span></label>
              <textarea rows="3" data-global="intro_html">${state.intro_html||''}</textarea>
            </div>
          </div>
        `;
        wrap.appendChild(g);

        // 문항들
        state.questions = state.questions || [];
        state.questions.forEach((q, idx)=>{
          const isGroup = q.type === 'group';
          const row = document.createElement('div');
          row.className = 'bwf-bui-row';
          row.dataset.idx = idx;

          row.innerHTML = `
            <h3>
              <span>문항 ${idx+1}</span>
              <span class="bwf-bui-actions">
                <button class="button button-small" data-act="up">위</button>
                <button class="button button-small" data-act="down">아래</button>
                <button class="button button-small" data-act="del">삭제</button>
              </span>
            </h3>
            <div class="bwf-bui-grid">
              <div>
                <label>라벨(제목)</label>
                <input type="text" data-field="label" value="${q.label||''}">
              </div>
              <div>
                <label>타입</label>
                <select data-field="type">
                  <option value="textarea" ${q.type==='textarea'?'selected':''}>텍스트영역</option>
                  <option value="text" ${q.type==='text'?'selected':''}>한 줄 입력</option>
                  <option value="group" ${q.type==='group'?'selected':''}>그룹(소문항)</option>
                </select>
              </div>
              <div>
                <label>필수</label>
                <select data-field="required">
                  <option value="1" ${(q.required!==false)?'selected':''}>예</option>
                  <option value="0" ${(q.required===false)?'selected':''}>아니오</option>
                </select>
              </div>
              <div>
                <label>최소 글자수(빈칸=공통)</label>
                <input type="number" min="0" data-field="minlength" value="${(q.minlength===''||q.minlength==null)?'':q.minlength}">
              </div>
              <div>
                <label>진행률 카운트(몇 문항으로 계산?)</label>
                <input type="number" min="0" data-field="count_as" value="${(q.count_as==null)?1:q.count_as}">
              </div>
              <div style="grid-column:1/-1">
                <label>설명(HTML 가능)</label>
                <textarea rows="3" data-field="desc_html">${q.desc_html||''}</textarea>
              </div>
              <div style="grid-column:1/-1">
                <label>예시(HTML 가능)</label>
                <textarea rows="4" data-field="examples_html">${q.examples_html||''}</textarea>
              </div>
            </div>

            ${isGroup ? `
              <div class="bwf-bui-sub">
                <h4>소문항 <button class="button button-small" data-act="addSub">추가</button></h4>
                <div class="bwf-bui-sublist">
                  ${(q.sub||[]).map((s,si)=>`
                    <div class="item" data-si="${si}">
                      <div class="row">
                        <div>
                          <label>라벨</label>
                          <input type="text" data-sub="label" value="${s.label||''}">
                        </div>
                        <div>
                          <label>플레이스홀더</label>
                          <input type="text" data-sub="placeholder" value="${s.placeholder||''}">
                        </div>
                        <div>
                          <label>필수</label>
                          <select data-sub="required">
                            <option value="1" ${(s.required!==false)?'selected':''}>예</option>
                            <option value="0" ${(s.required===false)?'selected':''}>아니오</option>
                          </select>
                        </div>
                      </div>
                      <div class="row">
                        <div>
                          <label>서브 ID</label>
                          <input type="text" data-sub="id" value="${s.id||('s_'+si)}">
                        </div>
                        <div>
                          <label>최소 글자수(빈칸=제한없음)</label>
                          <input type="number" min="0" data-sub="minlength" value="${(s.minlength===''||s.minlength==null)?'':s.minlength}">
                        </div>
                        <div class="bwf-bui-actions" style="align-items:end;">
                          <button class="button button-small gray" data-act="subUp">위</button>
                          <button class="button button-small gray" data-act="subDown">아래</button>
                          <button class="button button-small" data-act="subDel">삭제</button>
                        </div>
                      </div>
                    </div>
                  `).join('')}
                </div>
              </div>
            ` : '' }
          `;
          wrap.appendChild(row);
        });

        // 문항 추가 버튼
        const addWrap = document.createElement('p');
        addWrap.innerHTML = `<button class="button button-primary" id="bwfAddQ">문항 추가</button>`;
        wrap.appendChild(addWrap);

        container.appendChild(wrap);
        syncHidden(); // 렌더 후 상태 동기화

        /* 이벤트: 위임 방식으로 안정화 */
        wrap.addEventListener('click', function(e){
          const t = e.target;
          // 문항 추가
          if(t.id==='bwfAddQ'){ e.preventDefault();
            state.questions.push({id:uid('q'),type:'textarea',label:'새 문항',required:true,minlength:'',count_as:1,desc_html:'',examples_html:''});
            render(); return;
          }
          // 행/인덱스 탐색
          const row = t.closest('.bwf-bui-row[data-idx]');
          if(!row) return;
          const i = parseInt(row.dataset.idx,10);
          const q = state.questions[i];

          // 행 액션
          const act = t.dataset.act;
          if(act==='del'){ e.preventDefault(); state.questions.splice(i,1); render(); return; }
          if(act==='up'){ e.preventDefault(); if(i>0){ [state.questions[i-1],state.questions[i]]=[state.questions[i],state.questions[i-1]]; render(); } return; }
          if(act==='down'){ e.preventDefault(); if(i<state.questions.length-1){ [state.questions[i+1],state.questions[i]]=[state.questions[i],state.questions[i+1]]; render(); } return; }
          if(act==='addSub'){ e.preventDefault(); q.sub=q.sub||[]; q.sub.push({id:uid(q.id||'s'),label:'소문항',placeholder:'',required:true,minlength:''}); render(); return; }

          // 소문항 액션
          const item = t.closest('.item[data-si]');
          if(item){
            const si = parseInt(item.dataset.si,10);
            if(act==='subDel'){ e.preventDefault(); q.sub.splice(si,1); render(); return; }
            if(act==='subUp'){ e.preventDefault(); if(si>0){ [q.sub[si-1],q.sub[si]]=[q.sub[si],q.sub[si-1]]; render(); } return; }
            if(act==='subDown'){ e.preventDefault(); if(si<q.sub.length-1){ [q.sub[si+1],q.sub[si]]=[q.sub[si],q.sub[si+1]]; render(); } return; }
          }
        });

        // 값 변경(입력/선택) – 위임
        wrap.addEventListener('input', function(e){
          const el = e.target;
          // 글로벌
          if(el.matches('[data-global]')){
            const k = el.getAttribute('data-global');
            state[k] = (k==='min_length') ? Math.max(0,parseInt(el.value||'0',10)) : el.value;
            syncHidden(); return;
          }
          // 문항
          const row = el.closest('.bwf-bui-row[data-idx]'); if(!row) return;
          const i = parseInt(row.dataset.idx,10); const q = state.questions[i];
          if(el.matches('[data-field]')){
            const k = el.getAttribute('data-field');
            if(k==='required'){ q[k] = (el.value==='1'); }
            else if(k==='minlength' || k==='count_as'){ q[k] = (el.value===''? '' : Math.max(0,parseInt(el.value,10))); }
            else if(k==='type'){ q[k] = el.value; if(q[k]!=='group') delete q.sub; render(); return; } // 타입 변경 즉시 재렌더
            else { q[k] = el.value; }
            syncHidden(); return;
          }
          // 소문항
          const item = el.closest('.item[data-si]');
          if(item && q.type==='group'){
            const si = parseInt(item.dataset.si,10); const s = q.sub[si];
            const sk = el.getAttribute('data-sub');
            if(sk==='required'){ s[sk] = (el.value==='1'); }
            else if(sk==='minlength'){ s[sk] = (el.value===''? '' : Math.max(0,parseInt(el.value,10))); }
            else { s[sk] = el.value; }
            syncHidden(); return;
          }
        });

        // 제출 전 최종 동기화(이중 안전장치)
        document.getElementById('bwfBuiForm').addEventListener('submit', function(){ syncHidden(); });
      }
      render();
    })();
    </script>
  </div>
  <?php
}
