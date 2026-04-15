<?php
/**
 * 1:1 문의 목록
 * 변수: $qnaList (array), $status (string)
 */

$catLabel = [
    'class'   => '강의 수강',
    'payment' => '결제/환불',
    'account' => '계정',
    'tech'    => '기술 문제',
    'etc'     => '기타',
];
?>

<div class="mp-content-title">1:1 문의</div>

<!-- 필터 + 작성 버튼 -->
<div style="display:flex;align-items:center;gap:8px;margin-bottom:16px;">
  <div class="qna-filter" style="margin-bottom:0;flex:1">
    <a href="/mypage/qna"
       class="qna-filter-btn <?= $status === '' ? 'active' : '' ?>"
       style="text-decoration:none">전체</a>
    <a href="/mypage/qna?status=wait"
       class="qna-filter-btn <?= $status === 'wait' ? 'active' : '' ?>"
       style="text-decoration:none">답변대기</a>
    <a href="/mypage/qna?status=done"
       class="qna-filter-btn <?= $status === 'done' ? 'active' : '' ?>"
       style="text-decoration:none">답변완료</a>
  </div>
  <a href="/mypage/qna/write" class="qna-write-btn">+ 문의 작성</a>
</div>

<?php if (isset($_GET['deleted'])): ?>
<div style="background:#edf7f0;border:1px solid #b2dfdb;border-radius:6px;padding:10px 14px;margin-bottom:14px;font-size:13px;color:#27ae60">
  문의가 삭제되었습니다.
</div>
<?php endif; ?>

<?php if (empty($qnaList)): ?>
<div class="mp-empty">
  <div class="mp-empty-icon">💬</div>
  <?= $status === 'wait' ? '답변 대기 중인' : ($status === 'done' ? '답변 완료된' : '') ?> 문의가 없습니다.<br>
  <a href="/mypage/qna/write" style="color:#1a3a5c;font-size:13px;margin-top:8px;display:inline-block">문의 작성하기</a>
</div>
<?php else: ?>

<div class="qna-list">
  <?php foreach ($qnaList as $q): ?>
  <div style="border-bottom:1px solid #eee;padding:14px 0;display:flex;align-items:flex-start;gap:12px">
    <span class="qna-status <?= $q['status'] ?>">
      <?= $q['status'] === 'wait' ? '답변대기' : '답변완료' ?>
    </span>
    <a href="/mypage/qna/<?= (int)$q['qna_idx'] ?>" class="qna-body" style="flex:1;text-decoration:none">
      <div class="qna-cat"><?= htmlspecialchars($catLabel[$q['category']] ?? $q['category']) ?></div>
      <div class="qna-title"><?= htmlspecialchars($q['title']) ?></div>
      <div class="qna-date"><?= (new DateTimeImmutable($q['created_at']))->format('Y.m.d') ?></div>
    </a>
    <div style="flex-shrink:0">
      <?php if ($q['status'] === 'wait'): ?>
      <form method="POST" action="/mypage/qna/<?= (int)$q['qna_idx'] ?>/delete"
            onsubmit="return confirm('문의를 삭제하시겠습니까?')">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\App\Core\Csrf::token()) ?>">
        <button type="submit" class="btn-qna-del">삭제</button>
      </form>
      <?php endif; ?>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<?php endif; ?>
