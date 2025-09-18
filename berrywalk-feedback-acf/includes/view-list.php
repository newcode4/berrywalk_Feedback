<?php
/* /includes/view-list.php — 단건 보기 + 마이페이지 목록 */
if (!defined('ABSPATH')) exit;

/* 공통 URL 헬퍼 (중복 선언 방지) */
if (!function_exists('bwf_owner_view_url')) { function bwf_owner_view_url(){ return home_url('/my-question-view/'); } }
if (!function_exists('bwf_owner_edit_url')) { function bwf_owner_edit_url(){ return home_url('/owner-edit/'); } }
if (!function_exists('bwf_owner_list_url')) { function bwf_owner_list_url(){ return home_url('/my-questions/'); } }

/** 단건 보기: [bw_owner_view] / [bwf_owner_view] */
function bwos_render_single_view($atts){
  if (!is_user_logged_in()) return '<div class="bwf-form">로그인이 필요합니다.</div>';

  $id = (int)($atts['id'] ?? ($_GET['id'] ?? 0));
  $p  = get_post($id);
  if(!$p || $p->post_type !== 'bwf_owner_answer') return '<div class="bwf-form">데이터가 없습니다.</div>';
  if((int)$p->post_author !== get_current_user_id() && !current_user_can('read_post', $id)) return '<div class="bwf-form">권한이 없습니다.</div>';

  wp_enqueue_style('bwf-forms');

  // 질문 정의 & 답변
  $cfg = function_exists('bwf_owner_cfg') ? bwf_owner_cfg() : ['title'=>'대표 질문지','qs'=>[]];
  $ans = (array)get_post_meta($id, 'bwf_answers', true);

  // 작성자 메타(요청에 따라 최소 표시만)
  $au   = get_userdata($p->post_author);
  $meta = [
    '회사명'   => get_user_meta($p->post_author,'bw_company_name',true),
    '웹사이트' => $au && $au->user_url ? $au->user_url : '',
    '연락시간' => get_user_meta($p->post_author,'bw_contact_window',true),
    '휴대폰'   => get_user_meta($p->post_author,'bw_phone',true),
  ];

  ob_start(); ?>
  <div class="bwos-wrap">
    <h2 class="bwf-title"><?php echo esc_html($cfg['title']); ?></h2>

    <div class="bwf-card" style="margin-bottom:16px">
      <strong>작성 정보</strong>
      <div style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:6px 14px;margin-top:8px">
        <?php foreach($meta as $k=>$v): ?>
          <div>
            <span style="color:#64748b"><?php echo esc_html($k); ?></span> :
            <?php
              if ($k==='웹사이트' && $v) {
                echo '<a href="'.esc_url($v).'" target="_blank" rel="noopener">'.esc_html($v).'</a>';
              } else {
                echo $v ? esc_html($v) : '—';
              }
            ?>
          </div>
        <?php endforeach; ?>
      </div>
    </div>

    <?php foreach(($cfg['qs'] ?? []) as $q):
      $qid = $q['id'] ?? '';
      if(!$qid) continue; ?>
      <div class="bwf-field">
        <label><?php echo esc_html($q['label']); ?></label>
        <?php if(($q['type'] ?? '') === 'group'): ?>
          <?php foreach(($q['sub'] ?? []) as $sub):
            $sid = $sub['id']; $v = trim((string)($ans[$qid][$sid] ?? '')); ?>
            <div class="bwf-sub">
              <div class="bwf-help"><?php echo esc_html($sub['label']); ?></div>
              <div class="bwf-card"><?php echo nl2br(esc_html($v)); ?></div>
            </div>
          <?php endforeach; ?>
        <?php else:
          $v = trim((string)($ans[$qid] ?? '')); ?>
          <div class="bwf-card"><?php echo nl2br(esc_html($v)); ?></div>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>

    <div class="bwf-actions" style="justify-content:center">
      <a class="bwf-btn" href="<?php echo esc_url(add_query_arg(['id'=>$p->ID], bwf_owner_edit_url())); ?>">수정</a>
      <a class="bwf-btn-secondary" href="<?php echo esc_url(bwf_owner_list_url()); ?>">목록</a>
    </div>
  </div>
  <?php
  return ob_get_clean();
}
add_shortcode('bw_owner_view','bwos_render_single_view');
add_shortcode('bwf_owner_view','bwos_render_single_view'); // alias

/** 마이페이지 목록: [bw_owner_list] / [bw_my_questions] */
add_shortcode('bw_owner_list', function($atts){
  if (!is_user_logged_in()) return '<div class="bwf-form">로그인이 필요합니다.</div>';

  $paged = max(1, (int)get_query_var('paged', 0));
  if(!$paged) $paged = max(1, (int)($_GET['pg'] ?? 1));

  $q = new WP_Query([
    'post_type'      => 'bwf_owner_answer',
    'post_status'    => 'private',
    'author'         => get_current_user_id(),
    'posts_per_page' => 8,
    'orderby'        => 'date',
    'order'          => 'DESC',
    'paged'          => $paged,
    'fields'         => 'ids'
  ]);

  wp_enqueue_style('bwf-forms');

  // 사용자 회사명
  $uid     = get_current_user_id();
  $company = trim((string)get_user_meta($uid,'bw_company_name',true));
  if($company==='') $company = wp_get_current_user()->display_name;

  ob_start(); ?>
  <div class="bwos-wrap">
    <h2 class="bwf-title" style="margin-bottom:16px">마이페이지</h2>

    <div class="bwf-actions" style="justify-content:flex-start;margin:0 0 14px">
      <a class="bwf-btn" href="<?php echo esc_url(home_url('/owner-questions/')); ?>">새 피드백 질문하기</a>
    </div>

    <div class="bwos-list">
      <?php
      if ($q->have_posts()):
        foreach ($q->posts as $pid):
          // 제목은 저장된 raw post_title 사용(‘비공개:’ 접두 회피)
          $raw_title = get_post_field('post_title', $pid);

          // 혹시 과거 글(구 규칙)이라면 회사명 - #N 피드백으로 보정
          if (strpos($raw_title,'#') === false || strpos($raw_title,'피드백') === false) {
            // 최신글부터 역순이지만, 사용자는 번호만 구분하면 되므로 글ID 끝 3자리 정도를 보조로 표기할 수도 있음
            // 여기서는 간단히 저장 시각 기준 시퀀스를 표시
            $seq = (int) get_post_meta($pid, '_bw_seq_cache', true);
            if ($seq <= 0) {
              // 없으면 임시 계산: 내 글 전부에서 현재 PID의 역순 인덱스
              $all = new WP_Query([
                'post_type'=>'bwf_owner_answer','post_status'=>'any','author'=>$uid,
                'orderby'=>'date','order'=>'ASC','fields'=>'ids','posts_per_page'=>-1
              ]);
              $seq = 0;
              foreach ($all->posts as $i=>$id) { if ($id == $pid) { $seq = $i+1; break; } }
            }
            $display = sprintf('%s - #%d 피드백', $company, max(1,$seq));
          } else {
            $display = $raw_title;
          }

          $when = get_the_date('Y-m-d H:i', $pid);
          $view = esc_url(add_query_arg(['id'=>$pid], bwf_owner_view_url()));
          $edit = esc_url(add_query_arg(['id'=>$pid], bwf_owner_edit_url()));
      ?>
        <div class="bwos-card">
          <h4><?php echo esc_html($display); ?></h4>
          <div class="meta"><?php echo esc_html($when); ?></div>
          <div class="row">
            <a href="<?php echo $view; ?>">보기</a>
            <a href="<?php echo $edit; ?>">수정</a>
          </div>
        </div>
      <?php
        endforeach;
      else: ?>
        <p>작성한 내역이 없습니다. <a href="<?php echo esc_url(home_url('/owner-questions/')); ?>">새 피드백 질문하기</a>로 첫 저장본을 만들어 보세요.</p>
      <?php endif; ?>
    </div>

    <?php
    $links = paginate_links([
      'total'     => max(1,(int)$q->max_num_pages),
      'current'   => $paged,
      'type'      => 'array',
      'mid_size'  => 2,
      'prev_text' => '이전',
      'next_text' => '다음'
    ]);
    if ($links) {
      echo '<div class="bwos-pg">';
      foreach($links as $l) echo $l.' ';
      echo '</div>';
    }
    wp_reset_postdata();
    ?>
  </div>
  <?php
  return ob_get_clean();
});
add_shortcode('bw_my_questions', fn($a)=>do_shortcode('[bw_owner_list]'));
