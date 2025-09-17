<?php
if (!defined('ABSPATH')) exit;

add_shortcode('bw_my_questions', function(){
  if (!is_user_logged_in()) return '<div class="bwf-form"><p>로그인 후 이용해주세요.</p></div>';
  wp_enqueue_style('bwf-forms');

  $uid  = get_current_user_id();
  $page = max(1, intval($_GET['pg'] ?? 1));
  $per  = 10;

  $q = new WP_Query([
    'post_type'      => 'bwf_owner_answer',
    'author'         => $uid,
    'posts_per_page' => $per,
    'paged'          => $page,
    'orderby'        => 'date',
    'order'          => 'DESC',
  ]);

  ob_start();
  echo '<style>.bwf-topwrap{display:none!important}</style>';  // 이 페이지에서만 진행현황 숨김

  echo '<div class="bwf-form"><h2>내 질문 저장본</h2>';

  if (!$q->have_posts()){
    echo '<p>아직 저장한 질문이 없습니다.</p></div>'; return ob_get_clean();
  }

  echo '<ul class="bwf-list">';
  while ($q->have_posts()){ $q->the_post();
    $pid  = get_the_ID();
    $ans  = (array) get_post_meta($pid,'bwf_answers', true);

    // 요약(문자만 뽑기)
    $flat = [];
    $stack = [$ans];
    while ($stack) {
      $cur = array_pop($stack);
      foreach ($cur as $v) {
        if (is_array($v)) $stack[] = $v;
        else $flat[] = trim((string)$v);
      }
    }
    $short = mb_substr(implode(' ', array_filter($flat)), 0, 140) . (count($flat) ? '…' : '');

    $view = esc_url( add_query_arg(['id'=>$pid], home_url('/my-question-view/')) ); // [bwf_owner_view id=".."] 페이지에 매핑
    $del  = esc_url( wp_nonce_url( admin_url('admin-post.php?action=bwf_owner_delete&id='.$pid),
                                   'bwf_owner_delete_'.$pid ) );

    $u = get_post_time('U', true, $pid);           // GMT 기준 타임스탬프
    $time_local = wp_date('Y-m-d H:i', $u);        // 사이트 타임존으로 포맷

    echo '<li>';
    echo '<span class="bwf-time">'.esc_html($time_local).'</span>';
    echo '<span class="bwf-sum">'.esc_html($short).'</span> ';
    echo '<a class="bwf-btn-secondary" href="'.$view.'">보기</a> ';
    echo '<a class="bwf-btn-secondary" href="'.$del.'" onclick="return confirm(\'삭제할까요?\')">삭제</a>';
    echo '</li>';
  }
  wp_reset_postdata();
  echo '</ul>';

  // 페이지네이션
  $maxp = max(1, intval($q->max_num_pages));
  echo '<div class="bwf-pager">';
  if ($page>1) echo '<a class="bwf-btn-secondary" href="'.esc_url(add_query_arg('pg',$page-1)).'">이전</a>';
  echo '<span class="bwf-page"> '.$page.' / '.$maxp.' </span>';
  if ($page<$maxp) echo '<a class="bwf-btn-secondary" href="'.esc_url(add_query_arg('pg',$page+1)).'">다음</a>';
  echo '</div>';

  echo "<p style='margin-top:14px'><a class='bwf-btn' href='".esc_url(home_url('/owner-questions/'))."'>새 질문 작성</a></p></div>";
  return ob_get_clean();
});

add_action('admin_post_bwf_owner_delete', function(){
  if (!is_user_logged_in()) wp_safe_redirect( home_url('/') );
  $pid = absint($_GET['id'] ?? 0);
  if (!$pid || get_post_type($pid) !== 'bwf_owner_answer') wp_safe_redirect( home_url('/') );

  $nonce_ok = isset($_GET['_wpnonce']) && wp_verify_nonce($_GET['_wpnonce'], 'bwf_owner_delete_'.$pid);
  if (!$nonce_ok) wp_safe_redirect( home_url('/') );

  $author = (int) get_post_field('post_author', $pid);
  if ( get_current_user_id() === $author || current_user_can('delete_others_posts') ) {
    wp_delete_post($pid, true);
  }
  wp_safe_redirect( wp_get_referer() ?: home_url('/my-questions/') ); exit;
});
