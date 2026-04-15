<?php
// $term 이 컨트롤러에서 전달됨 (없으면 null)
?>

<?php if (empty($ajaxMode)): ?>
<div class="cs-banner">
  <div class="cs-banner-inner">
    <div class="cs-banner-en">Customer Support</div>
    <div class="cs-banner-title">고객<span>센터</span></div>
  </div>
</div>

<div class="cs-subnav">
  <a href="/supports/faqs"    class="cs-subnav-item">자주묻는질문</a>
  <a href="/supports/notices" class="cs-subnav-item">공지사항</a>
  <a href="/supports/terms"   class="cs-subnav-item active">이용약관</a>
  <a href="/supports/privacy" class="cs-subnav-item">개인정보처리방침</a>
</div>
<?php endif; ?>

<div class="policy-wrap">
  <div class="page-section-title">이용약관</div>

  <?php if ($term && !empty($term['content'])): ?>
  <?php if (!empty($term['updated_at'])): ?>
  <div class="policy-meta">최종 수정일: <?= date('Y년 m월 d일', strtotime($term['updated_at'])) ?></div>
  <?php endif; ?>
  <div class="policy-body">
    <?= $term['content'] ?>
  </div>
  <?php else: ?>
  <div class="policy-empty">이용약관이 아직 등록되지 않았습니다.</div>
  <?php endif; ?>
</div>
