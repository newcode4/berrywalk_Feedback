<?php
if (!defined('ABSPATH')) exit;

/** 옵션 셋 */
function bwf_industry_options(){
  return [
    'it_saas'=>'IT / SaaS','commerce'=>'커머스/쇼핑몰','food'=>'외식/식품','beauty'=>'뷰티/헬스',
    'education'=>'교육/강의','local'=>'로컬서비스','consulting'=>'컨설팅/대행','finance'=>'금융/핀테크','etc'=>'기타'
  ];
}
function bwf_source_options(){
  return [
    'instagram'=>'인스타그램','youtube'=>'유튜브','search'=>'검색(네이버/구글)',
    'referral'=>'지인 추천','blog'=>'블로그/커뮤니티','event'=>'세미나/이벤트','ads'=>'광고(배너/DA/SA)',
    'etc'=>'기타(직접 입력)'
  ];
}
function bwf_social_fields(){
  return [
    'instagram_url'=>'인스타그램','facebook_url'=>'페이스북','youtube_url'=>'유튜브',
    'naver_blog_url'=>'네이버 블로그','kakao_channel_url'=>'카카오 채널','tiktok_url'=>'틱톡'
  ];
}

/** 안전 출력/입력 */
function bwf_esc($v){ return esc_html(trim((string)$v)); }
function bwf_post($k,$default=''){ return isset($_POST[$k]) ? wp_unslash($_POST[$k]) : $default; }
function bwf_text($k,$default=''){ return sanitize_text_field(bwf_post($k,$default)); }
function bwf_textarea($k,$default=''){ return sanitize_textarea_field(bwf_post($k,$default)); }

/** 휴대폰 하이픈 자동 포맷 */
function bwf_format_phone($digits){
  $n = preg_replace('/\D+/','',$digits);
  if (strpos($n,'02') === 0) { // 서울번호 예외
    if (strlen($n) >= 10) return preg_replace('/^(\d{2})(\d{4})(\d{4}).*/','$1-$2-$3',$n);
    if (strlen($n) >= 9)  return preg_replace('/^(\d{2})(\d{3})(\d{4}).*/','$1-$2-$3',$n);
  }
  // 010 등
  if (strlen($n) >= 11) return preg_replace('/^(\d{3})(\d{4})(\d{4}).*/','$1-$2-$3',$n);
  if (strlen($n) >= 10) return preg_replace('/^(\d{3})(\d{3})(\d{4}).*/','$1-$2-$3',$n);
  return $n;
}
