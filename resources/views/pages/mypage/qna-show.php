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

<div class="sub_index">
  <div class="inner">
    <?php require VIEW_PATH . '/components/mp-user-area.php'; ?>
    <div class="sub_page_flex">
      <?php require VIEW_PATH . '/components/mp-subnav.php'; ?>
      <div class="sub_page_contents">
        <div class="page-section-title">문의 상세</div>

        <?php if (isset($_GET['created'])): ?>
        <div style="background:#edf7f0;border:1px solid #b2dfdb;border-radius:6px;padding:10px 14px;margin-bottom:14px;font-size:13px;color:#27ae60">
          문의가 접수되었습니다. 답변까지 1~2 영업일이 소요될 수 있습니다.
        </div>
        <?php elseif (isset($_GET['saved'])): ?>
        <div style="background:#edf7f0;border:1px solid #b2dfdb;border-radius:6px;padding:10px 14px;margin-bottom:14px;font-size:13px;color:#27ae60">
          문의가 수정되었습니다.
        </div>
        <?php endif; ?>

        <!-- 헤더 -->
        <div class="notice-detail-header">
          <div class="notice-detail-badge">
            <?= htmlspecialchars($catLabel[$qna['category']] ?? $qna['category']) ?>
          </div>
          <div class="notice-detail-title"><?= htmlspecialchars($qna['title']) ?></div>
          <div class="notice-detail-meta">
            <p>작성일 <?= (new DateTimeImmutable($qna['created_at']))->format('Y.m.d H:i') ?></p>
          </div>
          <span class="qna-status <?= $qna['status'] ?>">
            <?= $isDone ? '답변완료' : '답변대기' ?>
          </span>
        </div>

        <!-- 본문 -->
        <div class="notice-detail-body qna-detail-body">
          <?= nl2br(htmlspecialchars($qna['content'])) ?>
          <?php if (!empty($qna['file_path'])): ?>
          <?php $isPdf = strtolower(pathinfo($qna['file_path'], PATHINFO_EXTENSION)) === 'pdf'; ?>
          <div class="qna-file-attach">
            <span class="qna-file-attach-label">첨부파일</span>
            <a href="/uploads/qna/<?= htmlspecialchars($qna['file_path']) ?>" target="_blank" rel="noopener" class="qna-file-attach-link">
              <?= htmlspecialchars($qna['file_path']) ?>
            </a>
          </div>
          <?php endif; ?>
          
          <?php if ($isDone): ?>
          <div class="qna_modify_box">
            <span>답변 완료된 문의는 수정·삭제가 불가합니다.</span>
          </div>
          <?php else: ?>
            <div class="qna_modify_box">
              <a href="/mypage/qna/write?edit=<?= (int)$qna['qna_idx'] ?>" class="qna_modify_btn">수정하기</a>
              <button type="button" class="qna_modify_btn" onclick="deleteQna(<?= (int)$qna['qna_idx'] ?>, this)">삭제하기</button>
            </div>
          <?php endif; ?>
        </div>        

        <!-- 답변 영역 -->
        <div class="qna-answer-area">
          <div class="qna-answer-label <?php if ($isDone && $qna['answer']): ?>done<?php endif; ?>">관리자 답변</div>
          <?php if ($isDone && $qna['answered_at']): ?>
          <p class="qna-answer-date"><?= (new DateTimeImmutable($qna['answered_at']))->format('Y.m.d H:i') ?></p>
          <?php endif; ?>
          <?php if ($isDone && $qna['answer']): ?>
            <div class="qna-answer-text"><?= nl2br(htmlspecialchars($qna['answer'])) ?></div>
          <?php else: ?>
            <div class="qna-answer-text qna-no-answer">아직 답변이 등록되지 않았습니다.</div>
          <?php endif; ?>
        </div>

        <!-- 목록으로 -->
        <?php
          $backPage   = max(1, (int) ($_GET['back_page'] ?? 1));
          $backStatus = in_array($_GET['back_status'] ?? '', ['wait', 'done']) ? $_GET['back_status'] : '';
          $backQuery  = http_build_query(array_filter(['status' => $backStatus, 'page' => $backPage > 1 ? $backPage : null]));
          $listUrl    = '/mypage/qna' . ($backQuery ? '?' . $backQuery : '');
        ?>
        <div class="board_btn_box">
          <a href="<?= htmlspecialchars($listUrl) ?>" class="board_btn">목록보기</a>
        </div>

      </div>
    </div>
  </div>
</div>

<script>
function deleteQna(qnaIdx, btn) {
  if (!confirm('문의를 삭제하시겠습니까?')) return;
  btn.disabled = true;
  var fd = new FormData();
  fd.append('csrf_token', '<?= htmlspecialchars($csrfToken) ?>');
  fetch('/mypage/qna/' + qnaIdx + '/delete', {
    method: 'POST', body: fd,
    headers: { 'X-Requested-With': 'XMLHttpRequest' }
  })
  .then(function(r) { return r.json(); })
  .then(function(data) {
    if (data.ok) {
      alert('문의가 삭제되었습니다.');
      location.href = '<?= htmlspecialchars($listUrl) ?>';
    } else {
      alert('삭제할 수 없습니다.');
      btn.disabled = false;
    }
  })
  .catch(function() {
    alert('오류가 발생했습니다. 다시 시도해주세요.');
    btn.disabled = false;
  });
}
</script>
