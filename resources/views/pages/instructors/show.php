<?php
// $instructor 가 컨트롤러에서 전달됨
// $instructor['intros'][], $instructor['careers'][], $instructor['classes'][]
?>
<style>
/* ── 프로필 섹션 ── */
.ins-profile{padding:48px 60px 40px;display:flex;gap:64px;align-items:flex-start}

/* 좌: 사진 */
.ins-photo-wrap{width:280px;flex-shrink:0;position:relative}
.ins-photo-deco{position:absolute;bottom:0;left:-8px;width:220px;height:200px;z-index:0;overflow:hidden;pointer-events:none}
.ins-photo-deco::before{content:'';position:absolute;bottom:0;left:0;width:0;height:0;border-left:110px solid transparent;border-right:110px solid transparent;border-bottom:110px solid rgba(192,57,43,.18)}
.ins-photo-deco::after{content:'';position:absolute;bottom:20px;left:20px;width:0;height:0;border-left:90px solid transparent;border-right:90px solid transparent;border-bottom:90px solid rgba(192,57,43,.13)}
.ins-photo-img{position:relative;z-index:1;width:100%;height:340px;object-fit:cover;object-position:top;display:block;border-radius:2px}
.ins-photo-ph{position:relative;z-index:1;width:100%;height:340px;background:linear-gradient(160deg,#e8eef4 0%,#c4d8ec 100%);display:flex;flex-direction:column;align-items:center;justify-content:center;gap:8px;border-radius:2px}
.ins-photo-ph .person-icon{font-size:80px;color:#b0c8dc}
.ins-photo-ph small{font-size:11px;color:#9ab0c4}

/* 우: 정보 */
.ins-info-wrap{flex:1;min-width:0}
.ins-name{font-size:28px;font-weight:900;color:#111;letter-spacing:-.5px;margin-bottom:6px}
.ins-category{font-size:13px;color:#888;margin-bottom:14px}

/* 소셜 아이콘 */
.ins-social{display:flex;gap:8px;margin-bottom:28px}
.social-icon-btn{width:30px;height:30px;border-radius:6px;border:1px solid #ddd;display:flex;align-items:center;justify-content:center;font-size:14px;color:#555;text-decoration:none;transition:border-color .15s,color .15s}
.social-icon-btn:hover{border-color:#c0392b;color:#c0392b}

/* 소개/경력 행 */
.ins-row{display:flex;gap:0;border-top:1px solid #eee;padding:20px 0}
.ins-row:last-of-type{border-bottom:1px solid #eee}
.ins-row-label{width:80px;flex-shrink:0;font-size:13px;font-weight:700;color:#333;padding-top:1px}
.ins-row-content{flex:1}
.bullet-list{list-style:none}
.bullet-list li{font-size:13px;color:#444;line-height:1.8;padding-left:14px;position:relative}
.bullet-list li::before{content:'•';position:absolute;left:0;color:#c0392b;font-weight:700}
.ins-empty-text{font-size:13px;color:#aaa;padding:4px 0}

/* ── 강의 목록 섹션 ── */
.ins-lecture-section{padding:40px 60px 60px;border-top:8px solid #f5f5f5}
.lecture-title{font-size:18px;font-weight:900;color:#111;margin-bottom:24px;letter-spacing:-.3px}
.lecture-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:16px}
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
.ct-best{background:#fef0ee;color:#c0392b;border:1px solid #c0392b}
.ct-free{background:#e8f5e9;color:#27ae60;border:1px solid #27ae60}
.ct-premium{background:#f3e8ff;color:#8e44ad}
.card-title{font-size:13px;font-weight:700;color:#1a1a1a;line-height:1.4;overflow:hidden;text-overflow:ellipsis;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;margin-bottom:4px}
.card-meta{font-size:11.5px;color:#999}
.lecture-empty{padding:40px 0;text-align:center;color:#aaa;font-size:14px}

/* ── 뒤로 가기 링크 ── */
.ins-back{display:inline-flex;align-items:center;gap:6px;font-size:13px;color:#888;text-decoration:none;padding:12px 60px;border-bottom:1px solid #f0f0f0;transition:color .15s}
.ins-back:hover{color:#c0392b}

@media(max-width:900px){
  .ins-profile{flex-direction:column;gap:32px;padding:32px 24px}
  .ins-photo-wrap{width:100%;max-width:280px}
  .ins-lecture-section{padding:32px 24px 48px}
  .lecture-grid{grid-template-columns:repeat(2,1fr)}
}
@media(max-width:600px){
  .lecture-grid{grid-template-columns:1fr}
  .ins-back{padding:12px 24px}
}
</style>

<!-- 뒤로가기 -->
<a href="/instructors" class="ins-back">← 강사 목록으로</a>

<!-- 프로필 섹션 -->
<div class="ins-profile">

  <!-- 좌: 사진 -->
  <div class="ins-photo-wrap">
    <div class="ins-photo-deco"></div>
    <?php if (!empty($instructor['photo'])): ?>
    <img src="/uploads/instructor/<?= htmlspecialchars($instructor['photo']) ?>"
         alt="<?= htmlspecialchars($instructor['name']) ?>" class="ins-photo-img">
    <?php else: ?>
    <div class="ins-photo-ph">
      <div class="person-icon">👤</div>
      <small>강사 사진</small>
    </div>
    <?php endif; ?>
  </div>

  <!-- 우: 정보 -->
  <div class="ins-info-wrap">

    <!-- 이름 + 분야 -->
    <div class="ins-name"><?= htmlspecialchars($instructor['name']) ?></div>
    <div class="ins-category">
      <?= htmlspecialchars($instructor['field'] ?: ($instructor['category_name'] ?? '')) ?>
    </div>

    <!-- 소셜 아이콘 -->
    <?php $hasSocial = !empty($instructor['sns_youtube']) || !empty($instructor['sns_instagram']) || !empty($instructor['sns_facebook']); ?>
    <?php if ($hasSocial): ?>
    <div class="ins-social">
      <?php if (!empty($instructor['sns_youtube'])): ?>
      <a href="<?= htmlspecialchars($instructor['sns_youtube']) ?>" target="_blank" rel="noopener" class="social-icon-btn" title="유튜브">▶</a>
      <?php endif; ?>
      <?php if (!empty($instructor['sns_instagram'])): ?>
      <a href="<?= htmlspecialchars($instructor['sns_instagram']) ?>" target="_blank" rel="noopener" class="social-icon-btn" title="인스타그램">📷</a>
      <?php endif; ?>
      <?php if (!empty($instructor['sns_facebook'])): ?>
      <a href="<?= htmlspecialchars($instructor['sns_facebook']) ?>" target="_blank" rel="noopener" class="social-icon-btn" title="페이스북">f</a>
      <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- 강사소개 -->
    <div class="ins-row">
      <div class="ins-row-label">강사소개</div>
      <div class="ins-row-content">
        <?php if (!empty($instructor['intros'])): ?>
        <ul class="bullet-list">
          <?php foreach ($instructor['intros'] as $intro): ?>
          <li><?= htmlspecialchars($intro['content']) ?></li>
          <?php endforeach; ?>
        </ul>
        <?php else: ?>
        <div class="ins-empty-text">등록된 소개가 없습니다.</div>
        <?php endif; ?>
      </div>
    </div>

    <!-- 강사 경력 -->
    <div class="ins-row">
      <div class="ins-row-label">강사 경력</div>
      <div class="ins-row-content">
        <?php if (!empty($instructor['careers'])): ?>
        <ul class="bullet-list">
          <?php foreach ($instructor['careers'] as $career): ?>
          <li><?= htmlspecialchars($career['content']) ?></li>
          <?php endforeach; ?>
        </ul>
        <?php else: ?>
        <div class="ins-empty-text">등록된 경력이 없습니다.</div>
        <?php endif; ?>
      </div>
    </div>

  </div><!-- /ins-info-wrap -->
</div><!-- /ins-profile -->

<!-- 강의 목록 섹션 -->
<div class="ins-lecture-section">
  <div class="lecture-title">강의 목록</div>

  <?php if (empty($instructor['classes'])): ?>
  <div class="lecture-empty">등록된 강의가 없습니다.</div>
  <?php else: ?>
  <div class="lecture-grid">
    <?php foreach ($instructor['classes'] as $class): ?>
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
          <?php if ($class['type'] === 'free'): ?>
          <span class="ctag ct-free">무료</span>
          <?php else: ?>
          <span class="ctag ct-premium">프리미엄</span>
          <?php endif; ?>
        </div>
        <div class="card-title"><?= htmlspecialchars($class['title']) ?></div>
        <div class="card-meta"><?= htmlspecialchars($instructor['name']) ?></div>
      </div>
    </a>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</div>
