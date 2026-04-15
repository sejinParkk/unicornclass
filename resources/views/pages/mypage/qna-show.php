<?php
/**
 * 1:1 문의 상세
 * 변수: $qna (array), $csrfToken (string)
 */

$catLabel = [
    'class'   => '강의 수강',
    'payment' => '결제/환불',
    'account' => '계정',
    'tech'    => '기술 문제',
    'etc'     => '기타',
];

$isDone = $qna['status'] === 'done';
?>

<div class="mp-content-title">문의 상세</div>

<?php if (isset($_GET['created'])): ?>
<div style="background:#edf7f0;border:1px solid #b2dfdb;border-radius:6px;padding:10px 14px;margin-bottom:14px;font-size:13px;color:#27ae60">
  문의가 접수되었습니다. 답변까지 1~2 영업일이 소요될 수 있습니다.
</div>
<?php elseif (isset($_GET['saved'])): ?>
<div style="background:#edf7f0;border:1px solid #b2dfdb;border-radius:6px;padding:10px 14px;margin-bottom:14px;font-size:13px;color:#27ae60">
  문의가 수정되었습니다.
</div>
<?php endif; ?>

<div class="qna-detail-box">

  <!-- 헤더 -->
  <div class="qna-detail-head">
    <div class="qna-detail-cat">
      <?= htmlspecialchars($catLabel[$qna['category']] ?? $qna['category']) ?>
    </div>
    <div class="qna-detail-title"><?= htmlspecialchars($qna['title']) ?></div>
    <div class="qna-detail-meta">
      <span><?= (new DateTimeImmutable($qna['created_at']))->format('Y.m.d H:i') ?></span>
      <span class="qna-status <?= $qna['status'] ?>">
        <?= $isDone ? '답변완료' : '답변대기' ?>
      </span>
    </div>
  </div>

  <!-- 문의 내용 -->
  <div class="qna-detail-body">
    <?= nl2br(htmlspecialchars($qna['content'])) ?>
  </div>

  <!-- 답변 영역 -->
  <div class="qna-answer-area">
    <div class="qna-answer-label">
      관리자 답변
      <?php if ($isDone && $qna['answered_at']): ?>
        · <?= (new DateTimeImmutable($qna['answered_at']))->format('Y.m.d H:i') ?>
      <?php endif; ?>
    </div>
    <?php if ($isDone && $qna['answer']): ?>
      <div class="qna-answer-text"><?= nl2br(htmlspecialchars($qna['answer'])) ?></div>
    <?php else: ?>
      <div class="qna-no-answer">아직 답변이 등록되지 않았습니다.</div>
    <?php endif; ?>
  </div>

  <!-- 푸터 -->
  <div class="qna-detail-footer">
    <a href="/mypage/qna" class="btn-back-list"
       style="width:auto;height:34px;padding:0 16px;font-size:12px">목록으로</a>

    <?php if ($isDone): ?>
      <span style="font-size:11px;color:#bbb">답변 완료된 문의는 수정·삭제가 불가합니다.</span>
    <?php else: ?>
      <div style="display:flex;gap:8px">
        <a href="/mypage/qna/write?edit=<?= (int)$qna['qna_idx'] ?>"
           class="btn-order-detail" style="height:34px;font-size:12px">수정하기</a>
        <form method="POST" action="/mypage/qna/<?= (int)$qna['qna_idx'] ?>/delete"
              onsubmit="return confirm('문의를 삭제하시겠습니까?')" style="margin:0">
          <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
          <button type="submit" class="btn-refund" style="height:34px;font-size:12px">삭제하기</button>
        </form>
      </div>
    <?php endif; ?>
  </div>

</div>
