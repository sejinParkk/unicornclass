<?php
/**
 * 개인정보처리방침
 * @var array|null $term      현재 표시할 버전 (null 가능)
 * @var array      $versions  모든 버전 목록 [{terms_idx, title, effective_at, is_current}]
 * @var bool       $ajaxMode  팝업형 여부
 */
?>

<?php if (empty($ajaxMode)): ?>

  <div class="sub_index">
    <div class="inner">
      <!-- 서브 배너 -->
      <?php require VIEW_PATH . '/components/cs-banner.php'; ?>

      <div class="sub_page_flex">
        <!-- 서브 메뉴 -->
        <?php require VIEW_PATH . '/components/cs-subnav.php'; ?>

        <div class="policy-wrap">
          <div class="page-section-title">개인정보처리방침</div>

          <?php if ($term): ?>
          <!-- 헤더: 시행일 + 버전 선택 -->
          <div class="notice-detail-header policy_header">
            <p>시행일 <?= date('Y년 m월 d일', strtotime($term['effective_at'])) ?></p>
            <?php if (count($versions) > 1): ?>
            <select onchange="location.href=this.value">
              <?php foreach ($versions as $v):
                $isSelected = (int)$v['terms_idx'] === (int)$term['terms_idx'];
                $label = date('y.m.d', strtotime($v['effective_at']));
                if ($v['is_current']) $label .= ' (현재)';
                $url = $v['is_current'] ? '/supports/privacy' : '/supports/privacy?ver=' . $v['terms_idx'];
              ?>
              <option value="<?= $url ?>" <?= $isSelected ? 'selected' : '' ?>>
                <?= htmlspecialchars($label) ?>
              </option>
              <?php endforeach; ?>
            </select>
            <?php endif; ?>
          </div>

          <!-- 내용 -->
          <div class="policy_content">
            <?= $term['content'] ?>
          </div>

          <?php else: ?>
          <div class="policy-empty">개인정보처리방침이 아직 등록되지 않았습니다.</div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

<?php else: ?>

  <!-- 팝업형: 최신(현재) 버전만 표시 -->
  <div class="policy-wrap">
    <?php if ($term && !empty($term['content'])): ?>
    <div class="policy-meta">시행일: <?= date('Y년 m월 d일', strtotime($term['effective_at'])) ?></div>
    <div class="policy-body">
      <?= $term['content'] ?>
    </div>
    <?php else: ?>
    <div class="policy-empty">개인정보처리방침이 아직 등록되지 않았습니다.</div>
    <?php endif; ?>
  </div>

<?php endif; ?>
