(function($){
  $(document).ready(function(){

    // 최소 글자수 제한 (100자 이상)
    $(".bwf-form textarea[required]").on("blur", function(){
      let val = $(this).val().trim();
      if(val.length < 100){
        alert("답변은 최소 100자 이상 입력해주세요.");
        $(this).focus();
      }
    });

    // 카카오 버튼 클릭시 알림 (OAuth 붙이기 전까지)
    $(".bwf-kakao-btn").on("click", function(e){
      e.preventDefault();
      alert("카카오 로그인은 곧 지원됩니다! 현재는 이메일 회원가입을 이용해주세요.");
    });

    // 폼 제출 UX 개선
    $(".bwf-form").on("submit", function(){
      $(this).find("button[type=submit]").prop("disabled",true).text("제출 중...");
    });

  });
})(jQuery);
