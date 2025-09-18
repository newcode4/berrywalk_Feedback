/* /assets/owner-form.js  (카운터 텍스트 – "몇자 남음" 친절 메시지로) */
(function($){
  if(!$ || !$('.bwos-form').length) return;

  const $form = $('.bwos-form').first();
  const $progress = $form.find('.bwos-progress');
  const reqs = []; let submitted=false;

  function updateProgress(){
    const total=reqs.length, done=reqs.filter(r=>r.valid()).length, pct=total?Math.round(done/total*100):0;
    $progress.find('.bar>span').css('width',pct+'%');
    $progress.find('.status').text('작성 '+done+'/'+total+' ('+pct+'%)');
  }

  function wire($wrap){
    const min = parseInt($wrap.data('min')||200,10);
    const $subs = $wrap.find('.bwf-sub textarea');
    const isGroup = $subs.length>0;
    let $cnt = $wrap.find('.bwos-count'); if(!$cnt.length) $cnt=$('<small class="bwos-count"></small>').appendTo($wrap);

    const setError = bad => $wrap.toggleClass('bwos-error', submitted && !!bad);
    const valid = ()=>{
      if(isGroup){ let ok=true; $subs.each(function(){ if(($(this).val()||'').trim()==='') ok=false; }); return ok; }
      return (($wrap.find('textarea,input[type="text"]').first().val()||'').trim().length >= min);
    };

    const update = ()=>{
      if(isGroup){
        let anyEmpty=false; $subs.each(function(){ if(($(this).val()||'').trim()==='') anyEmpty=true; });
        $cnt.text(anyEmpty ? '질문 3개 모두 작성' : '충족').toggleClass('need',anyEmpty).toggleClass('ok',!anyEmpty);
        setError(anyEmpty);
      }else{
        const v=($wrap.find('textarea,input[type="text"]').first().val()||'').trim();
        const diff = min - v.length;
        if(diff>0){ $cnt.text(diff+'자 남음'); $cnt.removeClass('ok').addClass('need'); }
        else      { $cnt.text('충족'); $cnt.removeClass('need').addClass('ok'); }
        setError(v.length < min);
      }
      updateProgress();
    };

    (isGroup ? $subs : $wrap.find('textarea,input[type="text"]').first()).on('input blur', update);
    reqs.push({wrap:$wrap, valid, update, min}); update();
  }

  $form.find('.bwos-required').each(function(){ wire($(this)); });
  updateProgress();

  const $modal=$('<div class="bwos-modal"><div class="bwos-modal-inner"><h4>입력이 필요한 항목이 있어요</h4><p>아래 항목을 확인해 주세요.</p><ul class="bwf-modal__list"></ul><button type="button" class="bwf-btn-secondary ok">확인</button></div></div>').appendTo('body');
  $modal.on('click','.ok',()=> $modal.removeClass('is-open'));

  let locked=false;
  $form.on('submit',function(e){
    submitted=true; let bad=false; const items=[];
    reqs.forEach(r=>{
      if(!r.valid()){
        bad=true; const lab=r.wrap.find('label').first().text().replace(/\*.*$/,'').trim();
        if(r.wrap.find('.bwf-sub').length){ items.push('· '+lab+' — 3개 질문 모두 작성'); }
        else{
          const len=($r=r.wrap.find('textarea,input[type="text"]').first()).val().trim().length;
          const diff=(r.min||200)-len; items.push('· '+lab+' — 최소 글자수 부족 ('+diff+'자 남음)');
        }
        r.update();
      }
    });
    if(bad){
      e.preventDefault();
      const ul=$modal.find('.bwf-modal__list').empty();
      items.forEach(t=> $('<li/>').text(t).appendTo(ul));
      $modal.addClass('is-open');
      const $first=$form.find('.bwos-error').first(); if($first.length) $('html,body').animate({scrollTop:$first.offset().top-120},180);
      return;
    }
    if(locked){ e.preventDefault(); return; }
    locked=true; $form.find('.bwos-submit').prop('disabled',true).text('저장 중…');
    setTimeout(()=>{ locked=false; $form.find('.bwos-submit').prop('disabled',false).text('저장'); }, 6000);
  });
})(jQuery);

/* === Tab 이동: textarea에서 Tab 누르면 다음 필드로 포커스 === */
(function($){
  const $areas = $('.bwos-form textarea:visible');
  $areas.on('keydown', function(e){
    if(e.key === 'Tab' && !e.shiftKey){
      const list = $('.bwos-form textarea:visible');
      const idx = list.index(this);
      if(idx > -1 && idx < list.length - 1){
        e.preventDefault();
        list.eq(idx + 1).focus();
      }
    }
  });
})(jQuery);
