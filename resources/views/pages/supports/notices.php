<?php
// $list, $total, $page, $pages, $limit 가 컨트롤러에서 전달됨
?>

<div class="sub_index">
  <div class="inner">
    <!-- 서브 배너 -->
    <?php require VIEW_PATH . '/components/cs-banner.php'; ?>

    <div class="sub_page_flex">
      <!-- 서브 메뉴 -->
      <?php require VIEW_PATH . '/components/cs-subnav.php'; ?>

      <div class="sub_page_contents notice-wrap">
        <div class="page-section-title">공지사항</div>

        <div class="notice-list">
          <?php if (empty($list)): ?>
          <div class="lecture-empty faq-empty">등록된 공지사항이 없습니다.</div>
          <?php else: ?>
          <?php foreach ($list as $notice): ?>
          <a href="/supports/notices/<?= $notice['notice_idx'] ?>"
            class="notice-row <?= ($notice['is_pinned'] || $notice['is_maintenance']) ? 'notice-pinned' : '' ?>">
            <?php if ($notice['is_pinned']): ?>
            <span class="notice-badge">공지</span>
            <?php elseif ($notice['is_maintenance']): ?>
            <span class="notice-badge notice-badge--maintenance">점검</span>
            <?php else: ?>
            <span class="notice-badge gen">일반</span>
            <?php endif; ?>

            <span class="notice-title-text"><?= htmlspecialchars($notice['title']) ?></span>
            <span class="notice-date"><?= date('Y.m.d', strtotime($notice['created_at'])) ?></span>
          </a>
          <?php endforeach; ?>
          <?php endif; ?>
        </div>

        <!-- 페이지네이션 -->
        <?php if ($pages > 1): ?>
        <div class="pagination">
          <a href="?page=<?= max(1, $page - 1) ?>" class="page-btn page-prev <?= $page <= 1 ? 'disabled' : '' ?>"></a>
          <?php for ($i = 1; $i <= $pages; $i++): ?>
          <a href="?page=<?= $i ?>" class="page-btn <?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
          <?php endfor; ?>
          <a href="?page=<?= min($pages, $page + 1) ?>" class="page-btn page-next <?= $page >= $pages ? 'disabled' : '' ?>"></a>
        </div>
        <?php endif; ?>
      </div>

    </div>
  </div>
</div>