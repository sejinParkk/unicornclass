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
<div class="sub_index">
  <div class="inner">
    <?php require VIEW_PATH . '/components/mp-user-area.php'; ?>
    <div class="sub_page_flex">
      <?php require VIEW_PATH . '/components/mp-subnav.php'; ?>
      <div class="sub_page_contents">
        <div class="page-section-title">1:1 문의</div>

        <div class="qna-filter">
          <div class="faq-filters">
            <a href="/mypage/qna" class="faq-filter-btn <?= $status === '' ? 'active' : '' ?>">전체</a>
            <a href="/mypage/qna?status=wait" class="faq-filter-btn <?= $status === 'wait' ? 'active' : '' ?>">답변대기</a>
            <a href="/mypage/qna?status=done" class="faq-filter-btn <?= $status === 'done' ? 'active' : '' ?>">답변완료</a>
          </div>
          <a href="/mypage/qna/write" class="qna-write-btn">
            <img src="/assets/img/icon_write.svg" alt="">
            <span>문의 작성</span>
          </a>
        </div>
        <?php
          $backQuery = http_build_query(array_filter(['status' => $status, 'page' => $page > 1 ? $page : null]));
          $backUrl   = '/mypage/qna' . ($backQuery ? '?' . $backQuery : '');
        ?>       
        
        <?php if (empty($qnaList)): ?>
          <div class="notice-empty"><?= $status === 'wait' ? '답변 대기 중인' : ($status === 'done' ? '답변 완료된' : '') ?> 문의가 없습니다.</div>
        <?php else: ?>
          <div class="qna-list">
          <?php foreach ($qnaList as $q): ?>
          <div class="qna-item">
            <div class="qna-state">
              <span class="qna-status <?= $q['status'] ?>">
                <?= $q['status'] === 'wait' ? '답변대기' : '답변완료' ?>
              </span>
            </div>
            <a href="/mypage/qna/<?= (int)$q['qna_idx'] ?>?back_page=<?= $page ?>&back_status=<?= urlencode($status) ?>" class="qna-body">
              <div class="qna-title"><?= htmlspecialchars($q['title']) ?></div>
              <div class="qna-date"><?= htmlspecialchars($catLabel[$q['category']] ?? $q['category']) ?>ㅣ<?= (new DateTimeImmutable($q['created_at']))->format('Y.m.d') ?></div>
            </a>
            <div class="qna-del">
              <?php if ($q['status'] === 'wait'): ?>
              <button type="button" class="btn-qna-del" onclick="deleteQna(<?= (int)$q['qna_idx'] ?>, this)">삭제</button>
              <?php endif; ?>
            </div>
          </div>
          <?php endforeach; ?>
        </div>

        <?php if ($totalPages > 1): ?>
          <div class="pagination">
            <a href="?<?= http_build_query(array_filter(['status' => $status, 'page' => max(1, $page - 1)])) ?>" class="page-btn page-prev <?= $page <= 1 ? 'disabled' : '' ?>"></a>
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="?<?= http_build_query(array_filter(['status' => $status, 'page' => $i])) ?>" class="page-btn <?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
            <?php endfor; ?>
            <a href="?<?= http_build_query(array_filter(['status' => $status, 'page' => min($totalPages, $page + 1)])) ?>" class="page-btn page-next <?= $page >= $totalPages ? 'disabled' : '' ?>"></a>
          </div>
        <?php endif; ?>

        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<div class="auth-modal-overlay" id="deleteConfirmModal" style="display:none">
  <div class="auth-modal-card">
    <div class="auth-modal-title">문의 삭제</div>
    <div class="auth-modal-desc">삭제시 복구할 수 없습니다.<br>문의를 삭제하시겠습니까?</div>
    <div class="auth-modal-btn-flex">
      <button type="button" class="btn-next btn-cancel" onclick="closeModal('deleteConfirmModal')">취소</button>
      <button type="button" class="btn-next btn-error" id="deleteConfirmBtn">삭제하기</button>
    </div>
  </div>
</div>

<script>
var _qnaCsrf     = '<?= htmlspecialchars(\App\Core\Csrf::token()) ?>';
var _pendingIdx  = null;
var _pendingBtn  = null;

function deleteQna(qnaIdx, btn) {
  _pendingIdx = qnaIdx;
  _pendingBtn = btn;
  openModal('deleteConfirmModal');
}

document.getElementById('deleteConfirmBtn').addEventListener('click', function () {
  if (!_pendingIdx) return;
  closeModal('deleteConfirmModal');
  _pendingBtn.disabled = true;
  var fd = new FormData();
  fd.append('csrf_token', _qnaCsrf);
  fetch('/mypage/qna/' + _pendingIdx + '/delete', {
    method: 'POST', body: fd,
    headers: { 'X-Requested-With': 'XMLHttpRequest' }
  })
  .then(function(r) { return r.json(); })
  .then(function(data) {
    if (data.ok) {
      location.href = '<?= htmlspecialchars($backUrl) ?>';
    } else {
      alert('삭제할 수 없습니다.');
      _pendingBtn.disabled = false;
    }
  })
  .catch(function() {
    alert('오류가 발생했습니다. 다시 시도해주세요.');
    _pendingBtn.disabled = false;
  })
  .finally(function() {
    _pendingIdx = null;
    _pendingBtn = null;
  });
});
</script>
