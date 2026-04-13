<?php
// $term 이 컨트롤러에서 전달됨 (없으면 null)
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
.cs-subnav-item{position:relative;padding:0 20px;height:48px;line-height:48px;font-size:13.5px;font-weight:500;color:#888;border-bottom:2px solid transparent;transition:color .2s,border-color .2s;white-space:nowrap;text-decoration:none;display:block}
.cs-subnav-item:hover,.cs-subnav-item.active{color:#c0392b;border-bottom-color:#c0392b;font-weight:700}
.policy-wrap{max-width:900px;margin:0 auto;padding:32px 48px 60px}
.page-section-title{font-size:17px;font-weight:700;color:#1a1a1a;margin-bottom:20px}
.policy-meta{font-size:12px;color:#aaa;margin-bottom:16px}
.policy-body{border:1px solid #eee;border-radius:8px;padding:28px;font-size:13px;line-height:2;color:#555}
.policy-body h3{font-size:13.5px;font-weight:700;color:#1a1a1a;margin:24px 0 8px;padding-left:10px;border-left:3px solid #c0392b}
.policy-body h3:first-child{margin-top:0}
.policy-body p{margin-bottom:10px}
.policy-body ol,.policy-body ul{padding-left:18px;margin-bottom:10px}
.policy-body li{margin-bottom:4px}
.policy-empty{padding:48px 0;text-align:center;color:#aaa;font-size:14px}
</style>

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
