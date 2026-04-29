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

<div class="sub_index">
  <div class="inner">
    <?php require VIEW_PATH . '/components/mp-user-area.php'; ?>
    <div class="sub_page_flex">
      <?php require VIEW_PATH . '/components/mp-subnav.php'; ?>
      <div class="sub_page_contents">
        <div class="page-section-title">나의 강의 <!--<span>총 <?= $counts['all'] ?>개</span>--></div>

        <div class="faq-filters">
          <button class="faq-filter-btn <?= $type === 'all'     ? 'active' : '' ?>" onclick="location.href='/mypage/my-class'">
            전체 <strong><?= $counts['all'] ?></strong>
          </button>
          <button class="faq-filter-btn <?= $type === 'free'    ? 'active' : '' ?>" onclick="location.href='/mypage/my-class?type=free'">
            무료강의 <strong><?= $counts['free'] ?></strong>
          </button>
          <button class="faq-filter-btn <?= $type === 'premium' ? 'active' : '' ?>" onclick="location.href='/mypage/my-class?type=premium'">
            프리미엄강의 <strong><?= $counts['premium'] ?></strong>
          </button>
        </div>

        <?php if (empty($classes)): ?>
        <div class="notice-empty">수강 중인 강의가 없습니다.</div>
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
              <p class="thumb-badge <?= $isFree ? 'free' : '' ?>">
                <img src="/assets/img/hero_star_fff.svg" alt="">
                <span><?= $isFree ? '무료' : '프리미엄' ?> 강의</span>
              </p>
              <?php if ($c['thumbnail']): ?>
                <img src="/uploads/class/<?= htmlspecialchars($c['thumbnail']) ?>" alt="" class="on_img">
              <?php else: ?>
                <img src="/assets/img/logo.svg" alt="" class="no_img">
              <?php endif; ?>
            </div>

            <!-- 본문 -->
            <div class="class-card-body">
              <?if($isFree){?>
              <p class="calss-card-body-cate free">무료강의</p>
              <?php }else{?>
              <p class="calss-card-body-cate premium">Premium</p>
              <?php }?>
              <div class="class-card-title"><?= htmlspecialchars($c['title']) ?></div>
              <div class="class-card-instructor">
                <?= htmlspecialchars($c['instructor_name'] ?? '') ?>
                <?php if ($c['category_name']): ?>
                  · <?= htmlspecialchars($c['category_name']) ?>
                <?php endif; ?>
              </div>

              <?php if (!$isFree): ?>                
                <?php if ($isExpired): ?>
                  <div class="class-expired">⚠ 수강 기간 만료 (<?= formatDate($c['expire_at']) ?>)</div>
                  <?php if ($saleEnded): ?>
                    <div class="class-expired-closed" style="font-size:11px;color:#aaa;font-weight:500;margin-top:2px">
                      🔒 강의 판매 종료 — 기간 연장 불가
                    </div>
                  <?php endif; ?>
                <?php else: ?>
                  <div class="class-card-meta">
                    <img src="/assets/img/icon_class_calendar.svg" alt="">
                    <span>
                    수강일: <?= formatDate($c['enrolled_at']) ?> ~ <?= $c['expire_at'] ? formatDate($c['expire_at']) : '-' ?>
                    </span>
                  </div>
                <?php endif; ?>

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
              <?php else: ?>
                <div class="class-card-meta">
                  <img src="/assets/img/icon_class_calendar.svg" alt="">
                  <span>기간 제한 없음</span>
                  <!-- 수강신청일: <?= formatDate($c['enrolled_at']) ?> -->
                </div>
              <?php endif; ?>

              <!-- 액션 버튼 -->
              <div class="class-card-actions">
                <?php if (!$isFree): ?>
                  <?php if ($isExpired): ?>
                    <button class="myclass_btn" disabled>
                      <img src="/assets/img/icon_class_play.svg" alt="">
                      <span>강의 보기</span>
                    </button>
                    <?php if ($saleEnded): ?>
                      <button class="myclass_btn myclass_disabled" onclick="alert('해당 강의의 판매가 종료되어 더 이상 구매할 수 없습니다.')">
                        연장불가(판매종료)
                      </button>
                    <?php else: ?>
                      <a href="/classes/<?= (int)$c['class_idx'] ?>" class="myclass_btn myclass_add">
                        <img src="/assets/img/icon_class_ext.svg" alt="">
                        <span>기간 연장하기</span>                        
                      </a>
                    <?php endif; ?>
                  <?php else: ?>
                    <a href="/classes/<?= (int)$c['class_idx'] ?>/learn" class="myclass_btn">
                      <img src="/assets/img/icon_class_play.svg" alt="">
                      <span>강의 보기</span>
                    </a>
                  <?php endif; ?>
                <?php endif; ?>

                <?php
                  $kakaoUrl = $isFree ? ($c['kakao_url'] ?? '') : ($c['kakao_url'] ?? '');
                ?>
                <?php if ($kakaoUrl): ?>
                  <button class="myclass_btn myclass_kakao"
                          data-class="<?= (int)$c['class_idx'] ?>"
                          data-url="<?= htmlspecialchars($kakaoUrl) ?>"
                          onclick="openKakao(this)">                    
                    <img src="/assets/img/icon_class_kakao.svg" alt="">
                    <span>오픈채팅 입장</span>
                  </button>
                <?php endif; ?>
              </div>

            </div>
          </div>
          <?php endforeach; ?>
        </div>

        <?php if ($totalPages > 1): ?>
        <div class="pagination">
          <a href="?type=<?= urlencode($type) ?>&page=<?= max(1, $page - 1) ?>" class="page-btn page-prev <?= $page <= 1 ? 'disabled' : '' ?>"></a>
          <?php for ($i = 1; $i <= $totalPages; $i++): ?>
          <a href="?type=<?= urlencode($type) ?>&page=<?= $i ?>" class="page-btn <?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
          <?php endfor; ?>
          <a href="?type=<?= urlencode($type) ?>&page=<?= min($totalPages, $page + 1) ?>" class="page-btn page-next <?= $page >= $totalPages ? 'disabled' : '' ?>"></a>
        </div>
        <?php endif; ?>

        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

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