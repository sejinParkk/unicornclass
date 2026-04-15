<?php
/**
 * 강의 수강 페이지 (Vimeo Player)
 *
 * 전달 변수:
 *   $class          — findPublicById() 결과 (chapters[], files[], instructor_* 포함)
 *   $enroll         — lc_enroll 행
 *   $currentChapter — 현재 챕터 배열 (null 가능)
 *   $progressMap    — [chapter_idx => true]
 *   $order          — lc_order 행 (null 가능)
 *   $expireDays     — 만료까지 남은 일수 (null=무제한, 음수=이미 만료)
 */

use App\Core\Csrf;
use App\Repositories\ChapterRepository;

$chapters  = $class['chapters'] ?? [];
$materials = $class['files']    ?? [];
$csrfToken = Csrf::token();

// ── Vimeo video_id 추출 헬퍼 ──────────────────────────
function extractVimeoId(string $url): string {
    if (preg_match('/vimeo\.com\/(?:video\/)?(\d+)/', $url, $m)) {
        return $m[1];
    }
    return '';
}

// ── 현재 챕터 Vimeo embed URL ─────────────────────────
$embedUrl = '';
if ($currentChapter && !empty($currentChapter['vimeo_url'])) {
    $vid = extractVimeoId($currentChapter['vimeo_url']);
    if ($vid) {
        $embedUrl = 'https://player.vimeo.com/video/' . $vid
                  . '?autoplay=1&title=0&byline=0&portrait=0&dnt=1';
    }
}

// ── 파일 크기 표시 헬퍼 ──────────────────────────────
function fmtFileSize(int $bytes): string {
    if ($bytes >= 1048576) return round($bytes / 1048576, 1) . 'MB';
    if ($bytes >= 1024)    return round($bytes / 1024, 1) . 'KB';
    return $bytes . 'B';
}

// ── URL 짧게 표시 ────────────────────────────────────
function shortUrl(string $url): string {
    $host = parse_url($url, PHP_URL_HOST) ?? $url;
    return $host . '/···';
}
?>

<!-- ── 만료 D-7 이하 경고 배너 ────────────────────── -->
<?php if ($expireDays !== null && $expireDays <= 7 && $expireDays >= 0): ?>
<div class="learn-expire-banner">
  ⚠ 수강 기간이 <strong><?= $expireDays ?>일</strong> 후 만료됩니다.
  <a href="/classes/<?= (int)$class['class_idx'] ?>"
     style="margin-left:8px;color:#c0392b;font-weight:700;text-decoration:none">기간 연장 →</a>
</div>
<?php endif; ?>

<!-- ── 브레드크럼 ────────────────────────────────── -->
<div id="learn-breadcrumb">
  <span><a href="/" style="color:inherit;text-decoration:none">홈</a></span>
  <span><a href="/mypage/my-class" style="color:inherit;text-decoration:none">나의 강의</a></span>
  <span><?= htmlspecialchars(mb_substr($class['title'] ?? '', 0, 30)) ?></span>
  <span class="cur">영상 수강</span>
</div>

<!-- ── Vimeo 플레이어 ────────────────────────────── -->
<div id="learn-player-wrap">
  <div class="learn-player-ratio">
    <?php if ($embedUrl): ?>
      <iframe src="<?= htmlspecialchars($embedUrl) ?>"
              allow="autoplay; fullscreen; picture-in-picture"
              allowfullscreen
              title="<?= htmlspecialchars($currentChapter['title'] ?? '') ?>"></iframe>
    <?php else: ?>
      <div class="learn-player-placeholder">
        <div class="pl-icon">▶</div>
        <div class="pl-text">
          <?php if (empty($chapters)): ?>
            등록된 챕터가 없습니다.
          <?php else: ?>
            좌측 챕터 목록에서 강의를 선택해주세요.
          <?php endif; ?>
        </div>
      </div>
    <?php endif; ?>
  </div>
</div>

<!-- ── 강의 정보 바 ──────────────────────────────── -->
<div id="learn-info-bar">
  <div class="learn-info-title"><?= htmlspecialchars($class['title']) ?></div>
  <?php if (!empty($class['category_name'])): ?>
    <span class="learn-info-badge"><?= htmlspecialchars($class['category_name']) ?></span>
  <?php endif; ?>
  <div class="learn-info-meta">
    <?= htmlspecialchars($class['instructor_name'] ?? '') ?>
    <?php if ($currentChapter): ?>
      &nbsp;·&nbsp; <?= htmlspecialchars($currentChapter['title']) ?>
    <?php endif; ?>
  </div>
</div>

<!-- ── 2단 레이아웃 ─────────────────────────────── -->
<div id="learn-content-wrap">

  <!-- ════ 좌측: 챕터 + 강의소개 + 자료 ════ -->
  <div class="learn-content-left">

    <!-- 챕터 목록 -->
    <?php if (!empty($chapters)): ?>
    <div class="learn-chapter-list">
      <div class="learn-section-label">강의 목록</div>
      <?php foreach ($chapters as $i => $ch):
        $chIdx   = (int) $ch['chapter_idx'];
        $isDone  = !empty($progressMap[$chIdx]);
        $isActive = $currentChapter && (int)$currentChapter['chapter_idx'] === $chIdx;
        $dur     = (int)$ch['duration'] > 0
                   ? ChapterRepository::secondsToDisplay((int)$ch['duration'])
                   : '';
        $classes = 'learn-chapter-item'
                 . ($isActive ? ' active' : '')
                 . ($isDone   ? ' done'   : '');
      ?>
      <a href="/classes/<?= (int)$class['class_idx'] ?>/learn?chapter=<?= $chIdx ?>"
         class="<?= $classes ?>">
        <div class="ch-num">
          <?php if ($isDone): ?>✓<?php else: ?><?= $i + 1 ?><?php endif; ?>
        </div>
        <div class="ch-title"><?= htmlspecialchars($ch['title']) ?></div>
        <?php if ($dur): ?>
          <div class="ch-duration"><?= $dur ?></div>
        <?php endif; ?>
        <!-- 완료 체크 버튼 -->
        <button class="progress-toggle"
                data-chapter="<?= $chIdx ?>"
                data-done="<?= $isDone ? '1' : '0' ?>"
                onclick="event.preventDefault(); toggleProgress(this)"
                title="<?= $isDone ? '완료 취소' : '완료 표시' ?>"
                style="background:none;border:1px solid <?= $isDone ? '#27ae60' : '#ddd' ?>;
                       border-radius:4px;padding:2px 6px;font-size:10px;cursor:pointer;
                       color:<?= $isDone ? '#27ae60' : '#bbb' ?>;flex-shrink:0;
                       transition:all .15s">
          <?= $isDone ? '✓완료' : '완료' ?>
        </button>
      </a>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- 강의 소개 -->
    <?php if (!empty($class['description'])): ?>
    <div style="margin-bottom:28px">
      <div class="learn-section-label">강의 소개</div>
      <div class="learn-desc"><?= $class['description'] ?></div>
    </div>
    <?php endif; ?>

    <!-- 강의 자료 -->
    <?php if (!empty($materials)): ?>
    <div>
      <div class="learn-section-label">📎 강의 자료</div>
      <div class="learn-material-list">
        <?php foreach ($materials as $mat): ?>
          <?php if ($mat['file_type'] === 'file'): ?>
          <div class="learn-material-item file-type">
            <div class="learn-material-icon file-icon">📄</div>
            <div class="learn-material-info">
              <div class="learn-material-name"><?= htmlspecialchars($mat['title']) ?></div>
              <div class="learn-material-meta">
                첨부 파일<?= $mat['file_size'] > 0 ? ' · ' . fmtFileSize((int)$mat['file_size']) : '' ?>
              </div>
            </div>
            <a href="/uploads/materials/<?= htmlspecialchars($mat['file_path']) ?>"
               class="btn-material-dl" download>다운로드</a>
          </div>
          <?php else: ?>
          <div class="learn-material-item link-type">
            <div class="learn-material-icon link-icon">🔗</div>
            <div class="learn-material-info">
              <div class="learn-material-name"><?= htmlspecialchars($mat['title']) ?></div>
              <div class="learn-material-meta link-meta">
                외부 링크 · <?= htmlspecialchars(shortUrl($mat['external_url'] ?? '')) ?>
              </div>
            </div>
            <a href="<?= htmlspecialchars($mat['external_url'] ?? '#') ?>"
               target="_blank" rel="noopener" class="btn-material-link">새 탭 열기</a>
          </div>
          <?php endif; ?>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>

  </div><!-- /.learn-content-left -->

  <!-- ════ 우측: 강사 + 카카오 + 결제 정보 ════ -->
  <div class="learn-content-right">

    <!-- 강사 카드 -->
    <div class="learn-side-card">
      <div class="learn-side-head">강사</div>
      <div class="learn-inst-body">
        <div class="learn-inst-avatar">
          <?php if (!empty($class['instructor_photo'])): ?>
            <img src="/uploads/instructor/<?= htmlspecialchars($class['instructor_photo']) ?>"
                 alt="<?= htmlspecialchars($class['instructor_name']) ?>">
          <?php else: ?>
            <?= htmlspecialchars(mb_substr($class['instructor_name'] ?? '?', 0, 1)) ?>
          <?php endif; ?>
        </div>
        <div>
          <div class="learn-inst-name"><?= htmlspecialchars($class['instructor_name'] ?? '') ?></div>
          <?php if (!empty($class['instructor_field'])): ?>
            <div class="learn-inst-sub"><?= htmlspecialchars($class['instructor_field']) ?></div>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- 카카오 오픈채팅 카드 -->
    <?php $kakaoUrl = $enroll['kakao_url'] ?? $class['kakao_url'] ?? ''; ?>
    <?php if ($kakaoUrl): ?>
    <div class="learn-kakao-card">
      <div class="learn-kakao-head">💬 카카오 오픈채팅</div>
      <div class="learn-kakao-body">
        <div class="learn-kakao-desc">
          수강생 전용 오픈채팅방입니다.<br>질문·피드백은 채팅방을 이용해 주세요.
        </div>
        <button class="btn-kakao-enter"
                onclick="openKakao('<?= htmlspecialchars($kakaoUrl) ?>', <?= (int)$class['class_idx'] ?>)">
          💬 오픈채팅 입장하기
        </button>
      </div>
    </div>
    <?php endif; ?>

    <!-- 결제 정보 카드 -->
    <div class="learn-side-card">
      <div class="learn-side-head">수강 정보</div>
      <?php if ($order): ?>
        <?php if ($order['toss_order_id']): ?>
        <div class="learn-order-row">
          <span class="learn-order-label">주문번호</span>
          <span class="learn-order-value" style="font-size:11px">
            <?= htmlspecialchars($order['toss_order_id']) ?>
          </span>
        </div>
        <?php endif; ?>
        <div class="learn-order-row">
          <span class="learn-order-label">결제금액</span>
          <span class="learn-order-value" style="color:#c0392b">
            <?= number_format((int)$order['amount']) ?>원
          </span>
        </div>
        <?php if ($order['paid_at']): ?>
        <div class="learn-order-row">
          <span class="learn-order-label">결제일</span>
          <span class="learn-order-value">
            <?= (new \DateTimeImmutable($order['paid_at']))->format('Y.m.d') ?>
          </span>
        </div>
        <?php endif; ?>
      <?php endif; ?>
      <div class="learn-order-row">
        <span class="learn-order-label">수강 시작</span>
        <span class="learn-order-value">
          <?= (new \DateTimeImmutable($enroll['enrolled_at']))->format('Y.m.d') ?>
        </span>
      </div>
      <div class="learn-order-row">
        <span class="learn-order-label">수강 기간</span>
        <span class="learn-order-value">
          <?php if ($enroll['expire_at']): ?>
            <?= (new \DateTimeImmutable($enroll['expire_at']))->format('Y.m.d') ?>까지
            <?php if ($expireDays !== null && $expireDays >= 0 && $expireDays <= 30): ?>
              <span style="color:#e74c3c;font-size:11px">(D-<?= $expireDays ?>)</span>
            <?php endif; ?>
          <?php else: ?>
            무제한
          <?php endif; ?>
        </span>
      </div>
      <?php
        $doneCount = count(array_filter($progressMap));
        $total     = count($chapters);
        $rate      = $total > 0 ? (int)round($doneCount / $total * 100) : 0;
      ?>
      <?php if ($total > 0): ?>
      <div class="learn-order-row" style="flex-direction:column;gap:6px;align-items:stretch">
        <div style="display:flex;justify-content:space-between">
          <span class="learn-order-label">수강률</span>
          <span class="learn-order-value" id="progress-label"><?= $doneCount ?>/<?= $total ?>강 (<?= $rate ?>%)</span>
        </div>
        <div style="height:4px;background:#f0f0f0;border-radius:2px;overflow:hidden">
          <div style="height:100%;width:<?= $rate ?>%;background:<?= $rate >= 100 ? '#27ae60' : '#c0392b' ?>;border-radius:2px;transition:width .3s"></div>
        </div>
      </div>
      <?php endif; ?>
    </div>

    <!-- 강의 상세 링크 -->
    <a href="/classes/<?= (int)$class['class_idx'] ?>"
       style="display:block;text-align:center;padding:10px;font-size:12px;color:#aaa;text-decoration:none;margin-top:4px">
      ← 강의 상세 페이지로
    </a>

  </div><!-- /.learn-content-right -->

</div><!-- /#learn-content-wrap -->

<script>
const CSRF_TOKEN  = '<?= htmlspecialchars($csrfToken) ?>';
const CLASS_IDX   = <?= (int)$class['class_idx'] ?>;

// ── 챕터 완료 토글 ───────────────────────────────────
function toggleProgress(btn) {
  const chapterIdx = btn.dataset.chapter;
  const isDone     = btn.dataset.done === '1';

  fetch('/api/classes/' + CLASS_IDX + '/progress', {
    method : 'POST',
    headers: {
      'Content-Type'    : 'application/x-www-form-urlencoded',
      'X-Requested-With': 'XMLHttpRequest',
    },
    body: 'csrf_token=' + encodeURIComponent(CSRF_TOKEN)
        + '&chapter_idx=' + encodeURIComponent(chapterIdx),
  })
  .then(r => r.json())
  .then(data => {
    if (!data.success) return;
    const done    = data.is_complete;
    const item    = btn.closest('.learn-chapter-item');
    const numEl   = item.querySelector('.ch-num');
    const numIdx  = numEl.textContent.replace(/\D/g, '') || numEl.textContent;

    btn.dataset.done    = done ? '1' : '0';
    btn.textContent     = done ? '✓완료' : '완료';
    btn.style.borderColor = done ? '#27ae60' : '#ddd';
    btn.style.color       = done ? '#27ae60' : '#bbb';
    btn.title             = done ? '완료 취소' : '완료 표시';

    if (done) {
      item.classList.add('done');
      numEl.textContent = '✓';
    } else {
      item.classList.remove('done');
      // 원래 번호 복원
      numEl.textContent = numIdx || '?';
    }

    updateProgressBar();
  })
  .catch(() => {});
}

// ── 수강률 바 실시간 갱신 ────────────────────────────
function updateProgressBar() {
  const items = document.querySelectorAll('.learn-chapter-item');
  const total = items.length;
  if (total === 0) return;

  let done = 0;
  items.forEach(item => {
    const btn = item.querySelector('.progress-toggle');
    if (btn && btn.dataset.done === '1') done++;
  });

  const rate    = Math.round(done / total * 100);
  const barFill = document.querySelector('#learn-content-wrap .learn-order-row div[style*="background:#"]');
  const label   = document.getElementById('progress-label');

  if (barFill) {
    barFill.style.width      = rate + '%';
    barFill.style.background = rate >= 100 ? '#27ae60' : '#c0392b';
  }
  if (label) {
    label.textContent = done + '/' + total + '강 (' + rate + '%)';
  }
}

// ── 카카오 오픈채팅 클릭 로그 ────────────────────────
function openKakao(url, classIdx) {
  fetch('/api/openchat-log/' + classIdx, {
    method : 'POST',
    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    body   : 'csrf_token=' + encodeURIComponent(CSRF_TOKEN),
  }).catch(() => {});
  window.open(url, '_blank', 'noopener');
}
</script>
