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
        function update(){ var n=($ta.val()||"").trim().length; $c.text(n+" / "+min+"자"); }
        $ta.on("input blur", update); update();
      });
    }
    bindCounters(document);

    // 폼 제출: 첫 번째 에러로 스크롤
    $(".bwf-form").on("submit", function(e){
      var $form = $(this), ok = true, firstBad = null;

      // required 검사 + minlength 검사
      $form.find("[required]").each(function(){
        var $el = $(this), val = ($el.val()||"").trim(), bad = false, msg="";
        if(!val) { bad = true; msg="필수 입력입니다."; }
        if(!bad && $el.is("textarea") && $el.data("minlength")){
          var min = parseInt($el.data("minlength"),10);
          if(val.length < min){ bad = true; msg="최소 "+min+"자 이상 입력해주세요."; }
        }
        var $err = $el.data("errEl");
        if(!$err){ $err = $('<div class="bwf-error-text"></div>').insertAfter($el); $el.data("errEl",$err); }
        if(bad){
          ok = false; $el.addClass("bwf-error"); $err.text(msg).show();
          if(!firstBad) firstBad = $el;
        } else { $el.removeClass("bwf-error"); $err.text("").hide(); }
      });

      if(!ok){
        e.preventDefault();
        $('html,body').animate({scrollTop: firstBad.offset().top-120}, 300);
        firstBad.focus();
        return false;
      }

      // 중복 제출 방지
      $form.find("button[type=submit]").prop("disabled", true).text("처리 중...");
    });

  });
})(jQuery);
