(function($){
  $(function(){

    /** 기타 경로 토글 */
    function toggleEtc(sel, input){
      var v = $(sel).val();
      if(v && v.indexOf('etc') === 0){ $(input).show(); } else { $(input).hide().val(''); }
    }
    $('#bwf-source').on('change', function(){ toggleEtc(this, '#bwf-source-etc'); });
    $('#bwf-source-fb').on('change', function(){ toggleEtc(this, '#bwf-source-etc-fb'); });
    toggleEtc('#bwf-source', '#bwf-source-etc');
    toggleEtc('#bwf-source-fb', '#bwf-source-etc-fb');

    /** 휴대폰 하이픈(간단) */
    $('#bwf-phone').on('input', function(){
      var n = $(this).val().replace(/\D+/g,'');
      if(n.length>=11) n = n.replace(/(\d{3})(\d{4})(\d{4}).*/,'$1-$2-$3');
      else if(n.length>=10) n = n.replace(/(\d{3})(\d{3})(\d{4}).*/,'$1-$2-$3');
      $(this).val(n);
    });

    /** 카운터 + Sticky 진행률 */
    function updateCounters(){
      var total = 0, answered = 0;
      $('textarea[data-minlength]').each(function(){
        total++;
        var $t = $(this), val = $.trim($t.val()), min = parseInt($t.data('minlength'),10)||0;
        $t.siblings('.bwf-helper').find('.bwf-counter').text(val.length + '자');
        if(val.length >= min) answered++;
      });
      $('#bwf-total').text(total);
      $('#bwf-answered').text(answered);
      var pct = total ? Math.round(answered/total*100) : 0;
      $('#bwf-progress .bwf-bar').css('width', pct+'%');
    }
    $(document).on('input', 'textarea[data-minlength]', updateCounters);
    updateCounters();

    /** 클라이언트 유효성 검사: 누락 항목 팝업 + 스크롤 + 빨간 테두리 */
    $(document).on('submit','.bwf-form', function(e){
      var $form = $(this), missing = [];
      $form.find('.bwf-invalid').removeClass('bwf-invalid');

      $form.find('[required]').each(function(){
        var ok = true, $f = $(this);
        if($f.is('textarea') && $f.data('minlength')){
          ok = $.trim($f.val()).length >= parseInt($f.data('minlength'),10);
        }else{
          ok = $.trim($f.val()) !== '';
        }
        if(!ok){
          var label = $f.closest('label').text() || $f.attr('name');
          missing.push($.trim(label));
          $f.addClass('bwf-invalid');
        }
      });

      if(missing.length){
        e.preventDefault();
        alert('필수 항목이 누락되었습니다:\n- ' + missing.join('\n- '));
        $('html,body').animate({scrollTop: $('.bwf-invalid').first().offset().top - 80}, 200);
      }
    });

  });
})(jQuery);
