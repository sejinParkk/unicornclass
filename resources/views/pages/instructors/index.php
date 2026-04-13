<?php
// $list, $total, $page, $pages 가 컨트롤러에서 전달됨
?>
<style>
/* ── 서브 배너 ── */
.ins-banner{position:relative;background:linear-gradient(105deg,#0d0d0d 0%,#1a1a2e 60%,#0f2027 100%);padding:36px 32px 32px;overflow:hidden}
.ins-banner-bg{position:absolute;inset:0;background:linear-gradient(105deg,rgba(192,57,43,.15) 0%,transparent 60%);pointer-events:none}
.ins-banner-label{font-size:11px;color:rgba(255,255,255,.5);letter-spacing:2px;text-transform:uppercase;margin-bottom:8px}
.ins-banner-title{font-size:32px;font-weight:900;color:#fff;letter-spacing:-1px;line-height:1.2}

/* ── 콘텐츠 ── */
.ins-content{padding:40px 32px 0}

/* ── 목록 헤더 ── */
.ins-list-header{display:flex;align-items:baseline;gap:10px;margin-bottom:28px}
.ins-list-title{font-size:22px;font-weight:900;color:#111}
.ins-list-count{font-size:13px;color:#e74c3c;font-weight:700}

/* ── 강사 카드 그리드 ── */
.ins-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:0}

/* 카드 래퍼 — hover 기준 */
.i-card-wrap{padding:12px 10px;cursor:pointer;text-decoration:none;display:block}
.i-card{position:relative;transition:transform .28s ease}
.i-card-wrap:hover .i-card{transform:translateY(-8px)}

/* 사진 영역 */
.i-photo-wrap{position:relative;width:100%;height:240px;overflow:hidden;background:#e8eef4}

/* 호버 시 빨간 그라디언트 오버레이 */
.i-photo-wrap::after{content:'';position:absolute;bottom:0;left:0;right:0;height:70%;background:linear-gradient(to top,rgba(192,57,43,.88) 0%,rgba(231,76,60,.45) 45%,transparent 100%);opacity:0;transition:opacity .28s ease;z-index:1}
.i-card-wrap:hover .i-photo-wrap::after{opacity:1}

/* 사진 placeholder */
.i-photo-ph{position:absolute;inset:0;background:linear-gradient(160deg,#e8eef4 0%,#c8d8e8 100%);display:flex;flex-direction:column;align-items:center;justify-content:center;gap:6px}
.i-photo-ph .person-icon{font-size:64px;line-height:1;color:#b0c4d8}
.i-photo-ph small{font-size:10px;color:#aaa}
.i-photo-img{width:100%;height:100%;object-fit:cover;object-position:top;display:block}

/* 카드 정보 */
.i-info{padding:10px 2px 6px}
.i-name{font-size:15px;font-weight:800;color:#111;margin-bottom:5px}
.i-desc-list{list-style:none;margin-bottom:8px}
.i-desc-list li{font-size:11px;color:#666;line-height:1.55;padding-left:10px;position:relative;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;margin-bottom:2px}
.i-desc-list li::before{content:'•';position:absolute;left:0;color:#e74c3c;font-weight:700}

/* 소셜 아이콘 (하단 고정) */
.i-social-fixed{display:flex;gap:5px}
.i-social-icon{width:22px;height:22px;border-radius:50%;background:#f0f0f0;border:1px solid #e0e0e0;display:flex;align-items:center;justify-content:center;font-size:11px;color:#666}

/* ── CTA 섹션 ── */
.ins-cta{margin:48px 0;padding:48px 32px;background:linear-gradient(105deg,#1a1a2e 0%,#0f2027 100%);border-radius:16px;text-align:center;position:relative;overflow:hidden}
.ins-cta-bg{position:absolute;inset:0;background:linear-gradient(105deg,rgba(192,57,43,.2) 0%,transparent 60%);pointer-events:none}
.ins-cta-inner{position:relative;z-index:1}
.ins-cta-en{font-size:13px;color:rgba(255,255,255,.5);letter-spacing:3px;text-transform:uppercase;margin-bottom:12px}
.ins-cta-title{font-size:28px;font-weight:900;color:#fff;letter-spacing:-1px;margin-bottom:10px}
.ins-cta-desc{font-size:14px;color:rgba(255,255,255,.65);line-height:1.8;margin-bottom:28px}
.ins-cta-btn{display:inline-flex;align-items:center;gap:8px;height:52px;padding:0 32px;background:#c0392b;color:#fff;border-radius:10px;font-size:15px;font-weight:800;text-decoration:none;transition:background .15s}
.ins-cta-btn:hover{background:#a93226}

/* ── 페이지네이션 ── */
.pagination{display:flex;justify-content:center;align-items:center;gap:4px;margin:8px 0 60px}
.page-btn{width:34px;height:34px;border-radius:6px;border:1px solid #ddd;background:#fff;font-size:13px;color:#555;display:flex;align-items:center;justify-content:center;text-decoration:none;transition:all .15s}
.page-btn:hover{border-color:#c0392b;color:#c0392b}
.page-btn.active{background:#c0392b;border-color:#c0392b;color:#fff;font-weight:700}
.page-arrow{width:34px;height:34px;border-radius:6px;border:1px solid #ddd;background:#fff;font-size:14px;color:#888;display:flex;align-items:center;justify-content:center;text-decoration:none;transition:all .15s}
.page-arrow:hover{border-color:#c0392b;color:#c0392b}
.page-arrow.disabled{opacity:.35;pointer-events:none}

@media(max-width:900px){.ins-grid{grid-template-columns:repeat(3,1fr)}}
@media(max-width:640px){.ins-grid{grid-template-columns:repeat(2,1fr)}.ins-content{padding:24px 16px 0}}
</style>

<!-- 서브 배너 -->
<div class="ins-banner">
  <div class="ins-banner-bg"></div>
  <div class="ins-banner-label">유니콘클래스의</div>
  <div class="ins-banner-title">강사소개</div>
</div>

<div class="ins-content">

  <!-- 목록 헤더 -->
  <div class="ins-list-header">
    <div class="ins-list-title">강사진</div>
    <div class="ins-list-count">총 <?= number_format($total) ?>명</div>
  </div>

  <!-- 강사 카드 그리드 -->
  <?php if (empty($list)): ?>
  <p style="text-align:center;padding:60px 0;color:#aaa;font-size:14px">등록된 강사가 없습니다.</p>
  <?php else: ?>
  <div class="ins-grid">
    <?php foreach ($list as $ins): ?>
    <a href="/instructors/<?= $ins['instructor_idx'] ?>" class="i-card-wrap">
      <div class="i-card">
        <!-- 사진 -->
        <div class="i-photo-wrap">
          <?php if (!empty($ins['photo'])): ?>
          <img src="/uploads/instructor/<?= htmlspecialchars($ins['photo']) ?>"
               alt="<?= htmlspecialchars($ins['name']) ?>" class="i-photo-img">
          <?php else: ?>
          <div class="i-photo-ph">
            <div class="person-icon">👤</div>
            <small><?= htmlspecialchars($ins['name']) ?></small>
          </div>
          <?php endif; ?>
        </div>
        <!-- 정보 -->
        <div class="i-info">
          <div class="i-name"><?= htmlspecialchars($ins['name']) ?></div>
          <?php if (!empty($ins['field'])): ?>
          <ul class="i-desc-list">
            <li><?= htmlspecialchars($ins['field']) ?></li>
          </ul>
          <?php endif; ?>
          <div class="i-social-fixed">
            <?php if (!empty($ins['sns_youtube'])): ?>
            <div class="i-social-icon" title="유튜브">▶</div>
            <?php endif; ?>
            <?php if (!empty($ins['sns_instagram'])): ?>
            <div class="i-social-icon" title="인스타그램">📷</div>
            <?php endif; ?>
            <?php if (!empty($ins['sns_facebook'])): ?>
            <div class="i-social-icon" title="페이스북">f</div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </a>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

  <!-- CTA 섹션 -->
  <div class="ins-cta">
    <div class="ins-cta-bg"></div>
    <div class="ins-cta-inner">
      <div class="ins-cta-en">Join Us</div>
      <div class="ins-cta-title">유니콘클래스 강사가 되어보세요</div>
      <div class="ins-cta-desc">
        당신의 노하우와 경험을 수강생들과 나눠보세요.<br>
        유니콘클래스와 함께 더 많은 사람들에게 가치를 전달할 수 있습니다.
      </div>
      <a href="/instructors/apply" class="ins-cta-btn">🎓 강사 지원하기 →</a>
    </div>
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
