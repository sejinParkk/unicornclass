<?php
/**
 * 회원탈퇴
 * 변수: $blockReason (string), $csrfToken (string)
 */

$errMsg = $_GET['err'] ?? '';
?>

<div class="mp-wrap">
  <aside class="mp-sidebar">
    <?php require VIEW_PATH . '/components/mp-user-area.php'; ?>
    <?php require VIEW_PATH . '/components/mp-subnav.php'; ?>
  </aside>
  <div class="mp-content">

<div class="mp-content-title">회원탈퇴</div>

<div class="withdraw-wrap">
  <div class="withdraw-modal" style="border-top:4px solid #e74c3c;border:1px solid #eee;border-radius:16px;padding:36px 32px 28px">

    <div class="withdraw-icon">⚠️</div>
    <div class="withdraw-title">회원 탈퇴</div>

    <!-- 안내 사항 -->
    <ul class="withdraw-desc">
      <li>탈퇴 시 모든 수강 내역, 찜목록, 결제 기록이 삭제됩니다.</li>
      <li>삭제된 데이터는 복구되지 않습니다.</li>
      <li>수강 중인 강의가 있으면 탈퇴가 제한됩니다.</li>
      <li>환불 처리 중인 경우 처리 완료 후 탈퇴 가능합니다.</li>
    </ul>

    <!-- 탈퇴 불가 안내 -->
    <?php if ($blockReason): ?>
    <div style="background:#fef0ee;border:1px solid #f5c6c6;border-radius:8px;padding:12px 14px;margin-bottom:20px;font-size:13px;color:#c0392b;font-weight:600;text-align:center">
      🚫 <?= nl2br(htmlspecialchars($blockReason)) ?>
    </div>
    <a href="/mypage/profile" class="btn-withdraw-cancel" style="display:flex;width:100%">
      ← 정보수정으로 돌아가기
    </a>

    <?php else: ?>

    <form method="POST" action="/mypage/withdraw" id="withdraw-form">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">

      <!-- 확인 입력 -->
      <input type="text" name="confirm_text" id="confirm-input"
             class="withdraw-confirm-input"
             placeholder='확인을 위해 "탈퇴합니다"를 입력하세요'
             autocomplete="off">
      <div data-ajax-err="confirm_text" style="display:none;font-size:12px;color:#c0392b;margin-top:6px;text-align:center"></div>

      <div class="withdraw-btns">
        <a href="/mypage/profile" class="btn-withdraw-cancel">취소</a>
        <button type="submit" id="withdraw-btn" class="btn-withdraw-confirm"
                style="opacity:.4;cursor:default" disabled>
          탈퇴하기
        </button>
      </div>
    </form>

    <?php endif; ?>

  </div>
</div>

<script>
(function () {
  var input = document.getElementById('confirm-input');
  var btn   = document.getElementById('withdraw-btn');
  if (!input || !btn) return;

  input.addEventListener('input', function () {
    var ok = this.value === '탈퇴합니다';
    btn.disabled      = !ok;
    btn.style.opacity = ok ? '1'       : '0.4';
    btn.style.cursor  = ok ? 'pointer' : 'default';
  });

  document.getElementById('withdraw-form').addEventListener('submit', function (e) {
    e.preventDefault();
    if (input.value !== '탈퇴합니다') return;
    if (!confirm('정말로 탈퇴하시겠습니까?\n이 작업은 되돌릴 수 없습니다.')) return;
    ajaxSubmit(this);
  });
})();
</script>

  </div><!-- /.mp-content -->
</div><!-- /.mp-wrap -->
