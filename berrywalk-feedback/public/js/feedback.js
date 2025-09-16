(function ($) {
  $(function () {
    // 설정
    const MIN = 100;

    // 헤더 카운터/프로그레스 (있는 경우 자동 연결)
    const $progress = $("#bwf-progress");
    const $bar = $("#bwf-progress .bwf-bar");
    const $count = $("#bwf-answered");
    const $total = $("#bwf-total");

    // 대상 텍스트에어리어 수집
    const $areas = $(".bwf-form textarea[data-minlength], .bwf-form textarea[required]");
    const total = $areas.length;
    if ($total.length) $total.text(total);

    // 각 문항에 라이브 카운터/에러영역 붙이기
    $areas.each(function () {
      const $ta = $(this);
      if ($ta.next(".bwf-helper").length) return;

      const min = parseInt($ta.data("minlength") || MIN, 10);
      const $helper = $(`
        <div class="bwf-helper">
          <small class="bwf-guide">최소 ${min}자</small>
          <small class="bwf-counter"><span class="num">0</span> / ${min}</small>
          <div class="bwf-error" aria-live="polite"></div>
        </div>
      `);
      $ta.after($helper);

      const update = () => {
        const len = $.trim($ta.val() || "").length;
        $helper.find(".num").text(len);
        if (len && len < min) {
          $ta.attr("aria-invalid", "true").addClass("bwf-invalid");
          $helper.find(".bwf-error").text(`아직 ${min - len}자 더 작성해주세요.`);
        } else {
          $ta.removeAttr("aria-invalid").removeClass("bwf-invalid");
          $helper.find(".bwf-error").text("");
        }
        refreshProgress();
      };

      $ta.on("input", update);
      update();
    });

    // 답변 카운트/프로그레스 갱신
    function refreshProgress() {
      const answered = $areas.toArray().filter(el => {
        const min = parseInt($(el).data("minlength") || MIN, 10);
        return $.trim($(el).val() || "").length >= min;
      }).length;

      if ($count.length) $count.text(answered);
      if ($bar.length) {
        const pct = total ? Math.round((answered / total) * 100) : 0;
        $bar.css("width", pct + "%");
      }
    }

    // 제출 시 첫 번째 미충족 문항으로 스크롤 & 포커스
    $(".bwf-form").on("submit", function (e) {
      let firstInvalid = null;

      $areas.each(function () {
        const $ta = $(this);
        const min = parseInt($ta.data("minlength") || MIN, 10);
        const len = $.trim($ta.val() || "").length;
        if (!firstInvalid && len < min) firstInvalid = $ta.get(0);
      });

      if (firstInvalid) {
        e.preventDefault();
        const $fi = $(firstInvalid);
        $fi.addClass("bwf-invalid").attr("aria-invalid", "true");
        $('html, body').animate({ scrollTop: $fi.offset().top - 100 }, 250);
        $fi.focus();
      } else {
        // UX: 중복 제출 방지
        $(this).find("button[type=submit]").prop("disabled", true).text("제출 중…");
      }
    });

    // 카카오 버튼(후속 OAuth 연결 전 안내)
    $(".bwf-kakao-btn").on("click", function (e) {
      e.preventDefault();
      alert("카카오 간편 시작은 곧 연결됩니다. 지금은 이메일로 가입을 진행해주세요.");
    });
  });
})(jQuery);
