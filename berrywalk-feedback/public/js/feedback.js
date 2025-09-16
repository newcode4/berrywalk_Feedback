(function($){
  $(function(){

    // 기타 경로 토글
    function toggleEtc(sel, input){
      var v = $(sel).val();
      if(v === 'etc'){ $(input).show().attr('required', true); }
      else { $(input).hide().val('').attr('required', false); }
    }
    $("#bwf-source").on('change', function(){ toggleEtc(this, "#bwf-source-etc"); });
    $("#bwf-source-fb").on('change', function(){ toggleEtc(this, "#bwf-source-etc-fb"); });
    toggleEtc("#bwf-source", "#bwf-source-etc");
    toggleEtc("#bwf-source-fb", "#bwf-source-etc-fb");

    // 100자 카운터
    function bindCounters(ctx){
      $(ctx).find("textarea[data-minlength]").each(function(){
        var $ta = $(this), min = parseInt($ta.data("minlength")||100,10),
            $c = $ta.siblings(".bwf-counter");
        function update(){
          var n = ($ta.val()||"").trim().length;
          $c.text(n+" / "+min+"자");
        }
        $ta.on("input blur", update);
        update();
      });
    }
    bindCounters(document);

    // 휴대폰 자동 하이픈
    $(document).on('input', '#bwf-phone', function(){
      var v = $(this).val().replace(/\D+/g,'').slice(0,11);
      if (v.startsWith('02')) {
        if (v.length > 9) v = v.replace(/^(\d{2})(\d{4})(\d{0,4}).*$/, '$1-$2-$3');
        else            v = v.replace(/^(\d{2})(\d{0,3})(\d{0,4}).*$/, '$1-$2-$3');
      } else {
        if (v.length > 10) v = v.replace(/^(\d{3})(\d{4})(\d{0,4}).*$/, '$1-$2-$3');
        else               v = v.replace(/^(\d{3})(\d{0,4})(\d{0,4}).*$/, '$1-$2-$3');
      }
      $(this).val(v);
    });

    // 폼 제출 시: 첫 에러로 스크롤
    $(".bwf-form").on("submit", function(e){
      var $form = $(this), ok = true, firstBad = null;

      $form.find("[required]").each(function(){
        var $el = $(this), val = ($el.val()||"").trim(), bad = false, msg="";
        if(!val) { bad = true; msg="필수 입력입니다."; }
        if(!bad && $el.is("textarea") && $el.data("minlength")){
          var min = parseInt($el.data("minlength"),10);
          if(val.length < min){ bad = true; msg="최소 "+min+"자 이상 입력해주세요."; }
        }
        var $errText = $el.siblings(".bwf-error-text");
        if(!$errText.length) $errText = $('<div class="bwf-error-text"></div>').insertAfter($el.next(".bwf-counter").length?$el.next():$el);
        if(bad){
          ok = false; $el.addClass("bwf-error"); $errText.text(msg);
          if(!firstBad) firstBad = $el;
        }else{
          $el.removeClass("bwf-error"); $errText.text("");
        }
      });

      if(!ok){
        e.preventDefault();
        $('html,body').animate({scrollTop:firstBad.offset().top - 100}, 300);
      }
    });
  });
})(jQuery);
