<?php
// $notice, $prevNext 가 컨트롤러에서 전달됨
?>

<div class="sub_index">
  <div class="inner">
    <!-- 서브 배너 -->
    <?php require VIEW_PATH . '/components/cs-banner.php'; ?>

    <div class="sub_page_flex">
      <!-- 서브 메뉴 -->
      <?php require VIEW_PATH . '/components/cs-subnav.php'; ?>

      <div class="sub_page_contents notice-detail-wrap">
        <div class="page-section-title">공지사항</div>
        
        <!-- 헤더 -->
        <div class="notice-detail-header">
          <?php if ($notice['is_pinned']): ?>
          <div class="notice-detail-badge">공지</div>
          <?php elseif ($notice['is_maintenance']): ?>
          <div class="notice-detail-badge maintenance">점검</div>
          <?php else: ?>
          <!-- <div class="notice-detail-badge gen">일반</div> -->
          <?php endif; ?>
          <div class="notice-detail-title"><?= htmlspecialchars($notice['title']) ?></div>
          <div class="notice-detail-meta">
            <p>작성일 <?= date('Y.m.d', strtotime($notice['created_at'])) ?></p>
            <p>ㅣ</p>
            <p>조회수 <?= number_format((int) $notice['views']) ?></p>
          </div>
        </div>

        <!-- 본문 -->
        <div class="notice-detail-body">
          <?= $notice['content'] ?>
        </div>

        <!-- 목록으로 -->
        <div class="board_btn_box">
          <a href="/supports/notices" class="board_btn">목록보기</a>
        </div>

        <!-- 이전/다음글 -->
        <div class="notice-nav">
          <?php if ($prevNext['prev']): ?>
          <a href="/supports/notices/<?= $prevNext['prev']['notice_idx'] ?>" class="notice-row">
            <span class="notice-badge gen">이전글</span>
            <span class="notice-title-text"><?= htmlspecialchars($prevNext['prev']['title']) ?></span>
            <span class="notice-date"><?= date('Y.m.d', strtotime($prevNext['prev']['created_at'])) ?></span>
          </a>
          <?php else: ?>
          <div class="notice-row">            
            <span class="notice-badge gen">이전글</span>
            <span class="notice-title-text notice-nav-empty">이전 글이 없습니다.</span>
          </div>
          <?php endif; ?>

          <?php if ($prevNext['next']): ?>
          <a href="/supports/notices/<?= $prevNext['next']['notice_idx'] ?>" class="notice-row">            
            <span class="notice-badge gen">다음글</span>
            <span class="notice-title-text"><?= htmlspecialchars($prevNext['next']['title']) ?></span>
            <span class="notice-date"><?= date('Y.m.d', strtotime($prevNext['next']['created_at'])) ?></span>
          </a>
          <?php else: ?>
          <div class="notice-row">
            <span class="notice-badge gen">다음글</span>
            <span class="notice-title-text notice-nav-empty">다음 글이 없습니다.</span>
          </div>
          <?php endif; ?>
        </div>
      </div>
  </div>
</div>
