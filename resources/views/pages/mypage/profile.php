<?php
/**
 * 정보수정
 * 변수: $member, $csrfToken, $errors, $saved, $pwChanged
 */

$isSocial = empty($member['mb_password']);
?>

<form id="profileForm" method="POST" action="/mypage/profile">
<div class="sub_index profile_area">
  <div class="inner">
    <?php require VIEW_PATH . '/components/mp-user-area.php'; ?>
    <div class="sub_page_flex">
      <?php require VIEW_PATH . '/components/mp-subnav.php'; ?>
      <div class="sub_page_contents">
        <div class="page-section-title">정보 수정</div>

        <?php if (isset($_GET['saved'])): ?>
        <!-- <div class="form-msg success" id="profileSaved">✓ 저장되었습니다.</div> -->
        <?php endif; ?>

        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
        <input type="hidden" name="phone_verified" id="phone_verified" value="0">
        <input type="hidden" name="mb_phone" id="hidden_phone" value="<?= htmlspecialchars($member['mb_phone'] ?? '') ?>">

        <div class="apply-section">
          <div class="apply-section-title">기본 정보</div>
          <div class="form-field">
            <label class="form-label">아이디</label>
            <input class="form-input" type="text" value="<?= htmlspecialchars($member['mb_id']) ?>" disabled>
          </div>

          <?php if (!empty($member['signup_type']) && $member['signup_type'] !== 'email'): ?>
          <div class="form-field">
            <label class="form-label">소셜 계정</label>
            <div style="display:inline-flex;align-items:center;gap:5px;background:#f0f0f0;border-radius:6px;padding:6px 12px;font-size:12px;color:#555;margin-top:2px">
              <?= $member['signup_type'] === 'kakao' ? '카카오' : '네이버' ?> 로그인
            </div>
          </div>
          <?php endif; ?>

          <div class="form-field">
            <label class="form-label">이름<span class="req">*</span></label>
            <input type="text" name="mb_name" id="inp-name" class="form-input"
                  value="<?= htmlspecialchars($member['mb_name'] ?? '') ?>"
                  placeholder="이름을 입력하세요" maxlength="20">
          </div>

          <div class="form-field ver4">
            <label class="form-label">연락처<span class="req">*</span></label>
            <div class="form-field-wrap">
              <div class="form-field-flex">
                <input type="text" id="inp-phone" class="form-input"
                       value="<?= htmlspecialchars($member['mb_phone'] ?? '') ?>"
                       maxlength="13" disabled placeholder="휴대폰 번호 입력">
                <button type="button" id="event_btn1">변경</button>
              </div>
              <div class="form-field-flex" id="cert_box" style="display:none">
                <input type="text" id="inp-cert-code" class="form-input"
                       maxlength="6" placeholder="인증번호 6자리">
                <span id="cert-timer" class="cert-timer"></span>
                <button type="button" id="event_btn2">인증하기</button>
              </div>
              <div class="field-confirmed" id="field-confirmed" <?= empty($member['mb_phone']) ? 'style="display:none"' : '' ?>>✓ 인증된 번호입니다.</div>
            </div>
          </div>

          <div class="form-field">
            <label class="form-label">이메일</label>
            <input type="email" name="mb_email" class="form-input"
                  value="<?= htmlspecialchars($member['mb_email'] ?? '') ?>"
                  placeholder="이메일 주소 (선택)">
          </div>
        </div>

        <div class="apply-section">
          <div class="apply-section-title">비밀번호 변경</div>
          <?php if ($isSocial): ?>
            <p style="font-size:13px;color:#aaa">소셜 계정으로 가입하셨습니다. 비밀번호를 변경할 수 없습니다.</p>
          <?php else: ?>
            <div class="form-field">
              <label class="form-label">현재 비밀번호</label>
              <div class="form-field-wrap">
                <div class="form-field-flex">
                  <input type="password" name="current_password" id="pw-current" class="form-input" placeholder="현재 비밀번호 입력">
                  <button type="button" class="eye-btn" onclick="togglePw('pw-current',this)">👁</button>
                </div>
              </div>
            </div>
            <div class="form-field">
              <label class="form-label">새 비밀번호</label>
              <div class="form-field-wrap">
                <div class="form-field-flex">
                  <input type="password" name="new_password" id="pw-new" class="form-input" placeholder="영문+숫자+특수문자 8자 이상">
                  <button type="button" class="eye-btn" onclick="togglePw('pw-new',this)">👁</button>
                </div>
              </div>
            </div>
            <div class="form-field">
              <label class="form-label">새 비밀번호 확인</label>
              <div class="form-field-wrap">
                <div class="form-field-flex">
                  <input type="password" name="confirm_password" id="pw-confirm" class="form-input" placeholder="새 비밀번호를 다시 입력">
                  <button type="button" class="eye-btn" onclick="togglePw('pw-confirm',this)">👁</button>
                </div>
              </div>
            </div>            
          <?php endif; ?>
        </div>

        <div class="apply-section">
          <div class="apply-section-title">마케팅 수신 동의</div>
          <div class="form-field form-chk">
            <input type="checkbox" name="mb_mailling" id="mb_mailling" value="1"
                   <?= (int)($member['mb_mailling'] ?? 0) ? 'checked' : '' ?>>
            <label for="mb_mailling">[선택] 이메일 수신 동의</label>
          </div>
          <div class="form-field form-chk">
            <input type="checkbox" name="mb_sms" id="mb_sms" value="1"
                   <?= (int)($member['mb_sms'] ?? 0) ? 'checked' : '' ?>>
            <label for="mb_sms">[선택] SMS 수신 동의</label>
          </div>
        </div>
        <div class="profile_btn_box">
          <button type="submit" class="btn-next btn-save-profile">저장하기</button>
        </div>
        <div class="profile_btn_box mgt36">
          <button type="button" class="leave_btn">
            <span>회원탈퇴</span>
            <img src="/assets/img/icon_leave_arr.svg" alt="">
          </button>
        </div>
      </div>
    </div>
  </div>
</div>
</form>
<!-- <div style="background:#fff;border-radius:10px;border:1px solid #f5c6c6;padding:20px;margin-bottom:8px">
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
</div> -->

<div class="auth-modal-overlay" id="leaveConfirmModal" style="display:none">
  <div class="auth-modal-card">
    <div class="auth-modal-title">회원 탈퇴</div>
    <ul class="leave_guide">
      <li>탈퇴 시 모든 수강 내역, 찜 목록, 결제 기록이 삭제됩니다.</li>
      <li>삭제된 데이터는 복구되지 않습니다.</li>
      <li>수강 중인 강의가 있으면 탈퇴가 제한됩니다.</li>
      <li>환불 처리 중인 경우 처리 완료 후 탈퇴 가능합니다.</li>
    </ul>
    <div class="auth-input-group">
      <input type="text" id="withdraw-confirm-input" name="confirm_text"
             placeholder='확인을 위해 “탈퇴합니다"를 입력하세요' autocomplete="off">
    </div>
    <div class="auth-modal-btn-flex">
      <button type="button" class="btn-next btn-cancel" onclick="closeModal('leaveConfirmModal')">취소</button>
      <button type="button" class="btn-next btn-error" id="withdrawBtn" style="opacity:.4;cursor:default;pointer-events:none">회원탈퇴</button>
    </div>
  </div>
</div>

<div class="auth-modal-overlay" id="leaveErrorModal" style="display:none">
  <div class="auth-modal-card">
    <div class="auth-modal-title">회원 탈퇴 불가</div>
    <div class="auth-modal-desc" id="leaveErrorMsg"></div>
    <button type="button" class="btn-next mgt24" onclick="closeModal('leaveErrorModal')">확인</button>
  </div>
</div>

<script>
const CSRF = '<?= htmlspecialchars($csrfToken) ?>';

// ── 비밀번호 눈 토글 ──────────────────────────────────
function togglePw(id, btn) {
  const inp = document.getElementById(id);
  if (!inp) return;
  inp.type = inp.type === 'password' ? 'text' : 'password';
  btn.style.opacity = inp.type === 'text' ? '1' : '0.4';
}

// ── 타이머 ────────────────────────────────────────────
let timerInterval = null;

function startTimer(seconds) {
  clearInterval(timerInterval);
  const el = document.getElementById('cert-timer');
  el.textContent = formatTime(seconds);
  timerInterval = setInterval(() => {
    seconds--;
    if (seconds <= 0) {
      clearInterval(timerInterval);
      el.textContent = '만료';
      el.style.color = '#c0392b';
    } else {
      el.textContent = formatTime(seconds);
      el.style.color = '#e67e22';
    }
  }, 1000);
}

function formatTime(s) {
  const m = Math.floor(s / 60);
  const sec = s % 60;
  return m + ':' + String(sec).padStart(2, '0');
}

// ── 연락처 변경 버튼 (변경 → 인증요청) ───────────────
document.getElementById('event_btn1').addEventListener('click', function() {
  const phoneInput = document.getElementById('inp-phone');
  const certBox    = document.getElementById('cert_box');
  const confirmed  = document.getElementById('field-confirmed');

  if (this.textContent === '변경') {
    // 입력 활성화
    phoneInput.disabled = false;
    phoneInput.value    = '';
    phoneInput.focus();
    this.textContent    = '인증요청';
    certBox.style.display    = 'flex';
    confirmed.style.display  = 'none';
    document.getElementById('phone_verified').value = '0';
    document.getElementById('hidden_phone').value   = '';
  } else {
    // 인증요청
    const phone = phoneInput.value.trim();
    if (!phone) { alert('휴대폰 번호를 입력해주세요.'); return; }

    const fd = new FormData();
    fd.append('csrf_token', CSRF);
    fd.append('phone',   phone);
    fd.append('purpose', 'change_phone');

    fetch('/api/member/send-sms', { method: 'POST', body: fd })
      .then(r => r.json())
      .then(data => {
        if (!data.ok) { alert(data.message || '오류가 발생했습니다.'); return; }
        alert('인증번호가 발송되었습니다.' + (data._dev_code ? '\n[개발] 코드: ' + data._dev_code : ''));
        startTimer(180); // 3분
        document.getElementById('inp-cert-code').value = '';
        document.getElementById('inp-cert-code').focus();
      })
      .catch(() => alert('네트워크 오류가 발생했습니다.'));
  }
});

// ── 인증하기 버튼 ─────────────────────────────────────
document.getElementById('event_btn2').addEventListener('click', function() {
  const phone = document.getElementById('inp-phone').value.trim();
  const code  = document.getElementById('inp-cert-code').value.trim();
  if (!code) { alert('인증번호를 입력해주세요.'); return; }

  const fd = new FormData();
  fd.append('csrf_token', CSRF);
  fd.append('phone',   phone);
  fd.append('code',    code);
  fd.append('purpose', 'change_phone');

  fetch('/api/member/verify-sms', { method: 'POST', body: fd })
    .then(r => r.json())
    .then(data => {
      if (!data.ok) { alert(data.message || '인증에 실패했습니다.'); return; }

      // 인증 성공
      clearInterval(timerInterval);
      document.getElementById('cert-timer').textContent = '';
      document.getElementById('cert_box').style.display    = 'none';
      document.getElementById('inp-cert-code').value       = '';
      document.getElementById('field-confirmed').style.display = '';
      document.getElementById('inp-phone').disabled = true;
      document.getElementById('event_btn1').textContent    = '변경';
      document.getElementById('phone_verified').value      = '1';
      document.getElementById('hidden_phone').value        = phone;
    })
    .catch(() => alert('네트워크 오류가 발생했습니다.'));
});

// ── 회원탈퇴 모달 오버레이 클릭 닫기 ─────────────────
['leaveConfirmModal','leaveErrorModal'].forEach(function(id) {
  document.getElementById(id).addEventListener('click', function(e) {
    if (e.target === this) closeModal(id);
  });
});

// ── 회원탈퇴 모달 ─────────────────────────────────────
document.querySelector('.leave_btn').addEventListener('click', function() {
  fetch('/mypage/withdraw/check', {
    headers: { 'X-Requested-With': 'XMLHttpRequest' }
  })
  .then(r => r.json())
  .then(data => {
    if (data.ok) {
      document.getElementById('withdraw-confirm-input').value = '';
      const btn = document.getElementById('withdrawBtn');
      btn.style.opacity = '0.4';
      btn.style.cursor = 'default';
      btn.style.pointerEvents = 'none';
      openModal('leaveConfirmModal');
    } else {
      document.getElementById('leaveErrorMsg').innerHTML = data.message;
      openModal('leaveErrorModal');
    }
  })
  .catch(() => alert('네트워크 오류가 발생했습니다.'));
});

document.getElementById('withdraw-confirm-input').addEventListener('input', function() {
  const btn = document.getElementById('withdrawBtn');
  const ok  = this.value === '탈퇴합니다';
  btn.style.opacity      = ok ? '1'       : '0.4';
  btn.style.cursor       = ok ? 'pointer' : 'default';
  btn.style.pointerEvents = ok ? ''        : 'none';
});

document.getElementById('withdrawBtn').addEventListener('click', function() {
  if (document.getElementById('withdraw-confirm-input').value !== '탈퇴합니다') return;
  if (!confirm('정말로 탈퇴하시겠습니까?\n이 작업은 되돌릴 수 없습니다.')) return;

  const btn = this;
  btn.style.pointerEvents = 'none';
  btn.textContent = '처리 중...';

  const fd = new FormData();
  fd.append('csrf_token', CSRF);
  fd.append('confirm_text', '탈퇴합니다');

  fetch('/mypage/withdraw', {
    method: 'POST',
    body: fd,
    headers: { 'X-Requested-With': 'XMLHttpRequest' }
  })
  .then(r => r.json())
  .then(data => {
    if (data.ok) {
      location.href = data.redirect;
    } else {
      closeModal('leaveConfirmModal');
      document.getElementById('leaveErrorMsg').textContent = data.message || '처리 중 오류가 발생했습니다.';
      openModal('leaveErrorModal');
      btn.style.pointerEvents = '';
      btn.textContent = '회원탈퇴';
    }
  })
  .catch(() => {
    alert('네트워크 오류가 발생했습니다.');
    btn.style.pointerEvents = '';
    btn.textContent = '회원탈퇴';
  });
});

// ── 폼 제출 ───────────────────────────────────────────
document.getElementById('profileForm').addEventListener('submit', function(e) {
  e.preventDefault();
  const form = this;
  const btn  = form.querySelector('[type=submit]');
  btn.disabled = true;
  btn.textContent = '처리 중...';

  fetch('/mypage/profile', {
    method: 'POST',
    body: new FormData(form),
    headers: { 'X-Requested-With': 'XMLHttpRequest' },
  })
    .then(r => r.json())
    .then(data => {
      if (data.ok) {
        location.href = data.redirect;
      } else {
        const msgs = data.errors ? Object.values(data.errors).join('\n') : (data.message || '저장에 실패했습니다.');
        alert(msgs);
        btn.disabled = false;
        btn.textContent = '저장하기';
      }
    })
    .catch(() => {
      alert('네트워크 오류가 발생했습니다.');
      btn.disabled = false;
      btn.textContent = '저장하기';
    });
});
</script>
