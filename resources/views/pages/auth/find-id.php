<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>아이디 찾기 — 유니콘클래스</title>
    <link rel="stylesheet" href="/assets/css/styles.css">
</head>
<body class="auth-page">
<div class="auth-card">

    <!-- PANEL 1: 휴대폰 인증 -->
    <div class="panel active" id="panelVerify">
        <div class="auth-title">아이디 찾기</div>

        <!-- 휴대전화번호 -->
        <div class="field-block">
            <div class="field-label">휴대전화번호 <span class="required">*</span></div>
            <div class="input-row">
                <input type="tel" id="phone" placeholder="010-0000-0000" maxlength="13">
                <button class="btn-request" id="btnSend" disabled onclick="sendSms()">인증요청</button>
            </div>
            <div class="error-msg" id="phoneErr" style="display:none"></div>
            <div class="success-msg" id="phoneSent" style="display:none">✓ 인증번호가 생성되었습니다</div>
        </div>

        <!-- 인증번호 -->
        <div class="field-block">
            <div class="field-label">인증 번호</div>
            <div class="input-row" id="otpRow">
                <input type="text" id="otp" placeholder="인증요청 후 입력하세요" maxlength="6" disabled style="padding-right: 70px">
                <div class="timer-display" id="timerDisplay" style="display:none"></div>
                <button class="btn-request" id="btnVerify" disabled onclick="verifySms()">인증하기</button>
            </div>
            <div class="error-msg" id="otpErr" style="display:none"></div>
            <div class="success-msg" id="otpOk" style="display:none">✓ 인증이 완료되었습니다</div>
        </div>

        <button class="btn-next" id="btnNext" disabled onclick="showResult()">다음</button>

        <div class="bottom-links">
            <a href="/login">로그인</a>
            <a href="/find-password">비밀번호 찾기</a>
            <a href="/register">회원가입</a>
        </div>
    </div>

    <!-- PANEL 2: 결과 (일반 회원) -->
    <div class="panel" id="panelResult">
        <div class="auth-title">아이디 찾기 결과</div>
        <p style="font-size:13px;color:#888;margin-bottom:20px;line-height:1.6">입력하신 정보로 가입된 아이디입니다.</p>
        <div class="result-box">
            <div class="label">아이디</div>
            <div class="found-id" id="resultId"></div>
            <div class="hint">일부 정보는 보안을 위해 마스킹 처리됩니다</div>
        </div>
        <button class="btn-next" onclick="location.href='/login'" style="margin-bottom:10px">로그인하기</button>
        <button class="btn-outline" onclick="location.href='/find-password'">비밀번호 찾기</button>
    </div>

    <!-- PANEL 3: 결과 (소셜 회원) -->
    <div class="panel" id="panelSocial">
        <div class="auth-title">아이디 찾기 결과</div>
        <p style="font-size:13px;color:#888;margin-bottom:20px;line-height:1.6">입력하신 번호로 가입된 정보입니다.</p>
        <div class="social-box">
            <div class="ico">🔗</div>
            <div class="title" id="socialTitle"></div>
            <div class="desc">별도의 아이디/비밀번호가 없습니다.<br>소셜 로그인 버튼을 이용해 주세요.</div>
        </div>
        <button class="btn-outline" onclick="location.href='/login'">돌아가기</button>
    </div>

    <!-- PANEL 4: 가입 정보 없음 -->
    <div class="panel" id="panelNotFound">
        <div class="auth-title">아이디 찾기 결과</div>
        <div style="text-align:center;padding:24px 0">
            <div style="font-size:36px;margin-bottom:16px">🔍</div>
            <div style="font-size:15px;font-weight:700;color:#1a1a1a;margin-bottom:8px">가입된 정보를 찾을 수 없습니다</div>
            <div style="font-size:13px;color:#888;line-height:1.6">입력하신 번호로 가입된 계정이 없습니다.</div>
        </div>
        <button class="btn-next" onclick="resetForm()">다시 시도</button>
        <div class="bottom-links" style="margin-top:16px">
            <a href="/login">로그인</a>
            <a href="/register">회원가입</a>
        </div>
    </div>

</div>

<script>
const CSRF = <?= json_encode($csrfToken ?? '') ?>;
let timerInterval = null;
let verifyResult = null;

// 전화번호 하이픈 자동 삽입
document.getElementById('phone').addEventListener('input', function () {
    let v = this.value.replace(/[^0-9]/g, '');
    if (v.length <= 3) { this.value = v; }
    else if (v.length <= 7) { this.value = v.slice(0,3) + '-' + v.slice(3); }
    else { this.value = v.slice(0,3) + '-' + v.slice(3,7) + '-' + v.slice(7,11); }

    const digits = v.replace(/\D/g,'');
    document.getElementById('btnSend').disabled = digits.length < 10;
    clearErr('phoneErr');
});

document.getElementById('otp').addEventListener('input', function () {
    const v = this.value.replace(/[^0-9]/g, '');
    this.value = v;
    document.getElementById('btnVerify').disabled = v.length !== 6;
    clearErr('otpErr');
});

function clearErr(id) { const el = document.getElementById(id); if(el) el.style.display='none'; }
function showErr(id, msg) { const el = document.getElementById(id); if(el){ el.textContent=msg; el.style.display='block'; } }

async function sendSms() {
    const phone = document.getElementById('phone').value.trim();
    clearErr('phoneErr');
    document.getElementById('phoneSent').style.display = 'none';

    const fd = new FormData();
    fd.append('csrf_token', CSRF);
    fd.append('phone', phone);
    fd.append('purpose', 'find_id');

    const btn = document.getElementById('btnSend');
    btn.disabled = true; btn.textContent = '발송 중...';

    try {
        const res = await fetch('/api/member/send-sms', { method: 'POST', body: fd });
        const data = await res.json();
        if (!data.ok) {
            showErr('phoneErr', data.message);
            btn.disabled = false; btn.textContent = '인증요청';
            return;
        }
        document.getElementById('phoneSent').style.display = 'block';
        btn.textContent = '재요청'; btn.disabled = false; btn.style.background = '#aaa';

        const otpInput = document.getElementById('otp');
        otpInput.disabled = false; otpInput.focus();
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
    const code  = document.getElementById('otp').value.trim();
    clearErr('otpErr');
    document.getElementById('otpOk').style.display = 'none';

    const fd = new FormData();
    fd.append('csrf_token', CSRF);
    fd.append('phone', phone);
    fd.append('code', code);
    fd.append('purpose', 'find_id');

    const btn = document.getElementById('btnVerify');
    btn.disabled = true; btn.textContent = '확인 중...';

    try {
        const res = await fetch('/api/member/verify-sms', { method: 'POST', body: fd });
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
        verifyResult = data;
    } catch(e) {
        showErr('otpErr', '네트워크 오류가 발생했습니다.');
        btn.disabled = false; btn.textContent = '인증하기';
    }
}

function showResult() {
    if (!verifyResult) return;
    document.getElementById('panelVerify').classList.remove('active');

    if (!verifyResult.found) {
        document.getElementById('panelNotFound').classList.add('active');
    } else if (verifyResult.social) {
        document.getElementById('socialTitle').textContent = verifyResult.social_name + ' 계정으로 가입된 회원입니다';
        document.getElementById('panelSocial').classList.add('active');
    } else {
        document.getElementById('resultId').textContent = verifyResult.masked_id;
        document.getElementById('panelResult').classList.add('active');
    }
}

function resetForm() {
    document.querySelectorAll('.panel').forEach(p => p.classList.remove('active'));
    document.getElementById('panelVerify').classList.add('active');
    document.getElementById('phone').value = '';
    document.getElementById('otp').value = '';
    document.getElementById('otp').disabled = true;
    document.getElementById('btnSend').disabled = true;
    document.getElementById('btnVerify').disabled = true;
    document.getElementById('btnNext').disabled = true;
    document.getElementById('phoneSent').style.display = 'none';
    document.getElementById('otpOk').style.display = 'none';
    clearErr('phoneErr'); clearErr('otpErr');
    clearInterval(timerInterval);
    document.getElementById('timerDisplay').style.display = 'none';
    verifyResult = null;
}

function startTimer(seconds = 180) {
    clearInterval(timerInterval);
    const display = document.getElementById('timerDisplay');
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
    const m = Math.floor(s / 60);
    const sec = s % 60;
    return String(m).padStart(2,'0') + ':' + String(sec).padStart(2,'0');
}
</script>
</body>
</html>
