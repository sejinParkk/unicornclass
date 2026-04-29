<?php
// $faqs, $categories, $category 가 컨트롤러에서 전달됨
$currentCat = $category ?: 'all';
?>

<div class="sub_index">
  <div class="inner">
    <!-- 서브 배너 -->
    <?php require VIEW_PATH . '/components/cs-banner.php'; ?>

    <div class="sub_page_flex">
      <!-- 서브 메뉴 -->
      <?php require VIEW_PATH . '/components/cs-subnav.php'; ?>

      <div class="sub_page_contents faq-wrap">
        <div class="page-section-title">FAQ</div>

        <!-- 카테고리 필터 -->
        <div class="faq-filters">
          <?php foreach ($categories as $key => $label): ?>
          <a href="/supports/faqs?category=<?= htmlspecialchars($key) ?>"
            class="faq-filter-btn <?= $currentCat === $key ? 'active' : '' ?>">
            <?= htmlspecialchars($label) ?>
          </a>
          <?php endforeach; ?>
        </div>

        <!-- FAQ 목록 -->
        <div class="faq-list">
          <?php if (empty($faqs)): ?>
          <div class="lecture-empty faq-empty">등록된 FAQ가 없습니다.</div>
          <?php else: ?>
          <?php foreach ($faqs as $faq): ?>
          <div class="faq-item" data-faq-idx="<?= $faq['faq_idx'] ?>">
            <div class="faq-q" onclick="toggleFaq(this)">              
              <div class="faq-q-text"><?= htmlspecialchars($faq['question']) ?></div>
              <span class="faq-toggle"></span>
            </div>
            <div class="faq-a-wrap">
              <div class="faq-a">
                <p class="faq-a-text"><?= nl2br(htmlspecialchars($faq['answer'])) ?></p>
              </div>
            </div>
          </div>
          <?php endforeach; ?>
          <?php endif; ?>
        </div>

        <!-- 1:1 문의 유도 배너 -->
        <div class="faq-cta">
          <div class="faq-cta-text">
            <h4>원하시는 답변을 찾지 못하셨나요?</h4>
            <p>1:1 문의를 남겨주시면 빠르게 답변드리겠습니다.</p>
          </div>
          <a href="/mypage/qna" class="faq-cta-btn">1:1 문의하기</a>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
function toggleFaq(qEl) {
  const item = qEl.closest('.faq-item');
  const isOpen = item.classList.contains('open');
  // 모두 닫기
  document.querySelectorAll('.faq-item.open').forEach(el => el.classList.remove('open'));
  if (!isOpen) item.classList.add('open');
}
// URL 파라미터에 open 항목 자동 열기 지원 (선택)
</script>
