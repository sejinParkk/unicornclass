<?php
// $type, $categoryIdx, $categories, $list, $total, $page, $pages 가 컨트롤러에서 전달됨
$typeLabel = match($type) {
    'free'    => '무료강의',
    'premium' => '프리미엄강의',
    default   => '전체 클래스',
};
?>
<style>
/* ── 서브 배너 ── */
.cl-banner{position:relative;background:linear-gradient(105deg,#0d0d0d 0%,#1a1a2e 60%,#0f2027 100%);padding:36px 32px 32px;overflow:hidden}
.cl-banner-bg{position:absolute;inset:0;background:linear-gradient(105deg,rgba(192,57,43,.15) 0%,transparent 60%);pointer-events:none}
.cl-banner-label{font-size:11px;color:rgba(255,255,255,.5);letter-spacing:2px;text-transform:uppercase;margin-bottom:8px}
.cl-banner-title{font-size:32px;font-weight:900;color:#fff;letter-spacing:-1px;line-height:1.2}

/* ── 탭 ── */
.cl-tabs{background:#fff;border-bottom:2px solid #eee;padding:0 32px;display:flex;gap:0}
.cl-tab{padding:0 4px;height:50px;margin-right:24px;display:flex;align-items:center;font-size:14px;font-weight:600;color:#999;border-bottom:3px solid transparent;margin-bottom:-2px;text-decoration:none;transition:all .15s}
.cl-tab:hover{color:#333}
.cl-tab.active{color:#c0392b;border-bottom-color:#c0392b}

/* ── 카테고리 필터 ── */
.cat-bar{padding:16px 32px 14px;border-bottom:1px solid #f0f0f0;display:flex;gap:6px;flex-wrap:wrap}
.cat-btn{height:30px;padding:0 14px;border-radius:15px;border:1px solid #e0e0e0;font-size:12.5px;font-weight:600;color:#555;background:#fff;text-decoration:none;display:inline-flex;align-items:center;transition:all .15s}
.cat-btn:hover{border-color:#c0392b;color:#c0392b}
.cat-btn.active{background:#c0392b;border-color:#c0392b;color:#fff}

/* ── 카드 그리드 ── */
.cl-grid-wrap{padding:28px 32px 0}
.cl-count{font-size:13px;color:#888;margin-bottom:16px}
.cl-count strong{color:#c0392b}
.cl-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:20px}
.cl-card{border-radius:8px;overflow:hidden;cursor:pointer;border:1px solid #eee;background:#fff;text-decoration:none;transition:box-shadow .15s,transform .15s;display:block}
.cl-card:hover{box-shadow:0 6px 20px rgba(0,0,0,.1);transform:translateY(-2px)}
.card-thumb{position:relative;aspect-ratio:16/9;overflow:hidden;background:#1a1a2e}
.card-thumb img{width:100%;height:100%;object-fit:cover;display:block}
.card-thumb-ph{width:100%;height:100%;background:linear-gradient(135deg,#0f2027 0%,#203a43 50%,#2c5364 100%)}
.card-info{padding:10px 12px 14px}
.card-tags{display:flex;gap:3px;margin-bottom:6px;flex-wrap:wrap}
.ctag{font-size:10px;font-weight:700;padding:2px 6px;border-radius:2px}
.ct-hot{background:#fdecea;color:#c0392b}
.ct-new{background:#e8f5e9;color:#27ae60}
.ct-free{background:#e8f5e9;color:#27ae60;border:1px solid #27ae60}
.ct-premium{background:#f3e8ff;color:#8e44ad}
.card-title{font-size:13px;font-weight:700;color:#1a1a1a;line-height:1.4;overflow:hidden;text-overflow:ellipsis;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;margin-bottom:4px}
.card-meta{font-size:11.5px;color:#999}

/* ── 빈 상태 ── */
.cl-empty{padding:80px 32px;text-align:center;color:#aaa;font-size:14px}

/* ── 페이지네이션 ── */
.pagination{display:flex;justify-content:center;align-items:center;gap:4px;margin:32px 0 60px}
.page-btn{width:34px;height:34px;border-radius:6px;border:1px solid #ddd;background:#fff;font-size:13px;color:#555;cursor:pointer;display:flex;align-items:center;justify-content:center;text-decoration:none;transition:all .15s}
.page-btn:hover{border-color:#c0392b;color:#c0392b}
.page-btn.active{background:#c0392b;border-color:#c0392b;color:#fff;font-weight:700}
.page-arrow{width:34px;height:34px;border-radius:6px;border:1px solid #ddd;background:#fff;font-size:14px;color:#888;cursor:pointer;display:flex;align-items:center;justify-content:center;text-decoration:none;transition:all .15s}
.page-arrow:hover{border-color:#c0392b;color:#c0392b}
.page-arrow.disabled{opacity:.35;pointer-events:none}

@media(max-width:900px){.cl-grid{grid-template-columns:repeat(2,1fr)}}
@media(max-width:600px){.cl-grid{grid-template-columns:1fr}.cl-grid-wrap,.cat-bar,.cl-tabs{padding-left:16px;padding-right:16px}}
</style>

<!-- 서브 배너 -->
<div class="cl-banner">
  <div class="cl-banner-bg"></div>
  <div class="cl-banner-label">지금 진행중인</div>
  <div class="cl-banner-title">클래스</div>
</div>

<!-- 타입 탭 -->
<div class="cl-tabs">
  <?php
  $tabBase = $categoryIdx > 0 ? '?cat=' . $categoryIdx : '?';
  ?>
  <a href="/classes" class="cl-tab <?= $type === '' ? 'active' : '' ?>">전체</a>
  <a href="/classes?type=free<?= $categoryIdx > 0 ? '&cat='.$categoryIdx : '' ?>"
     class="cl-tab <?= $type === 'free' ? 'active' : '' ?>">무료강의</a>
  <a href="/classes?type=premium<?= $categoryIdx > 0 ? '&cat='.$categoryIdx : '' ?>"
     class="cl-tab <?= $type === 'premium' ? 'active' : '' ?>">프리미엄강의</a>
</div>

<!-- 카테고리 필터 -->
<?php if (!empty($categories)): ?>
<div class="cat-bar">
  <?php
  $catBase = $type !== '' ? '?type=' . $type : '?';
  ?>
  <a href="/classes<?= $type !== '' ? '?type='.$type : '' ?>"
     class="cat-btn <?= $categoryIdx === 0 ? 'active' : '' ?>">전체</a>
  <?php foreach ($categories as $cat): ?>
  <a href="/classes?<?= $type !== '' ? 'type='.$type.'&' : '' ?>cat=<?= $cat['category_idx'] ?>"
     class="cat-btn <?= $categoryIdx === (int)$cat['category_idx'] ? 'active' : '' ?>">
    <?= htmlspecialchars($cat['name']) ?>
  </a>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- 카드 그리드 -->
<div class="cl-grid-wrap">
  <div class="cl-count">총 <strong><?= number_format($total) ?></strong>개 강의</div>

  <?php if (empty($list)): ?>
  <div class="cl-empty">등록된 강의가 없습니다.</div>
  <?php else: ?>
  <div class="cl-grid">
    <?php foreach ($list as $class): ?>
    <a href="/classes/<?= $class['class_idx'] ?>" class="cl-card">
      <div class="card-thumb">
        <?php if (!empty($class['thumbnail'])): ?>
        <img src="/uploads/class/<?= htmlspecialchars($class['thumbnail']) ?>"
             alt="<?= htmlspecialchars($class['title']) ?>">
        <?php else: ?>
        <div class="card-thumb-ph"></div>
        <?php endif; ?>
      </div>
      <div class="card-info">
        <div class="card-tags">
          <?php if ($class['badge_hot']): ?><span class="ctag ct-hot">HOT</span><?php endif; ?>
          <?php if ($class['badge_new']): ?><span class="ctag ct-new">NEW</span><?php endif; ?>
          <?php if ($class['type'] === 'free'): ?>
          <span class="ctag ct-free">무료</span>
          <?php else: ?>
          <span class="ctag ct-premium">프리미엄</span>
          <?php endif; ?>
        </div>
        <div class="card-title"><?= htmlspecialchars($class['title']) ?></div>
        <div class="card-meta">
          <?= htmlspecialchars($class['instructor_name']) ?>
          <?php if (!empty($class['category_name'])): ?>
          · <?= htmlspecialchars($class['category_name']) ?>
          <?php endif; ?>
        </div>
      </div>
    </a>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

  <!-- 페이지네이션 -->
  <?php if ($pages > 1): ?>
  <?php
  $qBase = [];
  if ($type !== '') $qBase[] = 'type=' . $type;
  if ($categoryIdx > 0) $qBase[] = 'cat=' . $categoryIdx;
  $qBaseStr = implode('&', $qBase);
  ?>
  <div class="pagination">
    <a href="?<?= $qBaseStr ? $qBaseStr.'&' : '' ?>page=<?= max(1, $page - 1) ?>"
       class="page-arrow <?= $page <= 1 ? 'disabled' : '' ?>">‹</a>
    <?php
    $start = max(1, $page - 2);
    $end   = min($pages, $page + 2);
    if ($start > 1): ?><a href="?<?= $qBaseStr ? $qBaseStr.'&' : '' ?>page=1" class="page-btn">1</a><?php if ($start > 2): ?><span style="padding:0 4px;color:#ccc">…</span><?php endif; endif;
    for ($i = $start; $i <= $end; $i++):
    ?><a href="?<?= $qBaseStr ? $qBaseStr.'&' : '' ?>page=<?= $i ?>" class="page-btn <?= $i === $page ? 'active' : '' ?>"><?= $i ?></a><?php
    endfor;
    if ($end < $pages): if ($end < $pages - 1): ?><span style="padding:0 4px;color:#ccc">…</span><?php endif; ?><a href="?<?= $qBaseStr ? $qBaseStr.'&' : '' ?>page=<?= $pages ?>" class="page-btn"><?= $pages ?></a><?php endif; ?>
    <a href="?<?= $qBaseStr ? $qBaseStr.'&' : '' ?>page=<?= min($pages, $page + 1) ?>"
       class="page-arrow <?= $page >= $pages ? 'disabled' : '' ?>">›</a>
  </div>
  <?php endif; ?>
</div>
