<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>회원가입 — 유니콘클래스</title>
    <link rel="stylesheet" href="/assets/css/styles.css">
</head>
<body class="auth-page">
<?php
$err           = $errors       ?? [];
$o             = $old          ?? [];
$verifiedPhone = $verifiedPhone ?? null;
$registerDone  = $registerDone  ?? false;
$step          = $registerDone ? 3 : ($verifiedPhone ? 2 : 1);
?>
<div class="auth-card">

    <!-- ════ STEP 1: 휴대폰 인증 ════ -->
    <div class="panel <?= $step === 1 ? 'active' : '' ?>" id="panelStep1">
        <div class="auth-title">휴대폰 인증</div>

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
            <div class="input-row">
                <input type="text" id="otp" placeholder="인증요청 후 입력하세요" maxlength="6" disabled style="padding-right:70px">
                <div class="timer-display" id="timerDisplay" style="display:none"></div>
                <button class="btn-request" id="btnVerify" disabled onclick="verifySms()">인증하기</button>
            </div>
            <div class="error-msg" id="otpErr" style="display:none"></div>
            <div class="success-msg" id="otpOk" style="display:none">✓ 인증이 완료되었습니다</div>
        </div>

        <button class="btn-next" id="btnNextStep" disabled onclick="goStep2()">다음</button>
        <div class="bottom-links">
            <a href="/login">로그인</a>
            <a href="/find-id">아이디 찾기</a>
        </div>
    </div>

    <!-- ════ STEP 2: 정보 입력 ════ -->
    <div class="panel <?= $step === 2 ? 'active' : '' ?>" id="panelStep2">
        <div class="auth-title">회원가입</div>

        <?php if (!empty($err['agree_terms'])): ?>
        <div class="error-banner"><?= htmlspecialchars($err['agree_terms']) ?></div>
        <?php endif; ?>

        <form method="POST" action="/register" id="regForm" novalidate>
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken ?? '') ?>">

            <!-- 아이디 -->
            <div class="field-block">
                <div class="field-label">아이디 <span class="required">*</span></div>
                <div class="id-check-row">
                    <input type="text" name="mb_id" id="mb_id"
                           value="<?= htmlspecialchars($o['mb_id'] ?? '') ?>"
                           class="<?= isset($err['mb_id']) ? 'err' : '' ?>"
                           placeholder="소문자/숫자 4~20자" maxlength="20" autocomplete="username">
                    <button type="button" class="btn-dup" id="dupBtn" onclick="checkDuplicate()">중복확인</button>
                </div>
                <div class="error-msg"  id="idErr"  <?= isset($err['mb_id']) ? '' : 'style="display:none"' ?>><?= htmlspecialchars($err['mb_id'] ?? '') ?></div>
                <div class="success-msg" id="idOk" style="display:none">사용 가능한 아이디입니다.</div>
                <div class="field-hint">소문자 영문 + 숫자 조합, 4~20자</div>
            </div>

            <!-- 이메일 -->
            <div class="field-block">
                <div class="field-label">이메일 <span class="required">*</span></div>
                <input type="email" name="mb_email" id="mb_email"
                       class="single-input <?= isset($err['mb_email']) ? 'err' : '' ?>"
                       value="<?= htmlspecialchars($o['mb_email'] ?? '') ?>"
                       placeholder="이메일 주소를 입력해주세요">
                <div class="error-msg" id="emailErr" <?= isset($err['mb_email']) ? '' : 'style="display:none"' ?>><?= htmlspecialchars($err['mb_email'] ?? '') ?></div>
            </div>

            <!-- 비밀번호 -->
            <div class="field-block">
                <div class="field-label">비밀번호 <span class="required">*</span></div>
                <div class="pw-eye-wrap">
                    <input type="password" name="mb_password" id="pw1"
                           class="<?= isset($err['mb_password']) ? 'err' : '' ?>"
                           placeholder="영어, 숫자, 특수문자 8자 이상 조합" autocomplete="new-password">
                    <button type="button" class="eye-btn" onclick="toggleEye('pw1',this)">👁</button>
                </div>
                <div class="error-msg" id="pw1Err" <?= isset($err['mb_password']) ? '' : 'style="display:none"' ?>><?= htmlspecialchars($err['mb_password'] ?? '') ?></div>
                <div class="pw-eye-wrap">
                    <input type="password" name="mb_password2" id="pw2"
                           class="<?= isset($err['mb_password2']) ? 'err' : '' ?>"
                           placeholder="비밀번호를 한번 더 입력해주세요" autocomplete="new-password">
                    <button type="button" class="eye-btn" onclick="toggleEye('pw2',this)">👁</button>
                </div>
                <div class="error-msg" id="pw2Err" <?= isset($err['mb_password2']) ? '' : 'style="display:none"' ?>><?= htmlspecialchars($err['mb_password2'] ?? '') ?></div>
            </div>

            <!-- 이름 -->
            <div class="field-block">
                <div class="field-label">이름 <span class="required">*</span></div>
                <input type="text" name="mb_name" id="mb_name"
                       class="single-input <?= isset($err['mb_name']) ? 'err' : '' ?>"
                       value="<?= htmlspecialchars($o['mb_name'] ?? '') ?>"
                       placeholder="이름을 입력해주세요">
                <div class="error-msg" id="nameErr" <?= isset($err['mb_name']) ? '' : 'style="display:none"' ?>><?= htmlspecialchars($err['mb_name'] ?? '') ?></div>
            </div>

            <!-- 휴대전화번호 (수정 불가) -->
            <div class="field-block">
                <div class="field-label">휴대전화번호</div>
                <input type="tel" class="single-input" value="<?= htmlspecialchars($verifiedPhone ?? '') ?>" disabled>
                <div class="field-hint">✓ 인증 완료된 번호입니다. 수정할 수 없습니다.</div>
            </div>

            <!-- 약관 -->
            <div class="terms-wrap">
                <div class="check-all" onclick="toggleAll()">
                    <div class="cbox" id="cboxAll"></div>
                    <span class="ca-label">모두 동의</span>
                </div>
                <div class="term-row">
                    <div class="cbox" id="cboxPrivacy" onclick="event.stopPropagation();toggleTerm('privacy')"></div>
                    <span class="term-label" onclick="toggleTerm('privacy')">개인정보 처리 약관 동의<span class="term-req"> (필수) *</span></span>
                    <button type="button" class="btn-terms-view" onclick="openTerms('privacy')">보기</button>
                </div>
                <div class="term-row">
                    <div class="cbox" id="cboxMarketing" onclick="event.stopPropagation();toggleTerm('marketing')"></div>
                    <span class="term-label" onclick="toggleTerm('marketing')">광고성 정보 수신 동의<span class="term-opt"> (선택)</span></span>
                    <button type="button" class="btn-terms-view" onclick="openTerms('marketing')">보기</button>
                </div>
            </div>
            <input type="hidden" name="agree_terms" id="agreeTerms" value="0">
            <input type="hidden" name="agree_marketing" id="agreeMarketing" value="0">

            <button type="submit" class="btn-next" id="btnSubmit">회원가입</button>
        </form>
    </div>

    <!-- ════ STEP 3: 가입 완료 ════ -->
    <div class="panel <?= $step === 3 ? 'active' : '' ?>" id="panelDone">
        <div class="complete-wrap">
            <div class="complete-icon">🎉</div>
            <div class="complete-title">가입이 완료되었습니다!</div>
            <div class="complete-desc">유니콘클래스에 오신 것을 환영합니다.<br>지금 바로 강의를 둘러보세요.</div>
            <button class="btn-next" style="margin-top:0;margin-bottom:0" onclick="location.href='/classes'">강의 둘러보기</button>
            <button class="btn-outline" onclick="location.href='/login'">로그인하기</button>
        </div>
    </div>

</div>

<!-- ════ 약관 바텀시트 모달 ════ -->
<div class="modal-overlay" id="termsModal" onclick="closeTermsModal(event)">
    <div class="modal-sheet">
        <button class="modal-close-btn" onclick="closeTerms()">×</button>
        <div class="modal-sheet-title" id="termsModalTitle"></div>
        <div class="terms-content" id="termsModalContent"></div>
        <button class="btn-terms-agree" id="termsAgreeBtn" onclick="agreeAndClose()">동의하고 닫기</button>
    </div>
</div>

<script>
const CSRF = <?= json_encode($csrfToken ?? '') ?>;
const PW_REGEX = /^(?=.*[a-zA-Z])(?=.*\d)(?=.*[!@#$%^&*\-_]).{8,}$/;
let timerInterval = null;
let idChecked = false;
let termState = { privacy: false, marketing: false };
let currentTermsKey = null;

// ── STEP 1 로직 ──
document.getElementById('phone')?.addEventListener('input', function() {
    let v = this.value.replace(/[^0-9]/g,'');
    if (v.length <= 3) this.value = v;
    else if (v.length <= 7) this.value = v.slice(0,3)+'-'+v.slice(3);
    else this.value = v.slice(0,3)+'-'+v.slice(3,7)+'-'+v.slice(7,11);
    const digits = this.value.replace(/\D/g,'');
    document.getElementById('btnSend').disabled = digits.length < 10;
    clearMsg('phoneErr');
});

document.getElementById('otp')?.addEventListener('input', function() {
    this.value = this.value.replace(/[^0-9]/g,'');
    document.getElementById('btnVerify').disabled = this.value.length !== 6;
    clearMsg('otpErr');
});

async function sendSms() {
    const phone = document.getElementById('phone').value.trim();
    clearMsg('phoneErr'); hide('phoneSent');
    const fd = new FormData();
    fd.append('csrf_token', CSRF); fd.append('phone', phone); fd.append('purpose', 'register');
    const btn = document.getElementById('btnSend');
    btn.disabled = true; btn.textContent = '발송 중...';
    try {
        const data = await (await fetch('/api/member/send-sms', {method:'POST',body:fd})).json();
        if (!data.ok) { showErr('phoneErr', data.message); btn.disabled=false; btn.textContent='인증요청'; return; }
        show('phoneSent');
        btn.textContent='재요청'; btn.disabled=false; btn.style.background='#aaa';
        document.getElementById('otp').disabled=false; document.getElementById('otp').focus();
        startTimer();
        if (data._dev_code) { showErr('otpErr', '🔧 개발모드: 인증번호 → '+data._dev_code); document.getElementById('otpErr').style.color='#2980b9'; }
    } catch(e) { showErr('phoneErr','네트워크 오류가 발생했습니다.'); btn.disabled=false; btn.textContent='인증요청'; }
}

async function verifySms() {
    const phone = document.getElementById('phone').value.trim();
    const code  = document.getElementById('otp').value.trim();
    clearMsg('otpErr'); hide('otpOk');
    const fd = new FormData();
    fd.append('csrf_token',CSRF); fd.append('phone',phone); fd.append('code',code); fd.append('purpose','register');
    const btn = document.getElementById('btnVerify');
    btn.disabled=true; btn.textContent='확인 중...';
    try {
        const data = await (await fetch('/api/member/verify-sms',{method:'POST',body:fd})).json();
        if (!data.ok) {
            if (data.blocked) {
                showErr('otpErr', data.message + (data.social_type ? ' (/login에서 소셜 로그인을 이용해주세요)' : ''));
            } else {
                showErr('otpErr', data.message);
            }
            btn.disabled=false; btn.textContent='인증하기'; return;
        }
        clearInterval(timerInterval);
        hide('timerDisplay'); document.getElementById('otp').disabled=true;
        btn.textContent='✓ 인증완료'; btn.className='btn-request done';
        show('otpOk'); document.getElementById('btnNextStep').disabled=false;
    } catch(e) { showErr('otpErr','네트워크 오류가 발생했습니다.'); btn.disabled=false; btn.textContent='인증하기'; }
}

function goStep2() { location.reload(); }

// ── STEP 2 로직 ──
document.getElementById('mb_id')?.addEventListener('input', function() {
    idChecked = false;
    hide('idOk'); this.classList.remove('ok','err');
    clearMsg('idErr');
    if (this.value && !/^[a-z0-9]{0,20}$/.test(this.value)) {
        showErr('idErr', '소문자 영문/숫자만 입력 가능합니다.');
        this.classList.add('err');
    }
});

document.getElementById('mb_email')?.addEventListener('blur', function() {
    if (!this.value) return;
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(this.value)) {
        showErr('emailErr','올바른 이메일 형식이 아닙니다.'); this.classList.add('err');
    } else { clearMsg('emailErr'); this.classList.remove('err'); }
});
document.getElementById('mb_email')?.addEventListener('input', function() { clearMsg('emailErr'); this.classList.remove('err'); });

document.getElementById('pw1')?.addEventListener('blur', function() {
    if (!this.value) return;
    if (!PW_REGEX.test(this.value)) { showErr('pw1Err','영문 + 숫자 + 특수문자 포함 8자 이상이어야 합니다.'); this.classList.add('err'); }
    else { clearMsg('pw1Err'); this.classList.remove('err'); }
});
document.getElementById('pw2')?.addEventListener('blur', function() {
    if (!this.value) return;
    const pw = document.getElementById('pw1').value;
    if (this.value !== pw) { showErr('pw2Err','비밀번호가 일치하지 않습니다.'); this.classList.add('err'); }
    else { clearMsg('pw2Err'); this.classList.remove('err'); }
});
document.getElementById('pw1')?.addEventListener('input', function() { clearMsg('pw1Err'); this.classList.remove('err'); });
document.getElementById('pw2')?.addEventListener('input', function() { clearMsg('pw2Err'); this.classList.remove('err'); });

document.getElementById('mb_name')?.addEventListener('blur', function() {
    if (!this.value) return;
    if (!/^[가-힣a-zA-Z]{2,20}$/.test(this.value)) { showErr('nameErr','한글/영문 2~20자여야 합니다.'); this.classList.add('err'); }
    else { clearMsg('nameErr'); this.classList.remove('err'); }
});
document.getElementById('mb_name')?.addEventListener('input', function() { clearMsg('nameErr'); this.classList.remove('err'); });

async function checkDuplicate() {
    const id = document.getElementById('mb_id').value.trim();
    const input = document.getElementById('mb_id');
    hide('idOk'); clearMsg('idErr'); input.classList.remove('ok','err'); idChecked=false;
    if (!/^[a-z0-9]{4,20}$/.test(id)) { showErr('idErr','소문자 영문/숫자 4~20자여야 합니다.'); input.classList.add('err'); return; }
    const btn = document.getElementById('dupBtn');
    btn.disabled=true; btn.textContent='확인 중...';
    const fd=new FormData(); fd.append('csrf_token',CSRF); fd.append('mb_id',id);
    try {
        const data = await (await fetch('/api/member/check-id',{method:'POST',body:fd})).json();
        if (data.available) { show('idOk'); input.classList.add('ok'); idChecked=true; }
        else { showErr('idErr',data.message); input.classList.add('err'); }
    } catch(e) { showErr('idErr','확인 중 오류가 발생했습니다.'); }
    finally { btn.disabled=false; btn.textContent='중복확인'; }
}

document.getElementById('regForm')?.addEventListener('submit', function(e) {
    let ok = true;
    if (!idChecked) { alert('아이디 중복 확인을 해주세요.'); e.preventDefault(); return; }
    if (!termState.privacy) { alert('개인정보 처리 약관에 동의해주세요.'); e.preventDefault(); return; }
    const pw = document.getElementById('pw1').value;
    const pw2 = document.getElementById('pw2').value;
    if (!PW_REGEX.test(pw)) { showErr('pw1Err','영문 + 숫자 + 특수문자 포함 8자 이상이어야 합니다.'); document.getElementById('pw1').classList.add('err'); ok=false; }
    else if (pw !== pw2) { showErr('pw2Err','비밀번호가 일치하지 않습니다.'); document.getElementById('pw2').classList.add('err'); ok=false; }
    if (!ok) e.preventDefault();
});

// ── 약관 ──
function toggleAll() {
    const allChecked = termState.privacy && termState.marketing;
    const newState = !allChecked;
    termState.privacy = termState.marketing = newState;
    syncAll();
}
function toggleTerm(key) { termState[key] = !termState[key]; syncAll(); }
function syncAll() {
    setCbox('cboxPrivacy', termState.privacy);
    setCbox('cboxMarketing', termState.marketing);
    setCbox('cboxAll', termState.privacy && termState.marketing);
    document.getElementById('agreeTerms').value    = termState.privacy   ? '1' : '0';
    document.getElementById('agreeMarketing').value = termState.marketing ? '1' : '0';
}
function setCbox(id, checked) {
    const el = document.getElementById(id);
    if (!el) return;
    if (checked) el.classList.add('checked'); else el.classList.remove('checked');
}

const TERMS_DATA = {
    privacy: {
        title: '개인정보 처리 약관',
        content: `제1조 (목적)\n본 약관은 유니콘클래스(이하 "회사")가 제공하는 서비스 이용에 있어 회원의 개인정보 수집, 이용, 보관, 파기에 관한 사항을 규정합니다.\n\n제2조 (수집하는 개인정보)\n회사는 서비스 제공을 위해 아래 정보를 수집합니다.\n· 필수: 아이디, 이메일, 비밀번호, 이름, 휴대전화번호\n· 선택: 광고성 정보 수신 동의\n\n제3조 (개인정보의 이용 목적)\n수집한 개인정보는 회원 관리, 서비스 제공, 공지사항 전달 목적으로만 활용됩니다.\n\n제4조 (보유 및 이용 기간)\n회원 탈퇴 시 즉시 파기하며, 법령에 의해 보존이 필요한 경우 해당 기간 동안 보관합니다.\n\n[ 이하 생략 ]`
    },
    marketing: {
        title: '광고성 정보 수신 동의',
        content: `광고성 정보 수신 동의 (선택)\n\n유니콘클래스에서 발송하는 이벤트, 할인 혜택, 신규 강의 소식 등 광고성 정보 수신에 동의합니다.\n\n· 수신 채널: SMS, 이메일\n· 거부 권리: 동의하지 않아도 서비스 이용에 불이익이 없습니다.\n· 철회: 마이페이지 > 설정에서 언제든지 수신 거부 가능합니다.`
    }
};

function openTerms(key) {
    currentTermsKey = key;
    const data = TERMS_DATA[key];
    document.getElementById('termsModalTitle').textContent = data.title;
    document.getElementById('termsModalContent').textContent = data.content;
    document.getElementById('termsModal').classList.add('show');
}
function closeTerms() {
    document.getElementById('termsModal').classList.remove('show');
    currentTermsKey = null;
}
function closeTermsModal(e) { if(e.target === document.getElementById('termsModal')) closeTerms(); }
function agreeAndClose() {
    if (currentTermsKey) { termState[currentTermsKey] = true; syncAll(); }
    closeTerms();
}

// ── 공통 유틸 ──
function clearMsg(id) { const el=document.getElementById(id); if(el){ el.textContent=''; el.style.display='none'; } }
function showErr(id,msg) { const el=document.getElementById(id); if(el){ el.textContent=msg; el.style.display='block'; } }
function show(id) { const el=document.getElementById(id); if(el) el.style.display='block'; }
function hide(id) { const el=document.getElementById(id); if(el) el.style.display='none'; }
function toggleEye(id,btn) { const el=document.getElementById(id); el.type=el.type==='password'?'text':'password'; btn.style.opacity=el.type==='text'?'1':'0.4'; }

function startTimer(seconds=180) {
    clearInterval(timerInterval);
    const display=document.getElementById('timerDisplay');
    if(!display) return;
    display.style.display='block';
    let remaining=seconds;
    display.textContent=fmt(remaining);
    timerInterval=setInterval(()=>{
        remaining--;
        display.textContent=fmt(remaining);
        if(remaining<=0){
            clearInterval(timerInterval); display.textContent='00:00';
            document.getElementById('otp').disabled=true;
            document.getElementById('btnVerify').disabled=true;
            showErr('otpErr','인증번호가 만료되었습니다. 재요청해주세요.');
        }
    },1000);
}
function fmt(s){return String(Math.floor(s/60)).padStart(2,'0')+':'+String(s%60).padStart(2,'0');}
</script>
</body>
</html>
