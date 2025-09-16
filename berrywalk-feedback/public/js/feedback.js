jQuery(function($){
  // 글자수 + 진행도(그룹) ------------------------------------
  function computeProgress(){
    // 문항 묶음: ask3 그룹(고객에게 물어보고 싶은 3가지)은 하나로 카운트
    const $targets = $('textarea[data-minlength]');
    const groups = {};         // {groupKey: [textarea, ...]}
    const single = [];         // 비그룹 항목

    $targets.each(function(){
      const $t = $(this);
      const g = $t.data('group') || null;
      if (g) {
        if (!groups[g]) groups[g] = [];
        groups[g].push($t);
      } else {
        single.push($t);
      }
      // 글자수 카운터 갱신
      const cur = $t.val().length;
      const min = parseInt($t.data('minlength'), 10) || 0;
      $t.siblings('.bwf-helper').find('.bwf-counter').text(`${cur}/${min}`);
    });

    // 총 문항 수 = 비그룹 개수 + 그룹 수
    const total = single.length + Object.keys(groups).length;

    // 완료 수 계산(비그룹은 개별, 그룹은 전원 충족 시 1)
    let done = 0;
    single.forEach($t=>{
      const cur = $t.val().length, min = parseInt($t.data('minlength'),10)||0;
      if (cur >= min) done++;
    });
    Object.values(groups).forEach(list=>{
      const allOk = list.every($t => ($t.val().length >= (parseInt($t.data('minlength'),10)||0)));
      if (allOk) done++;
    });

    const pct = total ? Math.round((done/total)*100) : 0;
    $('.bwf-topcount .done').text(done);
    $('.bwf-topcount .total').text(total);
    $('#bwf-progress .bar').css('width', pct+'%');
    $('#bwf-progress .label').text(pct+'%');
  }

  // 입력 시 즉시 반영
  $(document).on('input', 'textarea[data-minlength]', computeProgress);

  // 제출 검증 -------------------------------------------------
  $('form.bwf-form').on('submit', function(e){
    let ok = true, firstBad = null;

    // 필수 input/select
    $(this).find('[required]').each(function(){
      const el = this;
      if (!el.value || (el.tagName==='SELECT' && !el.value)) {
        ok = false;
        $(el).addClass('bwf-invalid');
        if (!firstBad) firstBad = el;
      } else {
        $(el).removeClass('bwf-invalid');
      }
    });

    // 텍스트영역: 100자 최소
    $(this).find('textarea[data-minlength]').each(function(){
      const $t = $(this);
      const min = parseInt($t.data('minlength'),10) || 0;
      if ($t.val().length < min) {
        ok = false;
        $t.addClass('bwf-invalid');
        if (!firstBad) firstBad = $t[0];
      } else {
        $t.removeClass('bwf-invalid');
      }
    });

    if (!ok) {
      e.preventDefault();
      const $p = $(firstBad).closest('.bwf-field, div');
      if ($p.length) $('html,body').animate({scrollTop: $p.offset().top - 100}, 300);
      alert('필수 항목이 누락되었거나 최소 글자 수를 만족하지 못했습니다. 빨간 표시 항목을 확인해 주세요.');
    }
  });

  // “알게 된 경로 = 기타” 토글(대표/회원가입 공통)
  function toggleEtc(){
    const $sel = $('#bwf-source');
    const $etc = $('#bwf-source-etc');
    if ($sel.length && $etc.length) {
      $etc.toggle($sel.val()==='etc');
    }
  }
  $(document).on('change','#bwf-source', toggleEtc);
  toggleEtc();

  // 초기 계산
  computeProgress();
});
document.addEventListener('DOMContentLoaded', () => {
  const phone = document.getElementById('bwf-phone');
  if (phone) {
    const fmt010 = (v) => {
      const d = v.replace(/\D/g,'').slice(0, 11);
      if (!d.startsWith('010')) return d;                 // 010 이외는 입력 자체를 막고 싶다면 여길 강제 리턴 처리
      if (d.length <= 3) return d;
      if (d.length <= 7) return d.slice(0,3) + '-' + d.slice(3);
      return d.slice(0,3) + '-' + d.slice(3,7) + '-' + d.slice(7,11);
    };
    const validate = () => {
      const ok = /^010-\d{4}-\d{4}$/.test(phone.value);
      phone.setCustomValidity(ok ? '' : '010-1234-5678 형식으로 입력해주세요');
    };

    ['input','paste','blur','change'].forEach(ev=>{
      phone.addEventListener(ev, () => {
        const pos = phone.selectionStart;
        phone.value = fmt010(phone.value);
        validate();
      }, {passive:true});
    });
  }
});

document.addEventListener('DOMContentLoaded', () => {
  const form = document.querySelector('.bwf-form');
  if (!form) return;

  // 대표 질문지 6문항 규칙: [problem, value, ideal_customer, (q1+q2+q3 그룹), one_question, competitors]
  const fields = {
    problem:          form.querySelector('textarea[name="problem"]'),
    value:            form.querySelector('textarea[name="value"]'),
    ideal:            form.querySelector('textarea[name="ideal_customer"]'),
    ask3:             form.querySelectorAll('textarea[data-group="ask3"]'),
    one:              form.querySelector('input[name="one_question"]'),
    diff:             form.querySelector('textarea[name="competitors"]'),
  };

  const topCountEl = form.querySelector('.bwf-topcount .done') || form.querySelector('#bwf-answered');
  const topTotalEl = form.querySelector('.bwf-topcount .total') || form.querySelector('#bwf-total');
  const progress   = document.getElementById('bwf-progress');
  const bar        = progress ? progress.querySelector('.bar') : null;
  const label      = progress ? progress.querySelector('.label') : null;

  const MIN = 50;       // 대표질문지 최소 글자수
  const TOTAL = 6;

  if (topTotalEl) topTotalEl.textContent = TOTAL;

  const len = el => (el?.value || '').trim().length;
  const okText = el => el && len(el) >= (parseInt(el.dataset.minlength || MIN,10));
  const okAsk3 = () => {
    let okAll = true;
    (fields.ask3 || []).forEach(t => { if (!okText(t)) okAll = false; });
    return okAll;
  };
  const okOne = () => (fields.one?.value || '').trim().length > 0;

  const updateCounters = () => {
    let done = 0;
    if (okText(fields.problem)) done++;
    if (okText(fields.value)) done++;
    if (okText(fields.ideal)) done++;
    if (okAsk3()) done++;
    if (okOne()) done++;
    if (okText(fields.diff)) done++;

    if (topCountEl) topCountEl.textContent = done;
    if (bar)   bar.style.width = (done / TOTAL * 100).toFixed(0) + '%';
    if (label) label.textContent = `진행률 ${Math.round(done/TOTAL*100)}%`;

    // 각 textarea 하단 글자수 표시
    form.querySelectorAll('textarea').forEach(t=>{
      const hc = t.parentElement.querySelector('.bwf-counter');
      if (hc) hc.textContent = `${len(t)}자`;
    });
  };

  // 이벤트 바인딩
  form.querySelectorAll('textarea, input[type="text"]').forEach(el=>{
    el.addEventListener('input', updateCounters, {passive:true});
    el.addEventListener('change', updateCounters, {passive:true});
  });

  updateCounters(); // 초기 렌더
});

document.addEventListener('DOMContentLoaded', () => {
  const phs = [document.getElementById('bwf-phone'), document.getElementById('bwf-phone-admin')].filter(Boolean);
  phs.forEach(phone=>{
    const fmt = v => {
      const d = v.replace(/\D/g,'').slice(0,11);
      if (!d.startsWith('010')) return d;
      if (d.length <= 3) return d;
      if (d.length <= 7) return d.slice(0,3)+'-'+d.slice(3);
      return d.slice(0,3)+'-'+d.slice(3,7)+'-'+d.slice(7);
    };
    const validate = () => {
      const ok = /^010-\d{4}-\d{4}$/.test(phone.value);
      phone.setCustomValidity(ok ? '' : '010-1234-5678 형식만 허용됩니다.');
    };
    ['input','paste','blur','change'].forEach(ev=>{
      phone.addEventListener(ev, ()=>{
        phone.value = fmt(phone.value);
        validate();
      }, {passive:true});
    });
  });
});

document.addEventListener('DOMContentLoaded', () => {
  const form = document.querySelector('.bwf-form');
  if (!form) return;

  // 진행바가 없으면 주입
  if (!form.querySelector('.bwf-topwrap')) {
    const box = document.createElement('div');
    box.className = 'bwf-topwrap';
    box.innerHTML = `
      <div class="bwf-top-title">진행 현황</div>
      <div id="bwf-progress"><span class="bar"></span><span class="label"></span></div>
      <div class="bwf-helper">작성 <b class="done">0</b>/<b class="total">6</b> 문항 <span class="hint">각 문항 최소 50자</span></div>
    `;
    form.prepend(box);
  }

  // 필드 수집(6문항 규칙)
  const F = {
    problem: form.querySelector('textarea[name="problem"]'),
    value: form.querySelector('textarea[name="value"]'),
    ideal: form.querySelector('textarea[name="ideal_customer"]'),
    ask3: form.querySelectorAll('textarea[data-group="ask3"]'), // q1 q2 q3
    one: form.querySelector('input[name="one_question"]'),
    diff: form.querySelector('textarea[name="competitors"]')
  };

  // 각 textarea 하단에 글자수 메타 주입
  form.querySelectorAll('textarea').forEach(t=>{
    if (!t.parentElement.querySelector('.bwf-count')) {
      const m = document.createElement('div');
      m.className = 'bwf-count';
      m.innerHTML = `<span class="now">0자</span><span class="need">50자 이상</span>`;
      t.parentElement.appendChild(m);
    }
  });

  const top = form.querySelector('.bwf-topwrap');
  const doneEl = top.querySelector('.done');
  const totalEl = top.querySelector('.total'); totalEl.textContent = '6';
  const bar = top.querySelector('.bar'); const label = top.querySelector('.label');
  const MIN = 50;

  const len = el => (el?.value || '').trim().length;
  const okT = el => el && len(el) >= (parseInt(el.dataset.minlength || MIN,10));
  const okAsk3 = () => { let ok=true; F.ask3.forEach(t=>{ if(!okT(t)) ok=false; }); return ok; };
  const okOne = () => (F.one?.value || '').trim().length>0;

  const update = () => {
    // ①②③ + ④(ask3합) + ⑤(one) + ⑥(diff)
    let done=0;
    if (okT(F.problem)) done++;
    if (okT(F.value)) done++;
    if (okT(F.ideal)) done++;
    if (okAsk3()) done++;
    if (okOne()) done++;
    if (okT(F.diff)) done++;

    doneEl.textContent = done;
    const p = Math.round(done/6*100);
    bar.style.width = p+'%';
    label.textContent = `진행률 ${p}%`;

    // 글자수 표시/남은 글자
    form.querySelectorAll('textarea').forEach(t=>{
      const n = len(t), min = parseInt(t.dataset.minlength || MIN,10);
      const wrap = t.parentElement.querySelector('.bwf-count');
      if (wrap) {
        wrap.querySelector('.now').textContent = `${n}자`;
        wrap.querySelector('.need').textContent = n>=min ? '충분합니다' : `+${min-n}자 더 입력`;
      }
    });
  };

  form.querySelectorAll('textarea,input[type="text"]').forEach(el=>{
    el.addEventListener('input', update, {passive:true});
    el.addEventListener('change', update, {passive:true});
  });

  update(); // 초기 렌더
});
