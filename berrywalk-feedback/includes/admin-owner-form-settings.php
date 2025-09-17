<?php
if (!defined('ABSPATH')) exit;

/** 기본값 */
function bwf_owner_default_config(){
  return [
    'min_length' => 200,                 // 개별 문항 최소 글자수(ask3 제외)
    'title'      => '대표님 핵심 질문지',
    'intro'      => '사업의 본질을 파악하고 고객에게 정말 묻고 싶은 질문을 구체화합니다. 각 문항은 <strong>최소 {MIN}자</strong>입니다. <em>(단, <strong>“고객에게 물어보고 싶은 3가지”</strong>는 글자수 제한 없음)</em>',
    // 1
    'q_problem'  => [
      'label' => '1. 지금, 우리 사업의 가장 큰 고민은 무엇인가요?',
      'desc'  => '설명: 현재 가장 답답하거나 성장이 정체되었다고 느끼는 구체적인 상황을 한두 문장으로 설명해 주세요. 예를 들어, “광고는 많이 했는데, 실제 구매로 이어지는 비율이 너무 낮습니다” 또는 “고객 문의는 많은데, 대부분 가격만 물어보고 결제하지 않아요”와 같이 <strong>숫자</strong>나 <strong>행동</strong>을 기반으로 고민을 말씀해 주시면 좋습니다.',
      'examples' => '<ul>
<li>“최근 인스타그램 광고 효율이 너무 안 나와요. 클릭은 많은데, 저희 웹사이트에 1분도 머물지 않고 나가는 사람이 많아요.”</li>
<li>“기존 고객들의 재구매율이 낮아서, 항상 새로운 고객을 찾아야 하는 부담이 큽니다. 단골 고객을 어떻게 만들어야 할지 모르겠습니다.”</li>
</ul>',
    ],
    // 2
    'q_value'    => [
      'label' => '2. 우리 서비스는 고객의 ‘어떤 문제’를 해결해주고 있나요?',
      'desc'  => '설명: 고객이 우리 서비스를 만나기 전에는 어떤 어려움을 겪었고, 이용한 후에는 어떤 변화가 있었는지 떠올려 보세요. 이 서비스를 통해 고객이 얻게 되는 <strong>가장 중요한 가치</strong>를 한 문장으로 압축해 주세요.',
      'examples' => '<ol>
<li>“번거로운 서류 작업을 단 5분 만에 자동화하여, 자영업자들이 본업에만 집중할 수 있도록 돕습니다.”</li>
<li>“매번 배달 음식에 지쳐있던 바쁜 직장인에게 집에서 갓 만든 것 같은 건강한 한 끼를 제공합니다.”</li>
</ol>',
    ],
    // 3
    'q_ideal'    => [
      'label' => '3. 우리 서비스를 ‘누가’ 이용해야 하나요? 왜 우리를 선택하나요?',
      'desc'  => '설명: 우리 서비스가 가장 완벽하게 해결해 줄 수 있는 사람은 누구인가요? 그들의 특징(나이, 직업, 관심사)을 구체적으로 설명하고, 그들이 여러 선택지 중 우리를 골라야 하는 <strong>결정적인 이유</strong>를 한 가지 생각해 보세요.',
      'examples' => '<p>“운동은 하고 싶지만 헬스장에 갈 시간과 돈이 부족한 <strong>집순이 20대 대학생</strong>입니다. 왜냐하면 저렴한 구독료로 집에서 15분 만에 끝낼 수 있는 홈트레이닝 영상을 제공하기 때문입니다.”</p>',
    ],
    // 4 (ask3 그룹)
    'q_ask3'     => [
      'label' => '4. 고객에게 물어보고 싶은 3가지',
      'desc'  => '이 질문은 대표님께서 고객에게 직접적으로 가장 궁금해하는 점을 파악하여, 사업 성장을 위한 핵심 인사이트를 얻는 과정입니다. 아래 예시를 참고하여, 현재 가장 해결하고 싶은 고민에 대한 질문 3가지를 직접 작성해 주세요. 핵심은 고객이 실제로 <strong>경험한 내용</strong>에 대해 질문하는 것입니다. 피드백 제공자가 질문에 답하기 위해 별도의 시간이나 노력을 들이지 않고, 직접 경험한 내용을 바탕으로 솔직하게 답변할 수 있도록 <strong>구체적인 상황</strong>을 제시하는 것이 중요합니다.',
      'examples' => '<p><strong>피드백 대상이 ‘상품’일 경우</strong></p>
<ul>
  <li>“저희가 보내드린 상품을 처음 받으셨을 때 어떤 느낌이셨나요? (포장, 디자인, 첫 사용 경험 등)”</li>
  <li>“상품을 사용하시면서 가장 만족스러웠던 부분은 무엇이었나요? 반대로, ‘이 점은 조금 아쉽다’고 생각했던 부분이 있다면 무엇이었나요?”</li>
</ul>
<p><strong>피드백 대상이 ‘서비스’일 경우</strong></p>
<ul>
  <li>“저희 서비스의 [무료체험 기간]을 이용하셨을 때, 가장 좋았던 점과 아쉬웠던 점은 무엇이었나요?”</li>
</ul>
<p><strong>고민별 질문 예시 (참고)</strong></p>
<ul>
  <li>광고 효율 낮음 → “최근 저희가 진행한 <strong>[인스타그램 광고]</strong>를 보셨을 때, 어떤 점이 가장 인상적이었나요? 그리고 저희 웹사이트에 방문하셨다가 구매를 망설이게 한 이유가 있다면 무엇이었나요?”</li>
  <li>재구매율 낮음 → “저희 서비스를 이용하신 후, 다른 서비스를 다시 이용하셨다면 그 이유는 무엇이었나요? 저희 서비스가 제공하지 못했던 가치는 무엇이었나요?”</li>
  <li>차별점 불분명 → “저희와 비슷한 다른 서비스(예: A사)를 이용해 보셨다면, 그 서비스에 비해 저희 서비스의 어떤 부분이 가장 좋거나 아쉬웠나요?”</li>
</ul>',
    ],
    // 5 (one_question 제거, 경쟁사만 남김)
    'q_competitors' => [
      'label' => '5. 현재 경쟁사는 어디이며, 그들과의 차별점은 무엇이라고 생각하시나요?',
      'desc'  => '설명: 시장에 존재하는 주요 경쟁사 1~2곳을 언급하고, 그들과 비교했을 때 우리 서비스의 강점과 약점은 무엇이라고 생각하시나요?',
      'examples' => '<p>“A사는 가격이 저렴하지만 품질이 낮고, B사는 품질은 좋지만 가격이 너무 비쌉니다. 저희는 A와 B 사이에서 적정한 가격에 높은 만족도를 제공합니다.”</p>',
    ],
  ];
}

/** 옵션 등록 & 메뉴 */
add_action('admin_init', function(){
  register_setting('bwf_owner_settings', 'bwf_owner_config', [
    'type' => 'array',
    'sanitize_callback' => function($input){
      $def = bwf_owner_default_config();
      $out = $def;

      $out['min_length'] = max(0, intval($input['min_length'] ?? $def['min_length']));
      $out['title']      = wp_kses_post($input['title'] ?? $def['title']);
      $out['intro']      = wp_kses_post($input['intro'] ?? $def['intro']);

      foreach (['q_problem','q_value','q_ideal','q_ask3','q_competitors'] as $k){
        $o = $def[$k];
        $in = $input[$k] ?? [];
        $out[$k] = [
          'label'    => sanitize_text_field($in['label'] ?? $o['label']),
          'desc'     => wp_kses_post($in['desc'] ?? $o['desc']),
          'examples' => wp_kses_post($in['examples'] ?? $o['examples']),
        ];
      }
      return $out;
    }
  ]);
});

add_action('admin_menu', function(){
  add_submenu_page(
    'bwf_crm',
    '질문지 설정',
    '질문지 설정',
    'manage_options',
    'bwf_owner_settings',
    'bwf_owner_settings_page'
  );
});

/** 설정 화면 */
function bwf_owner_settings_page(){
  $opt = get_option('bwf_owner_config', bwf_owner_default_config());
  $field = function($group,$key,$label,$type='text') use (&$opt){
    $val = $opt[$group][$key] ?? '';
    if ($type==='textarea'){
      echo "<p><label><strong>{$label}</strong></label></p>";
      echo '<textarea name="bwf_owner_config['.$group.']['.$key.']" rows="5" style="width:100%">' . esc_textarea($val) . '</textarea>';
    } else {
      echo "<p><label><strong>{$label}</strong></label><br>";
      echo '<input type="text" name="bwf_owner_config['.$group.']['.$key.']" value="'.esc_attr($val).'" style="width:100%"></p>';
    }
  };
  ?>
  <div class="wrap">
    <h1>대표 질문지 설정</h1>
    <form method="post" action="options.php">
      <?php settings_fields('bwf_owner_settings'); ?>

      <h2>공통</h2>
      <p><label><strong>최소 글자수(ask3 제외)</strong></label><br>
        <input type="number" name="bwf_owner_config[min_length]" value="<?php echo intval($opt['min_length']); ?>" min="0" style="width:120px"> 자
      </p>
      <p><label><strong>폼 제목</strong></label><br>
        <input type="text" name="bwf_owner_config[title]" value="<?php echo esc_attr($opt['title']); ?>" style="width:100%">
      </p>
      <p><label><strong>인트로(설명)</strong> — <em>{MIN}은 최소 글자수로 치환</em></label><br>
        <textarea name="bwf_owner_config[intro]" rows="3" style="width:100%"><?php echo esc_textarea($opt['intro']); ?></textarea>
      </p>

      <hr>

      <h2>문항 내용</h2>
      <?php
      foreach ([
        'q_problem'     => '문항 1: 사업의 가장 큰 고민',
        'q_value'       => '문항 2: 해결하는 고객 문제',
        'q_ideal'       => '문항 3: 타겟과 선택 이유',
        'q_ask3'        => '문항 4: 고객에게 물어볼 3가지',
        'q_competitors' => '문항 5: 경쟁사/차별점',
      ] as $k=>$title): ?>
        <div style="background:#fff;border:1px solid #e5e7eb;padding:12px 14px;border-radius:8px;margin:10px 0;">
          <h3 style="margin:4px 0 10px;"><?php echo esc_html($title); ?></h3>
          <?php
            $field($k,'label','라벨(제목)');
            $field($k,'desc','설명(HTML 가능)','textarea');
            $field($k,'examples','예시(HTML 가능)','textarea');
          ?>
        </div>
      <?php endforeach; ?>

      <?php submit_button('저장'); ?>
    </form>
  </div>
  <?php
}
