(function ($) {
  $(function () {
    const MIN = 100;

    // 기타 경로 토글
    function toggleEtc(sel, input){
      var v = $(sel).val();
      if(v === 'etc'){ $(input).show().attr('required', true); }
      else { $(input).hide().val('').attr('required', false); }
    }
    $("#bwf-source").on('change', function(){ toggleEtc(this, "#bwf-source-etc"); }).trigger('change');
    $("#bwf-source-fb").on('change', function(){ toggleEtc(this, "#bwf-source-etc-fb"); }).trigger('change');

    // 휴대폰 자동 하이픈 + 길이 제한(010-1234-5678)
    const $phone = $("#bwf-phone");
    $phone.on("input", function(){
      let digits = ($(this).val() || "").replace(/\D/g, "").slice(0, 11); // 11자리 제한
      // 기본 3-4-4, 02 지역 등은 일반화
      let out;
      if (digits.length <= 3) out = digits;
      else if (digits.length <= 7) out = digits.slice(0,3) + "-" + digits.slice(3);
      else out = digits.slice(0,3) + "-" + digits.slice(3,7) + "-" + digits.slice(7);
      $(this).val(out);
    });

    // 카운터/프로그레스
    const $areas = $(".bwf-form textarea[data-minlength], .bwf-form textarea[required]");
    const $cnt = $("#bwf-answered"), $tot = $("#bwf-total"), $bar = $("#bwf-progress .bwf-bar");
    const total = $areas.length; if ($tot.length) $tot.text(total);

    function bind($ta){
      if ($ta.next(".bwf-helper").length) return;
      const min = parseInt($ta.data("minlength") || MIN, 10);
      const $helper = $(`
        <div class="bwf-helper">
          <small class="bwf-guide">필요: ${min}자</small>
          <small class="bwf-counter"><span class="num">0</span>자 / ${min}자</small>
          <div class="bwf-error" aria-live="polite"></div>
        </div>
      `);
      $ta.after($helper);

      const onChange = () => {
        const len = $.trim($ta.val() || "").length;
        $helper.find(".num").text(len);
        if (len && len < min) {
          $ta.addClass("bwf-invalid").attr("aria-invalid","true");
          $helper.find(".bwf-error").text(`${min-len}자 더 작성해주세요.`);
        } else {
          $ta.removeClass("bwf-invalid").removeAttr("aria-invalid");
          $helper.find(".bwf-error").text("");
        }
        refreshProgress();
      };
      $ta.on("input", onChange);
      onChange();
    }
    $areas.each(function(){ bind($(this)); });

    function refreshProgress(){
      let answered = 0;
      $areas.each(function(){
        const min = parseInt($(this).data("minlength") || MIN, 10);
        if ($.trim($(this).val()||"").length >= min) answered++;
      });
      if ($cnt.length) $cnt.text(answered);
      if ($bar.length) $bar.css("width", (total ? Math.round(answered/total*100) : 0) + "%");
    }

    // 제출: 첫 에러로 스크롤&포커스
    $(".bwf-form").on("submit", function(e){
      let firstInvalid = null;
      $areas.each(function(){
        const min = parseInt($(this).data("minlength") || MIN, 10);
        if (!firstInvalid && $.trim($(this).val()||"").length < min) firstInvalid = this;
      });
      if (firstInvalid) {
        e.preventDefault();
        const $fi = $(firstInvalid);
        $('html, body').animate({scrollTop: $fi.offset().top - 100}, 250);
        $fi.focus();
        return false;
      }
      $(this).find("button[type=submit]").prop("disabled", true).text("처리 중…");
    });
  });
})(jQuery);
