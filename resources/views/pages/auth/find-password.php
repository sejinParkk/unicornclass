<?php
$verifiedInfo = $verifiedInfo ?? null;
$pwError      = $_SESSION['reset_pw_error'] ?? null;
unset($_SESSION['reset_pw_error']);
$showStep2    = ($verifiedInfo !== null);
?>
<div class="auth-card">

    <!-- PANEL 1: 아이디 + 휴대폰 인증 -->
    <div class="panel <?= !$showStep2 ? 'active' : '' ?>" id="panelVerify">
        <div class="auth-title">비밀번호 찾기</div>

        <!-- 아이디 -->
        <div class="field-block auth-input-group">
            <div class="field-label">아이디 <span class="required">*</span></div>
            <input type="text" class="single-input" id="mbId" placeholder="아이디를 입력해주세요">
            <div class="error-msg" id="idErr" style="display:none"></div>
        </div>

        <!-- 휴대전화번호 -->
        <div class="field-block auth-input-group">
            <div class="field-label">휴대전화번호 <span class="required">*</span></div>
            <div class="input-row">
                <input type="tel" id="phone" placeholder="010-0000-0000" maxlength="13">
                <button class="btn-request" id="btnSend" disabled onclick="sendSms()">인증요청</button>
            </div>
            <div class="error-msg" id="phoneErr" style="display:none"></div>
            <div class="success-msg" id="phoneSent" style="display:none">인증번호가 생성되었습니다</div>
        </div>

        <!-- 인증번호 -->
        <div class="field-block auth-input-group">
            <div class="field-label">인증 번호</div>
            <div class="input-row">
                <input type="text" id="otp" placeholder="인증요청 후 입력하세요" maxlength="6" disabled style="padding-right:70px">
                <div class="timer-display" id="timerDisplay" style="display:none"></div>
                <button class="btn-request" id="btnVerify" disabled onclick="verifySms()">인증하기</button>
            </div>
            <div class="error-msg" id="otpErr" style="display:none"></div>
            <div class="success-msg" id="otpOk" style="display:none">인증이 완료되었습니다</div>
        </div>

        <button class="btn-next mgt24" id="btnNext" disabled onclick="showPwForm()">비밀번호 찾기</button>
    </div>

    <!-- PANEL 2: 새 비밀번호 설정 -->
    <div class="panel <?= $showStep2 ? 'active' : '' ?>" id="panelReset">
        <div class="auth-title">비밀번호 찾기</div>

        <!-- 완료된 필드 (dimmed) -->
        <?php if ($showStep2): ?>
        <div class="field-block auth-input-group dimmed">
            <div class="field-label">아이디</div>
            <input class="single-input" type="text" value="<?= htmlspecialchars($verifiedInfo['mb_id'] ?? '') ?>" disabled>
        </div>
        <div class="field-block auth-input-group dimmed">
            <div class="field-label">휴대전화번호</div>
            <div class="input-row">
                <input type="tel" value="<?= htmlspecialchars($verifiedInfo['phone'] ?? '') ?>" disabled>
                <button class="btn-request done" disabled>✓ 인증완료</button>
            </div>
        </div>
        <hr class="divider-dashed">
        <?php endif; ?>

        <form method="POST" action="/find-password/reset" id="resetForm" novalidate>
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken ?? '') ?>">

            <div class="field-block auth-input-group">
                <div class="field-label">새 비밀번호 <span class="required">*</span></div>
                <div class="pw-eye-wrap">
                    <input type="password" name="mb_password" id="newPw" placeholder="영문 + 숫자 + 특수문자 8자 이상" autocomplete="new-password">
                    <button type="button" class="eye-btn" onclick="toggleEye('newPw',this)">👁</button>
                </div>
                <div class="field-hint">영문 + 숫자 + 특수문자(!@#$%^&*) 포함 8자 이상</div>
                <div class="error-msg" id="pwErr" data-ajax-err="mb_password" style="display:none"></div>
            </div>

            <div class="field-block auth-input-group">
                <div class="field-label">새 비밀번호 확인 <span class="required">*</span></div>
                <div class="pw-eye-wrap">
                    <input type="password" name="mb_password2" id="newPw2" placeholder="비밀번호를 한번 더 입력해주세요" autocomplete="new-password">
                    <button type="button" class="eye-btn" onclick="toggleEye('newPw2',this)">👁</button>
                </div>
                <div class="error-msg" id="pw2Err" data-ajax-err="mb_password2" style="display:none"></div>
            </div>

            <button type="submit" class="btn-next mgt24" id="btnChange" disabled>비밀번호 변경</button>
        </form>
    </div>

    <!-- PANEL 3: 변경 완료 -->
    <div class="panel" id="panelDone">
        <div class="complete-wrap">
            <div class="id_result_txt1">비밀번호가 변경되었습니다!</div>
            <div class="id_result_txt2">새 비밀번호로 로그인해 주세요.</div>
            <button class="btn-next mgt24" onclick="location.href='/login'">로그인하기</button>
        </div>
    </div>

</div>

<script>
const CSRF = <?= json_encode($csrfToken ?? '') ?>;
let timerInterval = null;
let verified = false;

function checkSendReady() {
    const id = document.getElementById('mbId')?.value.trim() ?? '';
    const phone = document.getElementById('phone')?.value.replace(/[^0-9]/g,'') ?? '';
    const btn = document.getElementById('btnSend');
    if (btn) btn.disabled = !(id.length >= 3 && phone.length >= 10);
}

document.getElementById('mbId')?.addEventListener('input', function() {
    clearErr('idErr'); checkSendReady();
});

document.getElementById('phone')?.addEventListener('input', function() {
    let v = this.value.replace(/[^0-9]/g, '');
    if (v.length <= 3) this.value = v;
    else if (v.length <= 7) this.value = v.slice(0,3)+'-'+v.slice(3);
    else this.value = v.slice(0,3)+'-'+v.slice(3,7)+'-'+v.slice(7,11);
    clearErr('phoneErr'); checkSendReady();
});

document.getElementById('otp')?.addEventListener('input', function() {
    this.value = this.value.replace(/[^0-9]/g,'');
    document.getElementById('btnVerify').disabled = this.value.length !== 6;
    clearErr('otpErr');
});

const PW_REGEX = /^(?=.*[a-zA-Z])(?=.*\d)(?=.*[!@#$%^&*\-_]).{8,}$/;
document.getElementById('newPw')?.addEventListener('blur', validatePw);
document.getElementById('newPw2')?.addEventListener('blur', validatePw2);
document.getElementById('newPw')?.addEventListener('input', () => { clearErr('pwErr'); updateChangeBtn(); });
document.getElementById('newPw2')?.addEventListener('input', () => { clearErr('pw2Err'); updateChangeBtn(); });

function validatePw() {
    const pw = document.getElementById('newPw').value;
    if (pw && !PW_REGEX.test(pw)) {
        showErr('pwErr', '영문 + 숫자 + 특수문자 포함 8자 이상이어야 합니다.');
        return false;
    }
    clearErr('pwErr'); return true;
}
function validatePw2() {
    const pw = document.getElementById('newPw').value;
    const pw2 = document.getElementById('newPw2').value;
    if (pw2 && pw !== pw2) {
        showErr('pw2Err', '비밀번호가 일치하지 않습니다.');
        return false;
    }
    clearErr('pw2Err'); return true;
}
function updateChangeBtn() {
    const pw  = document.getElementById('newPw')?.value ?? '';
    const pw2 = document.getElementById('newPw2')?.value ?? '';
    const btn = document.getElementById('btnChange');
    if (btn) btn.disabled = !(PW_REGEX.test(pw) && pw === pw2);
}

function clearErr(id) { const el = document.getElementById(id); if(el) el.style.display='none'; }
function showErr(id, msg) { const el = document.getElementById(id); if(el){ el.textContent=msg; el.style.display='block'; } }
function toggleEye(id, btn) {
    const el = document.getElementById(id);
    el.type = el.type === 'password' ? 'text' : 'password';
    btn.style.opacity = el.type === 'text' ? '1' : '0.4';
}

async function sendSms() {
    const mbId = document.getElementById('mbId').value.trim();
    const phone = document.getElementById('phone').value.trim();
    clearErr('idErr'); clearErr('phoneErr');
    document.getElementById('phoneSent').style.display = 'none';

    const fd = new FormData();
    fd.append('csrf_token', CSRF);
    fd.append('phone', phone);
    fd.append('purpose', 'find_password');
    fd.append('mb_id', mbId);

    const btn = document.getElementById('btnSend');
    btn.disabled = true; btn.textContent = '발송 중...';

    try {
        const res  = await fetch('/api/member/send-sms', { method: 'POST', body: fd });
        const data = await res.json();
        if (!data.ok) {
            showErr('phoneErr', data.message);
            btn.disabled = false; btn.textContent = '인증요청';
            return;
        }
        document.getElementById('phoneSent').style.display = 'block';
        btn.textContent = '재요청'; btn.disabled = false; btn.style.background = '#aaa';
        document.getElementById('otp').disabled = false;
        document.getElementById('otp').focus();
        startTimer();
        if (data._dev_code) {
            showErr('otpErr', '🔧 개발모드: 인증번호 → ' + data._dev_code);
            document.getElementById('otpErr').style.color = '#2980b9';
        }
    } catch(e) {
        showErr('phoneErr', '네트워크 오류가 발생했습니다.');
        btn.disabled = false; btn.textContent = '인증요청';
    }
}

async function verifySms() {
    const phone = document.getElementById('phone').value.trim();
    const mbId  = document.getElementById('mbId').value.trim();
    const code  = document.getElementById('otp').value.trim();
    clearErr('otpErr');
    document.getElementById('otpOk').style.display = 'none';

    const fd = new FormData();
    fd.append('csrf_token', CSRF);
    fd.append('phone', phone);
    fd.append('code', code);
    fd.append('purpose', 'find_password');
    fd.append('mb_id', mbId);

    const btn = document.getElementById('btnVerify');
    btn.disabled = true; btn.textContent = '확인 중...';

    try {
        const res  = await fetch('/api/member/verify-sms', { method: 'POST', body: fd });
        const data = await res.json();
        if (!data.ok) {
            showErr('otpErr', data.message);
            btn.disabled = false; btn.textContent = '인증하기';
            return;
        }
        clearInterval(timerInterval);
        document.getElementById('timerDisplay').style.display = 'none';
        document.getElementById('otp').disabled = true;
        btn.textContent = '✓ 인증완료'; btn.className = 'btn-request done';
        document.getElementById('otpOk').style.display = 'block';
        document.getElementById('btnNext').disabled = false;
        verified = true;
    } catch(e) {
        showErr('otpErr', '네트워크 오류가 발생했습니다.');
        btn.disabled = false; btn.textContent = '인증하기';
    }
}

function showPwForm() {
    if (!verified) return;
    location.reload();
}

document.getElementById('resetForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    if (!validatePw() || !validatePw2()) return;
    ajaxSubmit(this, {
        onSuccess: function() {
            document.getElementById('panelReset').classList.remove('active');
            document.getElementById('panelDone').classList.add('active');
        }
    });
});

function startTimer(seconds = 180) {
    clearInterval(timerInterval);
    const display = document.getElementById('timerDisplay');
    if (!display) return;
    display.style.display = 'block';
    let remaining = seconds;
    display.textContent = formatTime(remaining);
    timerInterval = setInterval(() => {
        remaining--;
        display.textContent = formatTime(remaining);
        if (remaining <= 0) {
            clearInterval(timerInterval);
            display.textContent = '00:00';
            document.getElementById('otp').disabled = true;
            document.getElementById('btnVerify').disabled = true;
            showErr('otpErr', '인증번호가 만료되었습니다. 재요청해주세요.');
        }
    }, 1000);
}
function formatTime(s) {
    return String(Math.floor(s/60)).padStart(2,'0')+':'+String(s%60).padStart(2,'0');
}
</script>
