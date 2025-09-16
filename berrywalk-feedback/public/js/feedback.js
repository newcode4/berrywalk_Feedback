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
