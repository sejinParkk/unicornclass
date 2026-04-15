<?php
// $faqs, $categories, $category 가 컨트롤러에서 전달됨
$currentCat = $category ?: 'all';
?>

<!-- 서브 배너 -->
<div class="cs-banner">
  <div class="cs-banner-inner">
    <div class="cs-banner-en">Customer Support</div>
    <div class="cs-banner-title">고객<span>센터</span></div>
  </div>
</div>

<!-- 서브 메뉴 -->
<div class="cs-subnav">
  <a href="/supports/faqs"    class="cs-subnav-item active">자주묻는질문</a>
  <a href="/supports/notices" class="cs-subnav-item">공지사항</a>
  <a href="/supports/terms"   class="cs-subnav-item">이용약관</a>
  <a href="/supports/privacy" class="cs-subnav-item">개인정보처리방침</a>
</div>

<div class="faq-wrap">
  <div class="page-section-title">자주 묻는 질문</div>

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
    <div class="faq-empty">등록된 FAQ가 없습니다.</div>
    <?php else: ?>
    <?php foreach ($faqs as $faq): ?>
    <div class="faq-item" data-faq-idx="<?= $faq['faq_idx'] ?>">
      <div class="faq-q" onclick="toggleFaq(this)">
        <span class="faq-q-badge">Q</span>
        <span class="faq-q-text"><?= htmlspecialchars($faq['question']) ?></span>
        <span class="faq-toggle">
          <svg width="10" height="10" viewBox="0 0 10 10" fill="none" stroke="#aaa" stroke-width="2">
            <line x1="5" y1="1" x2="5" y2="9"/>
            <line x1="1" y1="5" x2="9" y2="5"/>
          </svg>
        </span>
      </div>
      <div class="faq-a-wrap">
        <div class="faq-a">
          <span class="faq-a-badge">A</span>
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
