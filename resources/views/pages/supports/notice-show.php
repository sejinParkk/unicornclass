<?php
// $notice, $prevNext 가 컨트롤러에서 전달됨
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
.notice-detail-wrap{max-width:900px;margin:0 auto;padding:32px 48px 60px}
.notice-detail-header{border-bottom:2px solid #222;padding-bottom:20px;margin-bottom:28px}
.notice-detail-badge{display:inline-block;padding:3px 10px;background:#c0392b;color:#fff;font-size:11px;font-weight:700;border-radius:3px;margin-bottom:12px}
.notice-detail-badge.gen{background:#f0f0f0;color:#888}
.notice-detail-title{font-size:20px;font-weight:800;color:#1a1a1a;line-height:1.4;margin-bottom:12px}
.notice-detail-meta{display:flex;gap:20px;font-size:12px;color:#aaa}
.notice-detail-body{font-size:13.5px;color:#444;line-height:2;min-height:200px;padding-bottom:32px;border-bottom:1px solid #eee;word-break:break-word}
.notice-detail-body p{margin-bottom:14px}
.notice-detail-body strong{color:#1a1a1a;font-weight:700}
.notice-back-btn{height:42px;padding:0 20px;background:#f5f5f5;border:1px solid #e0e0e0;border-radius:8px;font-size:13px;font-weight:600;color:#555;display:inline-flex;align-items:center;gap:6px;cursor:pointer;font-family:inherit;margin-top:16px;text-decoration:none}
.notice-back-btn:hover{background:#eee}
.notice-nav{border:1px solid #eee;border-radius:8px;overflow:hidden;margin-top:24px}
.notice-nav-row{display:flex;align-items:center;gap:14px;padding:14px 18px;border-bottom:1px solid #f0f0f0;text-decoration:none}
.notice-nav-row:last-child{border-bottom:none}
.notice-nav-row:hover{background:#fafafa}
.notice-nav-label{font-size:11px;font-weight:700;color:#aaa;width:28px;flex-shrink:0}
.notice-nav-title{font-size:13px;color:#555;flex:1}
.notice-nav-date{font-size:11px;color:#bbb}
.notice-nav-empty{font-size:13px;color:#bbb;flex:1}
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

<div class="notice-detail-wrap">
  <!-- 헤더 -->
  <div class="notice-detail-header">
    <div class="notice-detail-badge <?= $notice['is_pinned'] ? '' : 'gen' ?>">
      <?= $notice['is_pinned'] ? '공지' : '일반' ?>
    </div>
    <div class="notice-detail-title"><?= htmlspecialchars($notice['title']) ?></div>
    <div class="notice-detail-meta">
      <span>작성일 <?= date('Y.m.d', strtotime($notice['created_at'])) ?></span>
      <span>조회수 <?= number_format((int) $notice['views']) ?></span>
    </div>
  </div>

  <!-- 본문 -->
  <div class="notice-detail-body">
    <?= $notice['content'] ?>
  </div>

  <!-- 목록으로 -->
  <a href="/supports/notices" class="notice-back-btn">
    <svg width="14" height="14" viewBox="0 0 14 14" fill="none" stroke="#555" stroke-width="1.5">
      <polyline points="9,2 4,7 9,12"/>
    </svg>
    목록으로
  </a>

  <!-- 이전/다음글 -->
  <div class="notice-nav">
    <?php if ($prevNext['prev']): ?>
    <a href="/supports/notices/<?= $prevNext['prev']['notice_idx'] ?>" class="notice-nav-row">
      <span class="notice-nav-label">이전글</span>
      <span class="notice-nav-title"><?= htmlspecialchars($prevNext['prev']['title']) ?></span>
      <span class="notice-nav-date"><?= date('Y.m.d', strtotime($prevNext['prev']['created_at'])) ?></span>
    </a>
    <?php else: ?>
    <div class="notice-nav-row">
      <span class="notice-nav-label">이전글</span>
      <span class="notice-nav-empty">이전 글이 없습니다.</span>
    </div>
    <?php endif; ?>

    <?php if ($prevNext['next']): ?>
    <a href="/supports/notices/<?= $prevNext['next']['notice_idx'] ?>" class="notice-nav-row">
      <span class="notice-nav-label">다음글</span>
      <span class="notice-nav-title"><?= htmlspecialchars($prevNext['next']['title']) ?></span>
      <span class="notice-nav-date"><?= date('Y.m.d', strtotime($prevNext['next']['created_at'])) ?></span>
    </a>
    <?php else: ?>
    <div class="notice-nav-row">
      <span class="notice-nav-label">다음글</span>
      <span class="notice-nav-empty">다음 글이 없습니다.</span>
    </div>
    <?php endif; ?>
  </div>
</div>
