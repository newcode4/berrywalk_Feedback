<?php
/**
 * 대표 질문지 저장 + 텔레그램 알림(디버그 포함)
 */
if (!defined('ABSPATH')) exit;

/* 혹시 남아있을지 모르는 이전 핸들러 제거 */
remove_action('admin_post_bwf_owner_save',     'bwf_owner_handle_save');
remove_action('admin_post_nopriv_bwf_owner_save','bwf_owner_handle_save');

/* 저장 엔드포인트 */
add_action('admin_post_bwf_owner_save',      'bwf_owner_save');
add_action('admin_post_nopriv_bwf_owner_save','bwf_owner_save');

function bwf_owner_save(){
  if (!is_user_logged_in()) { wp_safe_redirect(home_url('/')); exit; }
  check_admin_referer('bwf_owner_save','bwf_owner_nonce');

  $uid = get_current_user_id();

  /** 중복 제출 잠금(8초) */
  $lock_key = 'bwf_owner_lock_'.$uid;
  if (get_transient($lock_key)) { wp_safe_redirect(home_url('/my-questions/')); exit; }
  set_transient($lock_key, 1, 8);

  /** 폼 값 */
  $post_id = (int)($_POST['post_id'] ?? 0);
  $src     = (array)($_POST['q'] ?? []);

  /** 질문 정의/검증 */
  $cfg = function_exists('bwf_owner_cfg') ? bwf_owner_cfg() : ['qs'=>[], 'min'=>200];
  $min = (int)($cfg['min'] ?? 200);
  $ans = []; $err = [];

  foreach ($cfg['qs'] as $q) {
    $id   = $q['id'];
    $type = $q['type'] ?? 'textarea';
    $req  = !empty($q['required']);
    $m    = (int)($q['min'] ?? $min);

    if ($type === 'group') {
      $ans[$id] = [];
      foreach (($q['sub'] ?? []) as $sub) {
        $sid = $sub['id'];
        $v   = trim((string)($src[$id][$sid] ?? ''));
        $ans[$id][$sid] = sanitize_textarea_field($v);
        if (!empty($sub['required']) && $v==='') { $err[$id][$sid] = true; }
      }
    } else {
      $v = trim((string)($src[$id] ?? ''));
      $ans[$id] = sanitize_textarea_field($v);
      if ($req && mb_strlen($v) < $m) { $err[$id] = true; }
    }
  }

  /** 검증 실패 → 폼으로 되돌리기 */
  if (!empty($err)) {
    set_transient('bwf_owner_old_'.$uid, $src, 60);
    set_transient('bwf_owner_err_'.$uid, $err, 60);
    delete_transient($lock_key);
    wp_safe_redirect(wp_get_referer() ?: home_url('/owner-questions/'));
    exit;
  }

  /** 제목 규칙: "회사명 - #N 피드백" (새로 만들 때만 계산) */
  $company = trim((string)get_user_meta($uid,'bw_company_name',true));
  if ($company==='') $company = wp_get_current_user()->display_name;

  $is_new = ($post_id === 0);
  if ($is_new) {
    $count = (int) (new WP_Query([
      'post_type'      => 'bwf_owner_answer',
      'post_status'    => 'any',
      'author'         => $uid,
      'fields'         => 'ids',
      'posts_per_page' => -1
    ]))->found_posts;
    $seq   = $count + 1;
    $title = sprintf('%s - #%d 피드백', $company, $seq);
  } else {
    $title = get_post_field('post_title', $post_id);
  }

  /** 저장/수정 */
  $postarr = [
    'post_type'   => 'bwf_owner_answer',
    'post_status' => 'private',
    'post_title'  => $title,
    'post_author' => $uid,
  ];
  if ($post_id) { $postarr['ID'] = $post_id; $pid = wp_update_post($postarr, true); }
  else          { $pid = wp_insert_post($postarr, true); }

  if (is_wp_error($pid)) { delete_transient($lock_key); wp_die($pid); }

  update_post_meta($pid, 'bwf_answers', $ans);

  // 번호 캐시(목록 보정용)
  if ($is_new && preg_match('~#(\d+)~', $title, $m)) {
    update_post_meta($pid, '_bw_seq_cache', (int)$m[1]);
  }

  /** 텔레그램 알림 */
  bwf_send_telegram_notice($pid, $uid, $is_new);

  delete_transient($lock_key);
  wp_safe_redirect( add_query_arg(['id'=>$pid], home_url('/my-question-view/')) );
  exit;
}


/* ===================== 텔레그램 전송 + 디버그 ===================== */
/**
 * 상수(둘 중 아무 세트나 사용 가능):
 *   define('BWF_TG_BOT',  '123456789:BOT_TOKEN');
 *   define('BWF_TG_CHAT', '@your_channel_or_-100xxxxxxxxxx');
 *  또는
 *   define('BW_TG_BOT_TOKEN', '123456789:BOT_TOKEN');
 *   define('BW_TG_CHAT_ID',   '@your_channel_or_-100xxxxxxxxxx');
 */

function bwf__tg_conf(){
  $bot  = defined('BWF_TG_BOT')       ? BWF_TG_BOT
        : (defined('BW_TG_BOT_TOKEN') ? BW_TG_BOT_TOKEN : '');
  $chat = defined('BWF_TG_CHAT')      ? BWF_TG_CHAT
        : (defined('BW_TG_CHAT_ID')   ? BW_TG_CHAT_ID   : '');
  return [$bot, $chat];
}
function bwf__tg_log($data){
  set_transient('bwf_tg_debug', $data, 120);
  error_log('[BWF_TG] '.print_r($data, true));
}

/** 전송 */
function bwf_send_telegram_notice($post_id, $user_id, $is_new){
  list($BOT, $CHAT_RAW) = bwf__tg_conf();
  if (!$BOT || !$CHAT_RAW) { bwf__tg_log(['stage'=>'skip','reason'=>'missing_bot_or_chat']); return; }

  // chat_id 정규화
  $chat = trim($CHAT_RAW);
  if (preg_match('~t\.me/([A-Za-z0-9_]+)$~i', $chat, $m)) $chat = '@'.$m[1];
  if ($chat !== '' && $chat[0] !== '@' && $chat[0] !== '-' && !preg_match('~^-?\d+$~', $chat)) $chat = '@'.$chat;
  if (preg_match('~bot$~i', $chat)) { bwf__tg_log(['stage'=>'abort','reason'=>'chat_is_bot_username','chat'=>$chat]); return; }

  $u       = get_userdata($user_id);
  $company = get_user_meta($user_id,'bw_company_name',true);
  $phone   = get_user_meta($user_id,'bw_phone',true);
  $title   = $post_id ? get_post_field('post_title', $post_id) : '테스트';
  $url     = $post_id ? add_query_arg(['id'=>$post_id], home_url('/my-question-view/')) : home_url('/');
  $verb    = $is_new ? '새 피드백 작성' : '피드백 수정';

  $lines = [
    "✅ {$verb}",
    "제목: {$title}",
    "대표: {$u->display_name} (@{$u->user_login})",
    "회사: " . ($company ?: '—'),
    "연락처: " . ($phone ?: '—'),
    "보기: {$url}",
  ];
  $text = implode("\n", $lines);

  $endpoint = 'https://api.telegram.org/bot'.$BOT.'/sendMessage';
  $args = [
    'timeout' => 15,
    'headers' => ['Accept'=>'application/json'],
    'body'    => [
      'chat_id'                 => $chat,
      'text'                    => $text,
      'parse_mode'              => 'HTML',
      'disable_web_page_preview'=> true,
    ]
  ];

  $res = wp_remote_post($endpoint, $args);

  if (is_wp_error($res)) {
    bwf__tg_log(['stage'=>'request','ok'=>false,'error'=>$res->get_error_message(),'chat'=>$chat]);
  } else {
    $code = wp_remote_retrieve_response_code($res);
    $body = wp_remote_retrieve_body($res);
    $json = json_decode($body, true);
    bwf__tg_log(['stage'=>'request','ok'=>($code===200 && !empty($json['ok'])),'code'=>$code,'chat'=>$chat,'body'=>$body]);
  }
}

/** 관리자에게 1회성 디버그 알림 */
add_action('admin_notices', function(){
  if (!current_user_can('manage_options')) return;
  $d = get_transient('bwf_tg_debug'); if(!$d) return;
  delete_transient('bwf_tg_debug');
  echo '<div class="notice notice-info"><p><strong>텔레그램 전송 디버그</strong></p><pre style="white-space:pre-wrap">';
  echo esc_html(print_r($d, true));
  echo '</pre></div>';
});

/** 전송 테스트: /wp-admin/?bw_tg_test=1 */
add_action('admin_init', function(){
  if (current_user_can('manage_options') && isset($_GET['bw_tg_test'])) {
    bwf_send_telegram_notice(0, get_current_user_id(), true);
    wp_die('텔레그램 테스트 전송 시도 완료 — 관리자 알림 또는 PHP error_log에서 결과 확인');
  }
});
