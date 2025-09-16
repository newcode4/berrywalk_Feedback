<?php
if (!defined('ABSPATH')) exit;

/**
 * [bw_owner_form]
 * - 대표가 핵심 질문지 작성 → 저장 → 피드백 링크 메일 발송
 */
add_shortcode('bw_owner_form', function($atts){
  wp_enqueue_style('bwf-forms');
  wp_enqueue_script('bwf-owner');
  wp_localize_script('bwf-owner','BWF_OWNER',[
    'ajax'=> admin_url('admin-ajax.php'),
    'nonce'=> wp_create_nonce('bwf_owner_submit')
  ]);

  ob_start(); ?>
  <form id="bwf-owner" class="bwf-form">
    <h3>대표님 핵심 질문지</h3>

    <div class="grid-2">
      <label>회사/서비스명
        <input name="company_name" required>
      </label>
      <label>업종
        <select name="industry" required>
          <option value="">선택</option>
          <option>교육</option><option>부동산</option><option>커머스</option>
          <option>콘텐츠/미디어</option><option>기타</option>
        </select>
      </label>

      <label>직원 수
        <select name="employees" required>
          <option value="">선택</option>
          <option>1~3</option><option>4~9</option><option>10~29</option><option>30+</option>
        </select>
      </label>
      <label>홈페이지 URL
        <input type="url" name="website_url" placeholder="https://example.com" required>
      </label>
    </div>

    <label>SNS/링크(쉼표 또는 JSON 배열)
      <textarea name="socials" placeholder='["https://instagram.com/...","https://youtube.com/..."]'></textarea>
    </label>

    <label>알게 된 경로
      <input name="discover_source" placeholder="유튜브/검색/지인/광고 등">
    </label>

    <hr>

    <label>현재 가장 큰 고민
      <textarea name="pain_points" required></textarea>
    </label>

    <label>핵심 가치(문제→가치 한 줄)
      <textarea name="value_prop" required></textarea>
    </label>

    <label>이상적 타겟/선택 이유
      <textarea name="ideal_customer" required></textarea>
    </label>

    <div class="grid-3">
      <label>맞춤 질문 1
        <textarea name="must_ask_q1" required></textarea>
      </label>
      <label>맞춤 질문 2
        <textarea name="must_ask_q2" required></textarea>
      </label>
      <label>맞춤 질문 3
        <textarea name="must_ask_q3" required></textarea>
      </label>
    </div>

    <button type="submit" class="bwf-btn">질문지 제출 및 링크 받기</button>
    <p class="bwf-hint">제출 후, 고객 피드백용 고유 링크가 관리자 이메일로 발송됩니다.</p>
  </form>
  <?php
  return ob_get_clean();
});


/**
 * [bw_feedback_form]
 * - 고객 서술형 피드백 (최소 100자)
 * - ref 토큰으로 대표 질문 자동 노출
 */
add_shortcode('bw_feedback_form', function($atts){
  $ref = sanitize_text_field($_GET['ref'] ?? '');
  if (!$ref) return '<div>유효하지 않은 접근입니다.</div>';

  global $wpdb;
  $rep = $wpdb->prefix.'bw_representatives';
  $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM $rep WHERE view_token=%s", $ref), ARRAY_A);
  if (!$row) return '<div>존재하지 않는 링크입니다.</div>';

  // 로그인 사용자 기본값 프리필(있으면)
  $u = wp_get_current_user();
  $pref_age    = $u->ID ? get_user_meta($u->ID,'age_range',true) : '';
  $pref_gender = $u->ID ? get_user_meta($u->ID,'gender',true) : '';

  wp_enqueue_style('bwf-forms');
  wp_enqueue_script('bwf-feedback');
  wp_localize_script('bwf-feedback','BWF_FB',[
    'ajax'=> admin_url('admin-ajax.php'),
    'nonce'=> wp_create_nonce('bwf_feedback_submit'),
    'ref'=> $ref,
    'minlen'=> 100
  ]);

  ob_start(); ?>
  <div class="bwf-card">
    <h3>피드백 설문</h3>
    <p class="muted">대표 질문 요약</p>
    <ul class="bwf-rep">
      <li><b>가장 큰 고민:</b> <?=esc_html($row['pain_points']);?></li>
      <li><b>핵심 가치(대표 생각):</b> <?=esc_html($row['value_prop']);?></li>
      <li><b>이상적 타겟:</b> <?=esc_html($row['ideal_customer']);?></li>
      <li><b>맞춤 질문 1:</b> <?=esc_html($row['must_ask_q1']);?></li>
      <li><b>맞춤 질문 2:</b> <?=esc_html($row['must_ask_q2']);?></li>
      <li><b>맞춤 질문 3:</b> <?=esc_html($row['must_ask_q3']);?></li>
    </ul>
  </div>

  <form id="bwf-feedback" class="bwf-form">
    <h4>응답자 기본정보</h4>
    <div class="grid-3">
      <label>연령대
        <select name="age_range" required>
          <option value="">선택</option>
          <option <?=$pref_age==='10s'?'selected':'';?> value="10s">10대</option>
          <option <?=$pref_age==='20s'?'selected':'';?> value="20s">20대</option>
          <option <?=$pref_age==='30s'?'selected':'';?> value="30s">30대</option>
          <option <?=$pref_age==='40s'?'selected':'';?> value="40s">40대</option>
          <option <?=$pref_age==='50s+'?'selected':'';?> value="50s+">50대+</option>
        </select>
      </label>
      <label>성별
        <select name="gender" required>
          <option value="">선택</option>
          <option <?=$pref_gender==='male'?'selected':'';?> value="male">남</option>
          <option <?=$pref_gender==='female'?'selected':'';?> value="female">여</option>
          <option <?=$pref_gender==='other'?'selected':'';?> value="other">기타/응답거부</option>
        </select>
      </label>
      <label>카테고리 구매경험
        <select name="category_experience" required>
          <option value="">선택</option>
          <option value="1">예</option>
          <option value="0">아니오</option>
        </select>
      </label>
    </div>

    <label>최초 유입 경로(예: 유튜브/검색/지인)
      <input name="discover_channel" required>
    </label>

    <hr>

    <h4>핵심 질문(서술형, 최소 100자)</h4>
    <label>① 첫인상/서비스 평가
      <textarea name="first_impression" required></textarea>
    </label>
    <label>② 타겟 적합성(대표 타겟과 실제 체감)
      <textarea name="target_fit" required></textarea>
    </label>
    <label>③ 경쟁사 비교/차별점
      <textarea name="competitor_compare" required></textarea>
    </label>
    <label>④ ‘꼭 사야 할 이유’/구매장애
      <textarea name="must_buy_reason" required></textarea>
    </label>
    <label>⑤ 추천 의향/개선 시 추천 가능성
      <textarea name="recommend_intent" required></textarea>
    </label>

    <h4>대표 맞춤 질문</h4>
    <label>Q1: <?=esc_html($row['must_ask_q1']);?>
      <textarea name="ans_owner_q1" required></textarea>
    </label>
    <label>Q2: <?=esc_html($row['must_ask_q2']);?>
      <textarea name="ans_owner_q2" required></textarea>
    </label>
    <label>Q3: <?=esc_html($row['must_ask_q3']);?>
      <textarea name="ans_owner_q3" required></textarea>
    </label>

    <button type="submit" class="bwf-btn">피드백 제출</button>
    <p class="bwf-hint">각 문항은 최소 100자 이상이 필요합니다.</p>
  </form>
  <?php
  return ob_get_clean();
});
