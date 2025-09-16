(function($){
  $(function(){

    // 최소 글자수 제한 (100자 이상)
    $(".bwf-form textarea[required]").on("blur", function(){
      var val = $.trim($(this).val() || "");
      if(val.length && val.length < 100){
        alert("답변은 최소 100자 이상 입력해주세요.");
        $(this).focus();
      }
    });

    // 카카오 버튼 (후속 OAuth 붙이기 전까지)
    $(".bwf-kakao-btn").on("click", function(e){
      e.preventDefault();
      alert("카카오 로그인은 곧 지원됩니다! 현재는 이메일 회원가입을 이용해주세요.");
    });

    // 기타 경로 선택 시 텍스트 박스 토글
    function toggleEtc(sel, input){
      var v = $(sel).val();
      if(v === 'etc'){ $(input).show().attr('required', true); }
      else { $(input).hide().val('').attr('required', false); }
    }
    $("#bwf-source").on('change', function(){ toggleEtc(this, "#bwf-source-etc"); });
    $("#bwf-source-fb").on('change', function(){ toggleEtc(this, "#bwf-source-etc-fb"); });
    // 페이지 로드 시 초기 상태 반영
    toggleEtc("#bwf-source", "#bwf-source-etc");
    toggleEtc("#bwf-source-fb", "#bwf-source-etc-fb");

    // 제출 UX
    $(".bwf-form").on("submit", function(){
      $(this).find("button[type=submit]").prop("disabled",true).text("처리 중...");
    });

  });
})(jQuery);
