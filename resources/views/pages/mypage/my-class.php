<?php
/**
 * 나의 강의
 * 변수: $classes (array), $counts (array), $type (string)
 */

$now = new DateTimeImmutable();

function formatDate(string $dt): string {
    return (new DateTimeImmutable($dt))->format('Y.m.d');
}

function progressRate(int $done, int $total): int {
    if ($total <= 0) return 0;
    return (int) min(100, round($done / $total * 100));
}
?>

<div class="mp-content-title">
  나의 강의
  <span>총 <?= $counts['all'] ?>개</span>
</div>

<!-- 탭 -->
<div class="my-class-tabs">
  <button class="my-class-tab <?= $type === 'all'     ? 'active' : '' ?>"
          onclick="location.href='/mypage/my-class'">
    전체 (<?= $counts['all'] ?>)
  </button>
  <button class="my-class-tab <?= $type === 'free'    ? 'active' : '' ?>"
          onclick="location.href='/mypage/my-class?type=free'">
    무료강의 (<?= $counts['free'] ?>)
  </button>
  <button class="my-class-tab <?= $type === 'premium' ? 'active' : '' ?>"
          onclick="location.href='/mypage/my-class?type=premium'">
    프리미엄강의 (<?= $counts['premium'] ?>)
  </button>
</div>

<?php if (empty($classes)): ?>
<div class="mp-empty">
  <div class="mp-empty-icon">📚</div>
  수강 중인 강의가 없습니다.
</div>
<?php else: ?>

<div class="class-card-list">
  <?php foreach ($classes as $c):
    $isFree    = $c['class_type'] === 'free';
    $isExpired = !$isFree && $c['expire_at'] && (new DateTimeImmutable($c['expire_at'])) < $now;
    $saleEnded = !empty($c['sale_end_at']) && (new DateTimeImmutable($c['sale_end_at'])) < $now;
    $rate      = progressRate((int) $c['done_count'], (int) $c['total_episodes']);
    $thumbOpacity = $isExpired ? ($saleEnded ? '0.4' : '0.6') : '1';
  ?>
  <div class="class-card">

    <!-- 썸네일 -->
    <div class="class-card-thumb-wrap" style="opacity:<?= $thumbOpacity ?>">
      <span class="thumb-badge <?= $isFree ? 'free' : '' ?>">
        <?= $isFree ? '무료' : '프리미엄' ?>
      </span>
      <?php if ($c['thumbnail']): ?>
        <img src="/uploads/class/<?= htmlspecialchars($c['thumbnail']) ?>"
             alt="" style="width:100%;height:100%;object-fit:cover">
      <?php else: ?>
        🎬
      <?php endif; ?>
    </div>

    <!-- 본문 -->
    <div class="class-card-body">
      <div class="class-card-title"><?= htmlspecialchars($c['title']) ?></div>
      <div class="class-card-instructor">
        <?= htmlspecialchars($c['instructor_name'] ?? '') ?>
        <?php if ($c['category_name']): ?>
          · <?= htmlspecialchars($c['category_name']) ?>
        <?php endif; ?>
      </div>

      <?php if (!$isFree): ?>
        <!-- 진도율 -->
        <div class="class-card-progress">
          <div class="progress-label">
            <span>수강률 <?= $rate ?>%</span>
            <span><?= (int)$c['done_count'] ?> / <?= (int)$c['total_episodes'] ?>강</span>
          </div>
          <div class="progress-bar">
            <div class="progress-fill" style="width:<?= $rate ?>%;<?= $rate >= 100 ? 'background:#27ae60' : '' ?>"></div>
          </div>
        </div>

        <?php if ($isExpired): ?>
          <div class="class-expired">⚠ 수강 기간 만료 (<?= formatDate($c['expire_at']) ?>)</div>
          <?php if ($saleEnded): ?>
            <div class="class-expired-closed" style="font-size:11px;color:#aaa;font-weight:500;margin-top:2px">
              🔒 강의 판매 종료 — 기간 연장 불가
            </div>
          <?php endif; ?>
        <?php else: ?>
          <div class="class-card-meta">
            수강 기간: <?= formatDate($c['enrolled_at']) ?> ~
            <?= $c['expire_at'] ? formatDate($c['expire_at']) : '-' ?>
          </div>
        <?php endif; ?>
      <?php else: ?>
        <div class="class-card-meta">
          수강신청일: <?= formatDate($c['enrolled_at']) ?> · 기간 제한 없음
        </div>
      <?php endif; ?>

      <!-- 액션 버튼 -->
      <div class="class-card-actions">
        <?php if (!$isFree): ?>
          <?php if ($isExpired): ?>
            <button class="btn-vimeo" style="opacity:.4;cursor:default" disabled>▶ 강의 보기</button>
            <?php if ($saleEnded): ?>
              <button class="btn-extend-disabled"
                      style="height:34px;padding:0 14px;background:#f0f0f0;color:#aaa;border-radius:6px;font-size:12px;font-weight:600;border:none;cursor:default"
                      onclick="alert('해당 강의의 판매가 종료되어 더 이상 구매할 수 없습니다.')">
                연장 불가
              </button>
            <?php else: ?>
              <a href="/classes/<?= (int)$c['class_idx'] ?>"
                 class="btn-vimeo" style="background:#1a3a5c">+ 기간 연장</a>
            <?php endif; ?>
          <?php else: ?>
            <a href="/classes/<?= (int)$c['class_idx'] ?>/learn" class="btn-vimeo">▶ 강의 보기</a>
          <?php endif; ?>
        <?php endif; ?>

        <?php
          $kakaoUrl = $isFree ? ($c['kakao_url'] ?? '') : ($c['kakao_url'] ?? '');
        ?>
        <?php if ($kakaoUrl): ?>
          <button class="btn-kakao-sm"
                  data-class="<?= (int)$c['class_idx'] ?>"
                  data-url="<?= htmlspecialchars($kakaoUrl) ?>"
                  onclick="openKakao(this)">
            💬 카카오 오픈채팅 입장
          </button>
        <?php endif; ?>
      </div>

    </div>
  </div>
  <?php endforeach; ?>
</div>

<?php endif; ?>

<script>
function openKakao(btn) {
  const classIdx = btn.dataset.class;
  const url      = btn.dataset.url;
  // 클릭 로그 기록 (fire-and-forget)
  fetch('/api/openchat-log/' + classIdx, {
    method: 'POST',
    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    body: ''
  }).catch(() => {});
  window.open(url, '_blank', 'noopener');
}
</script>
