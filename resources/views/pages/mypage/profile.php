<?php
/**
 * 정보수정
 * 변수: $member, $csrfToken, $errors, $pwErrors, $saved, $pwChanged
 */

$isSocial = empty($member['mb_password']);
$pwError  = $_GET['pw_error'] ?? null;

$profileAction = isset($_POST['_action']) && $_POST['_action'] === 'profile';
$passwordAction = isset($_POST['_action']) && $_POST['_action'] === 'password';
?>

<div class="mp-content-title">정보수정</div>

<!-- ── 기본 정보 ─────────────────────────── -->
<div class="profile-form">
  <div class="profile-section-title">기본 정보</div>

  <?php if ($saved): ?>
  <div style="background:#edf7f0;border:1px solid #b2dfdb;border-radius:6px;padding:10px 14px;margin-bottom:16px;font-size:13px;color:#27ae60">
    ✓ 회원정보가 저장되었습니다.
  </div>
  <?php endif; ?>

  <form method="POST" action="/mypage/profile">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
    <input type="hidden" name="_action" value="profile">

    <!-- 아이디 -->
    <div class="profile-field">
      <label class="profile-label">
        아이디 <span class="note">(수정 불가)</span>
      </label>
      <input type="text" class="profile-input" value="<?= htmlspecialchars($member['mb_id']) ?>" disabled>
    </div>

    <!-- 소셜 계정 표시 -->
    <?php if (!empty($member['signup_type']) && $member['signup_type'] !== 'email'): ?>
    <div class="profile-field">
      <label class="profile-label">소셜 계정</label>
      <div style="display:inline-flex;align-items:center;gap:5px;background:#f0f0f0;border-radius:6px;padding:6px 12px;font-size:12px;color:#555;margin-top:2px">
        <?= $member['signup_type'] === 'kakao' ? '카카오' : '네이버' ?> 로그인
      </div>
    </div>
    <?php endif; ?>

    <!-- 이름 -->
    <div class="profile-field">
      <label class="profile-label">이름 <span class="req">*</span></label>
      <input type="text" name="mb_name" class="profile-input"
             value="<?= htmlspecialchars($profileAction ? ($_POST['mb_name'] ?? '') : ($member['mb_name'] ?? '')) ?>"
             placeholder="이름을 입력하세요" maxlength="20"
             style="<?= isset($errors['mb_name']) ? 'border-color:#c0392b' : '' ?>">
      <?php if (isset($errors['mb_name'])): ?>
      <div style="font-size:11px;color:#c0392b;margin-top:4px"><?= htmlspecialchars($errors['mb_name']) ?></div>
      <?php endif; ?>
    </div>

    <!-- 이메일 -->
    <div class="profile-field">
      <label class="profile-label">이메일</label>
      <input type="email" name="mb_email" class="profile-input"
             value="<?= htmlspecialchars($profileAction ? ($_POST['mb_email'] ?? '') : ($member['mb_email'] ?? '')) ?>"
             placeholder="이메일 주소 (선택)"
             style="<?= isset($errors['mb_email']) ? 'border-color:#c0392b' : '' ?>">
      <?php if (isset($errors['mb_email'])): ?>
      <div style="font-size:11px;color:#c0392b;margin-top:4px"><?= htmlspecialchars($errors['mb_email']) ?></div>
      <?php endif; ?>
    </div>

    <!-- 마케팅 수신 동의 -->
    <div class="profile-field" style="margin-bottom:20px">
      <label class="profile-label">마케팅 수신 동의</label>
      <label class="agree-row" style="margin-top:4px">
        <input type="checkbox" name="mb_mailling" value="1"
               style="width:16px;height:16px;accent-color:#1a3a5c;cursor:pointer"
               <?= ($profileAction ? isset($_POST['mb_mailling']) : (int)($member['mb_mailling'] ?? 0)) ? 'checked' : '' ?>>
        <span class="agree-label">이메일 수신 동의 <span class="opt">(선택)</span></span>
      </label>
      <label class="agree-row">
        <input type="checkbox" name="mb_sms" value="1"
               style="width:16px;height:16px;accent-color:#1a3a5c;cursor:pointer"
               <?= ($profileAction ? isset($_POST['mb_sms']) : (int)($member['mb_sms'] ?? 0)) ? 'checked' : '' ?>>
        <span class="agree-label">SMS 수신 동의 <span class="opt">(선택)</span></span>
      </label>
    </div>

    <button type="submit" class="btn-save-profile">저장하기</button>
  </form>
</div>

<!-- ── 비밀번호 변경 ──────────────────────── -->
<div class="profile-form">
  <div class="profile-section-title">비밀번호 변경</div>

  <?php if ($pwChanged): ?>
  <div style="background:#edf7f0;border:1px solid #b2dfdb;border-radius:6px;padding:10px 14px;margin-bottom:16px;font-size:13px;color:#27ae60">
    ✓ 비밀번호가 변경되었습니다.
  </div>
  <?php endif; ?>
  <?php if ($pwError): ?>
  <div style="background:#fef0ee;border:1px solid #f5c6c6;border-radius:6px;padding:10px 14px;margin-bottom:16px;font-size:13px;color:#c0392b">
    ⚠ <?= htmlspecialchars($pwError) ?>
  </div>
  <?php endif; ?>

  <?php if ($isSocial): ?>
  <p style="font-size:13px;color:#aaa">소셜 계정으로 가입하셨습니다. 비밀번호를 변경할 수 없습니다.</p>
  <?php else: ?>

  <!-- Ajax 결과 메시지 영역 -->
  <div id="pw-result" style="display:none;border-radius:6px;padding:10px 14px;margin-bottom:16px;font-size:13px"></div>

  <div class="profile-field">
    <label class="profile-label">현재 비밀번호 <span class="req">*</span></label>
    <div class="profile-input-wrap">
      <input type="password" id="pw-current" class="profile-input" placeholder="현재 비밀번호">
      <button type="button" class="eye-btn" onclick="togglePw('pw-current',this)">👁</button>
    </div>
    <div id="err-current_password" style="display:none;font-size:11px;color:#c0392b;margin-top:4px"></div>
  </div>

  <div class="profile-field">
    <label class="profile-label">새 비밀번호 <span class="req">*</span></label>
    <div class="profile-input-wrap">
      <input type="password" id="pw-new" class="profile-input" placeholder="영문+숫자+특수문자 8자 이상">
      <button type="button" class="eye-btn" onclick="togglePw('pw-new',this)">👁</button>
    </div>
    <div id="err-new_password" style="display:none;font-size:11px;color:#c0392b;margin-top:4px"></div>
    <div style="font-size:11px;color:#aaa;margin-top:4px">8자 이상 입력해주세요.</div>
  </div>

  <div class="profile-field">
    <label class="profile-label">새 비밀번호 확인 <span class="req">*</span></label>
    <div class="profile-input-wrap">
      <input type="password" id="pw-confirm" class="profile-input" placeholder="새 비밀번호를 다시 입력">
      <button type="button" class="eye-btn" onclick="togglePw('pw-confirm',this)">👁</button>
    </div>
    <div id="err-confirm_password" style="display:none;font-size:11px;color:#c0392b;margin-top:4px"></div>
  </div>

  <div style="font-size:11px;color:#aaa;margin-bottom:16px">비밀번호를 변경하지 않을 경우 빈칸으로 두세요.</div>

  <button type="button" class="btn-save-profile" onclick="submitPwChange()">비밀번호 변경</button>

  <?php endif; ?>
</div>

<!-- ── 회원탈퇴 ───────────────────────────── -->
<div style="background:#fff;border-radius:10px;border:1px solid #f5c6c6;padding:20px;margin-bottom:8px">
  <div style="font-size:13px;font-weight:700;color:#e74c3c;margin-bottom:8px;display:flex;align-items:center;gap:6px">
    ⚠ 회원탈퇴
  </div>
  <div style="font-size:12px;color:#888;line-height:1.8;margin-bottom:16px">
    탈퇴 시 모든 수강 내역, 찜목록이 삭제되며 복구되지 않습니다.<br>
    수강 중이거나 환불 처리 중인 경우 탈퇴가 제한됩니다.
  </div>
  <a href="/mypage/withdraw"
     style="display:inline-flex;align-items:center;height:42px;padding:0 24px;background:#fff;color:#e74c3c;border:1.5px solid #e74c3c;border-radius:8px;font-size:13px;font-weight:700;text-decoration:none">
    회원탈퇴
  </a>
</div>

<script>
function togglePw(id, btn) {
  const inp = document.getElementById(id);
  if (!inp) return;
  inp.type = inp.type === 'password' ? 'text' : 'password';
  btn.style.opacity = inp.type === 'text' ? '1' : '0.4';
}

function submitPwChange() {
  // 인라인 오류 초기화
  ['current_password','new_password','confirm_password'].forEach(function(k) {
    const el = document.getElementById('err-' + k);
    if (el) { el.style.display = 'none'; el.textContent = ''; }
  });
  const result = document.getElementById('pw-result');
  result.style.display = 'none';

  const body = new URLSearchParams({
    csrf_token       : '<?= htmlspecialchars($csrfToken) ?>',
    _action          : 'password',
    current_password : document.getElementById('pw-current').value,
    new_password     : document.getElementById('pw-new').value,
    confirm_password : document.getElementById('pw-confirm').value,
  });

  fetch('/mypage/profile', {
    method : 'POST',
    headers: {
      'Content-Type'    : 'application/x-www-form-urlencoded',
      'X-Requested-With': 'XMLHttpRequest',
    },
    body: body.toString(),
  })
  .then(function(r) { return r.json(); })
  .then(function(data) {
    if (data.success) {
      result.style.cssText = 'display:block;background:#edf7f0;border:1px solid #b2dfdb;border-radius:6px;padding:10px 14px;margin-bottom:16px;font-size:13px;color:#27ae60';
      result.textContent = '✓ 비밀번호가 변경되었습니다.';
      // 입력 필드 초기화
      ['pw-current','pw-new','pw-confirm'].forEach(function(id) {
        const el = document.getElementById(id);
        if (el) el.value = '';
      });
    } else {
      const errors = data.errors || {};
      Object.keys(errors).forEach(function(k) {
        const el = document.getElementById('err-' + k);
        if (el) { el.style.display = 'block'; el.textContent = errors[k]; }
      });
      result.style.cssText = 'display:block;background:#fef0ee;border:1px solid #f5c6c6;border-radius:6px;padding:10px 14px;margin-bottom:16px;font-size:13px;color:#c0392b';
      result.textContent = '입력 내용을 확인해주세요.';
    }
  })
  .catch(function() {
    result.style.cssText = 'display:block;background:#fef0ee;border:1px solid #f5c6c6;border-radius:6px;padding:10px 14px;margin-bottom:16px;font-size:13px;color:#c0392b';
    result.textContent = '오류가 발생했습니다. 다시 시도해주세요.';
  });
}
</script>
