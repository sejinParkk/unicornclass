<?php
// $list, $total, $page, $pages, $limit 가 컨트롤러에서 전달됨
?>
<style>
.cs-banner{position:relative;height:140px;background:linear-gradient(135deg,#1a1a1a 0%,#2d2d2d 55%,#3a1a1a 100%);display:flex;align-items:center;overflow:hidden}
.cs-banner::before{content:'';position:absolute;inset:0;background:repeating-linear-gradient(-45deg,transparent,transparent 28px,rgba(192,57,43,.04) 28px,rgba(192,57,43,.04) 29px)}
.cs-banner::after{content:'';position:absolute;right:-50px;top:-50px;width:260px;height:260px;border-radius:50%;background:radial-gradient(circle,rgba(192,57,43,.18) 0%,transparent 70%)}
.cs-banner-inner{position:relative;z-index:1;padding:0 48px}
.cs-banner-en{font-size:10px;font-weight:500;letter-spacing:3px;color:#c0392b;margin-bottom:8px}
.cs-banner-title{font-size:26px;font-weight:700;color:#fff;letter-spacing:-.5px}
.cs-banner-title span{color:#c0392b}
.cs-subnav{background:#fff;border-bottom:1px solid #eee;display:flex;padding:0 48px}
.cs-subnav-item{position:relative;padding:0 20px;height:48px;line-height:48px;font-size:13.5px;font-weight:500;color:#888;cursor:pointer;border-bottom:2px solid transparent;transition:color .2s,border-color .2s;white-space:nowrap;text-decoration:none;display:block}
.cs-subnav-item:hover,.cs-subnav-item.active{color:#c0392b;border-bottom-color:#c0392b;font-weight:700}
.notice-wrap{max-width:900px;margin:0 auto;padding:32px 48px 60px}
.page-section-title{font-size:17px;font-weight:700;color:#1a1a1a;margin-bottom:20px}
.notice-list{border-top:2px solid #222}
.notice-row{display:flex;align-items:center;gap:14px;padding:16px 0;border-bottom:1px solid #eee;cursor:pointer;text-decoration:none}
.notice-row:hover .notice-title-text{color:#c0392b}
.notice-badge{flex-shrink:0;padding:2px 8px;background:#c0392b;color:#fff;font-size:10px;font-weight:700;border-radius:3px;letter-spacing:.4px}
.notice-badge.gen{background:#f0f0f0;color:#888}
.notice-pinned{background:#fffaf9}
.notice-title-text{flex:1;font-size:13.5px;font-weight:500;color:#333;line-height:1.5;transition:color .2s}
.notice-date{flex-shrink:0;font-size:12px;color:#aaa}
.notice-empty{padding:48px 0;text-align:center;color:#aaa;font-size:14px}
.pagination{display:flex;justify-content:center;align-items:center;gap:4px;margin-top:32px}
.page-btn{width:34px;height:34px;border-radius:6px;border:1px solid #ddd;background:#fff;font-size:13px;color:#555;cursor:pointer;display:flex;align-items:center;justify-content:center;text-decoration:none;transition:all .15s}
.page-btn:hover{border-color:#c0392b;color:#c0392b}
.page-btn.active{background:#c0392b;border-color:#c0392b;color:#fff;font-weight:700}
.page-arrow{width:34px;height:34px;border-radius:6px;border:1px solid #ddd;background:#fff;font-size:14px;color:#888;cursor:pointer;display:flex;align-items:center;justify-content:center;text-decoration:none;transition:all .15s}
.page-arrow:hover{border-color:#c0392b;color:#c0392b}
.page-arrow.disabled{opacity:.35;pointer-events:none}
</style>

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
