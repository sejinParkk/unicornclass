<?php
// $list, $total, $page, $pages, $limit 가 컨트롤러에서 전달됨
?>

<div class="cs-banner">
  <div class="cs-banner-inner">
    <div class="cs-banner-en">Customer Support</div>
    <div class="cs-banner-title">고객<span>센터</span></div>
  </div>
</div>

<div class="cs-subnav">
  <a href="/supports/faqs"    class="cs-subnav-item">자주묻는질문</a>
  <a href="/supports/notices" class="cs-subnav-item active">공지사항</a>
  <a href="/supports/terms"   class="cs-subnav-item">이용약관</a>
  <a href="/supports/privacy" class="cs-subnav-item">개인정보처리방침</a>
</div>

<div class="notice-wrap">
  <div class="page-section-title">공지사항</div>

  <div class="notice-list">
    <?php if (empty($list)): ?>
    <div class="notice-empty">등록된 공지사항이 없습니다.</div>
    <?php else: ?>
    <?php foreach ($list as $notice): ?>
    <a href="/supports/notices/<?= $notice['notice_idx'] ?>"
       class="notice-row <?= $notice['is_pinned'] ? 'notice-pinned' : '' ?>">
      <span class="notice-badge <?= $notice['is_pinned'] ? '' : 'gen' ?>">
        <?= $notice['is_pinned'] ? '공지' : '일반' ?>
      </span>
      <span class="notice-title-text"><?= htmlspecialchars($notice['title']) ?></span>
      <span class="notice-date"><?= date('Y.m.d', strtotime($notice['created_at'])) ?></span>
    </a>
    <?php endforeach; ?>
    <?php endif; ?>
  </div>

  <!-- 페이지네이션 -->
  <?php if ($pages > 1): ?>
  <div class="pagination">
    <a href="?page=<?= max(1, $page - 1) ?>" class="page-arrow <?= $page <= 1 ? 'disabled' : '' ?>">‹</a>
    <?php for ($i = 1; $i <= $pages; $i++): ?>
    <a href="?page=<?= $i ?>" class="page-btn <?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
    <?php endfor; ?>
    <a href="?page=<?= min($pages, $page + 1) ?>" class="page-arrow <?= $page >= $pages ? 'disabled' : '' ?>">›</a>
  </div>
  <?php endif; ?>
</div>
