<?php
// $csrfToken 가 컨트롤러에서 전달됨
use App\Core\Csrf;
?>
<!-- 서브 배너 -->
<div id="sub-banner">
  <div class="sub-banner-bg"></div>
  <div class="sub-banner-label">유니콘클래스와 함께</div>
  <div class="sub-banner-title">강사 지원하기</div>
  <div class="sub-banner-desc">당신의 노하우를 수강생들과 나눠보세요.<br>지원 후 담당자 검토를 거쳐 영업일 기준 3~5일 내 안내드립니다.</div>
</div>

<div class="apply-wrap">

  <?php if (isset($_GET['success'])): ?>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      document.getElementById('apply-modal').classList.add('active');
    });
  </script>
  <?php endif; ?>

  <form method="POST" action="/instructors/apply" enctype="multipart/form-data" id="applyForm" novalidate>
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">

    <!-- 섹션 1: 기본 정보 -->
    <div class="apply-section">
      <div class="apply-section-title">기본 정보</div>

      <div class="form-row">
        <div class="form-field">
          <label class="form-label">이름 <span class="req">*</span></label>
          <input class="form-input" type="text" name="name" id="inp-name" placeholder="홍길동" autocomplete="name">
          <div class="field-error" id="err-name"></div>
        </div>
        <div class="form-field">
          <label class="form-label">연락처 <span class="req">*</span></label>
          <input class="form-input" type="tel" name="phone" id="inp-phone" placeholder="010-0000-0000" maxlength="13" autocomplete="tel">
          <div class="field-error" id="err-phone"></div>
        </div>
      </div>

      <div class="form-field">
        <label class="form-label">이메일 <span class="req">*</span></label>
        <input class="form-input" type="email" name="email" id="inp-email" placeholder="example@email.com" autocomplete="email">
        <div class="field-error" id="err-email"></div>
      </div>

      <div class="form-row">
        <div class="form-field">
          <label class="form-label">강의 분야 <span class="req">*</span></label>
          <select class="form-select" name="teach_field" id="inp-field">
            <option value="">분야를 선택해주세요</option>
            <option value="커머스/쇼핑몰">커머스 / 쇼핑몰</option>
            <option value="AI/자동화">AI / 자동화</option>
            <option value="SNS/마케팅">SNS / 마케팅</option>
            <option value="유튜브/콘텐츠">유튜브 / 콘텐츠</option>
            <option value="부동산/투자">부동산 / 투자</option>
            <option value="창업/비즈니스">창업 / 비즈니스</option>
            <option value="기타">기타</option>
          </select>
          <div class="field-error" id="err-field"></div>
        </div>
        <div class="form-field">
          <label class="form-label">강의 경력 <span class="req">*</span></label>
          <select class="form-select" name="teach_exp" id="inp-exp">
            <option value="">강의 경력을 선택해주세요</option>
            <option value="없음">없음 (처음 도전)</option>
            <option value="1년미만">1년 미만</option>
            <option value="1~3년">1~3년</option>
            <option value="3~5년">3~5년</option>
            <option value="5년이상">5년 이상</option>
          </select>
          <div class="field-error" id="err-exp"></div>
        </div>
      </div>
    </div>

    <!-- 섹션 2: 강사 소개 -->
    <div class="apply-section">
      <div class="apply-section-title">강사 소개</div>

      <div class="form-field">
        <label class="form-label">주요 경력 / 자기소개 <span class="req">*</span></label>
        <textarea class="form-textarea" name="bio" id="inp-bio" placeholder="주요 경력, 성과, 보유 역량 등을 자유롭게 작성해 주세요." style="min-height:130px;"></textarea>
        <div class="char-count" id="bio-count">0자 / 최소 100자</div>
        <div class="field-error" id="err-bio"></div>
      </div>

      <div class="form-field">
        <label class="form-label">강의 계획 / 커리큘럼 아이디어 <span class="req">*</span></label>
        <textarea class="form-textarea" name="curriculum" id="inp-curriculum" placeholder="어떤 강의를 진행하고 싶으신가요? 대략적인 커리큘럼이나 강의 방향을 작성해 주세요."></textarea>
        <div class="field-error" id="err-curriculum"></div>
      </div>

      <div class="form-field">
        <label class="form-label">희망 강의 형태 <span class="req">*</span></label>
        <select class="form-select" name="teach_format" id="inp-format">
          <option value="">선택해주세요</option>
          <option value="free_webinar">무료 웨비나 (카카오 오픈채팅 기반)</option>
          <option value="paid_vod">유료 VOD (Vimeo 영상 기반)</option>
          <option value="mixed">무료 + 유료 혼합</option>
        </select>
        <div class="field-error" id="err-format"></div>
      </div>
    </div>

    <!-- 섹션 3: SNS / 채널 -->
    <div class="apply-section">
      <div class="apply-section-title">SNS / 채널 <span style="font-size:11px;font-weight:400;color:#aaa;margin-left:2px;">(선택)</span></div>

      <div class="social-row">
        <span class="social-label">📷 인스타</span>
        <input class="form-input" type="url" name="sns_instagram" placeholder="https://instagram.com/...">
      </div>
      <div class="social-row">
        <span class="social-label">▶ 유튜브</span>
        <input class="form-input" type="url" name="sns_youtube" placeholder="https://youtube.com/...">
      </div>
      <div class="social-row">
        <span class="social-label">✦ 블로그</span>
        <input class="form-input" type="url" name="sns_blog" placeholder="https://blog.naver.com/...">
      </div>
      <div class="social-row">
        <span class="social-label">@ 기타</span>
        <input class="form-input" type="url" name="sns_other" placeholder="기타 채널 URL">
      </div>
      <div class="form-hint" style="margin-top:6px;">보유 채널이 없어도 지원 가능합니다.</div>
    </div>

    <!-- 섹션 4: 포트폴리오 -->
    <div class="apply-section">
      <div class="apply-section-title">포트폴리오 / 참고 자료 <span style="font-size:11px;font-weight:400;color:#aaa;margin-left:2px;">(선택)</span></div>

      <div class="form-field">
        <label class="form-label">파일 첨부 <span class="opt">(PDF/PPT/이미지 · 최대 20MB · 최대 3개)</span></label>
        <div class="file-drop" onclick="document.getElementById('portfolioFiles').click()">
          <div class="file-drop-icon">📎</div>
          <div class="file-drop-text">파일을 드래그하거나 클릭하여 업로드</div>
          <div class="file-drop-hint">PDF · PPT · PPTX · JPG · PNG</div>
        </div>
        <input type="file" id="portfolioFiles" name="portfolio_files[]" multiple accept=".pdf,.ppt,.pptx,.jpg,.jpeg,.png" style="display:none" onchange="handleFiles(this)">
        <div class="file-list" id="fileList"></div>
      </div>

      <div class="form-field">
        <label class="form-label">외부 링크 <span class="opt">(선택)</span></label>
        <input class="form-input" type="url" name="portfolio_link" placeholder="Notion, Google Drive, 유튜브 강의 링크 등">
        <div class="form-hint">파일 업로드가 어려운 경우 링크로 대체 가능합니다.</div>
      </div>
    </div>

    <!-- 섹션 5: 동의 + 제출 -->
    <div class="apply-section">
      <div class="apply-section-title">개인정보 수집 및 이용 동의</div>

      <label class="agree-box">
        <input type="checkbox" name="agree" id="inp-agree" value="1">
        <div class="agree-text">
          <strong>[필수]</strong> 개인정보 수집 및 이용에 동의합니다.<br>
          <span style="font-size:11px;color:#aaa;">수집 항목: 이름, 연락처, 이메일, 강의 관련 정보 · 보유 기간: 검토 완료 후 1년</span>
          &nbsp;<a href="#" onclick="openPrivacyModal(event)">개인정보처리방침 보기</a>
        </div>
      </label>
      <div class="field-error" id="err-agree"></div>

      <button type="button" class="btn-submit" id="submitBtn" onclick="validateAndSubmit()">
        🎓 강사 지원서 제출하기
      </button>
      <div class="apply-note">제출 후 영업일 기준 3~5일 내 연락처 또는 이메일로 안내드립니다.</div>
    </div>

  </form>
</div>

<!-- 개인정보처리방침 팝업 -->
<div class="modal-overlay" id="privacyModal" onclick="if(event.target===this)closePrivacyModal()">
  <div class="modal-sheet">
    <button class="modal-close-btn" onclick="closePrivacyModal()">×</button>
    <div class="modal-sheet-title">개인정보 수집 및 이용 동의</div>
    <div class="terms-content" id="privacyModalContent"></div>
    <button class="btn-terms-agree" onclick="agreePrivacyAndClose()">동의하고 닫기</button>
  </div>
</div>

<!-- 완료 모달 -->
<div id="apply-modal" class="modal-overlay" onclick="if(event.target===this)this.classList.remove('active')">
  <div class="modal-card">
    <div class="modal-icon">🎉</div>
    <div class="modal-title">지원서가 접수되었습니다!</div>
    <div class="modal-desc">강사 지원해 주셔서 감사합니다.<br>담당자 검토 후 영업일 기준 3~5일 이내<br>기재하신 연락처로 안내드리겠습니다.</div>
    <button class="modal-btn-primary" onclick="location.href='/instructors'">강사 소개 페이지로</button>
    <button class="modal-btn-secondary" onclick="location.href='/'">메인으로 돌아가기</button>
  </div>
</div>

<script>
/* ── 인라인 에러 표시 ── */
function showErr(id, msg) {
  const el = document.getElementById(id);
  if (!el) return;
  el.textContent = msg;
  el.classList.add('open');
}
function clearErr(id) {
  const el = document.getElementById(id);
  if (!el) return;
  el.textContent = '';
  el.classList.remove('open');
}

/* ── 연락처 하이픈 자동 포맷 ── */
document.getElementById('inp-phone').addEventListener('input', function() {
  let digits = this.value.replace(/\D/g, '');
  if (digits.length > 11) digits = digits.slice(0, 11);
  let fmt = digits;
  if (digits.startsWith('02')) {
    if (digits.length > 6) fmt = digits.slice(0, 2) + '-' + digits.slice(2, digits.length - 4) + '-' + digits.slice(-4);
    else if (digits.length > 2) fmt = digits.slice(0, 2) + '-' + digits.slice(2);
  } else {
    if (digits.length > 7) fmt = digits.slice(0, 3) + '-' + digits.slice(3, 7) + '-' + digits.slice(7);
    else if (digits.length > 3) fmt = digits.slice(0, 3) + '-' + digits.slice(3);
  }
  this.value = fmt;
  if (this.value) clearErr('err-phone');
});

/* ── bio 글자수 카운터 ── */
document.getElementById('inp-bio').addEventListener('input', function() {
  const len = this.value.length;
  const el  = document.getElementById('bio-count');
  if (len >= 100) {
    el.textContent = len + '자 ✓';
    el.className   = 'char-count ok';
    clearErr('err-bio');
  } else {
    el.textContent = len + '자 / 최소 100자';
    el.className   = 'char-count';
  }
});

/* ── 에러 자동 해제 리스너 ── */
document.getElementById('inp-name').addEventListener('input',  function() { if (this.value.trim().length >= 2) clearErr('err-name'); });
document.getElementById('inp-email').addEventListener('input', function() { if (/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(this.value.trim())) clearErr('err-email'); });
document.getElementById('inp-field').addEventListener('change', function() { if (this.value) clearErr('err-field'); });
document.getElementById('inp-exp').addEventListener('change',   function() { if (this.value) clearErr('err-exp'); });
document.getElementById('inp-curriculum').addEventListener('input', function() { if (this.value.trim()) clearErr('err-curriculum'); });
document.getElementById('inp-format').addEventListener('change',    function() { if (this.value) clearErr('err-format'); });
document.getElementById('inp-agree').addEventListener('change',     function() { if (this.checked) clearErr('err-agree'); });

/* ── 폼 검증 + 제출 ── */
function validateAndSubmit() {
  let ok = true;
  let firstErrEl = null;

  function fail(id, msg) {
    showErr(id, msg);
    if (!firstErrEl) firstErrEl = document.getElementById(id);
    ok = false;
  }

  const name = document.getElementById('inp-name').value.trim();
  if (name.length < 2) fail('err-name', '이름을 2자 이상 입력해 주세요.');
  else clearErr('err-name');

  const phone = document.getElementById('inp-phone').value.replace(/\D/g, '');
  if (!/^\d{10,11}$/.test(phone)) fail('err-phone', '연락처를 올바르게 입력해 주세요. (예: 010-1234-5678)');
  else clearErr('err-phone');

  const email = document.getElementById('inp-email').value.trim();
  if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) fail('err-email', '이메일 형식이 올바르지 않습니다.');
  else clearErr('err-email');

  if (!document.getElementById('inp-field').value) fail('err-field', '강의 분야를 선택해 주세요.');
  else clearErr('err-field');

  if (!document.getElementById('inp-exp').value) fail('err-exp', '강의 경력을 선택해 주세요.');
  else clearErr('err-exp');

  const bio = document.getElementById('inp-bio').value.trim();
  if (bio.length < 10) fail('err-bio', '자기소개를 입력해 주세요.');
  else clearErr('err-bio');

  if (!document.getElementById('inp-curriculum').value.trim()) fail('err-curriculum', '강의 계획을 입력해 주세요.');
  else clearErr('err-curriculum');

  if (!document.getElementById('inp-format').value) fail('err-format', '희망 강의 형태를 선택해 주세요.');
  else clearErr('err-format');

  if (!document.getElementById('inp-agree').checked) fail('err-agree', '개인정보 수집 및 이용에 동의해 주세요.');
  else clearErr('err-agree');

  if (!ok) {
    firstErrEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
    return;
  }

  const btn = document.getElementById('submitBtn');
  btn.disabled = true;
  btn.textContent = '제출 중...';
  document.getElementById('applyForm').submit();
}

/* ── 파일 업로드 핸들러 ── */
const dt = new DataTransfer();

function handleFiles(input) {
  const files = Array.from(input.files);
  const current = dt.files.length;
  let added = 0;
  files.forEach(file => {
    if (current + added >= 3) return;
    dt.items.add(file);
    added++;
  });
  input.files = dt.files;
  renderFileList();
}

function renderFileList() {
  const list = document.getElementById('fileList');
  list.innerHTML = '';
  Array.from(dt.files).forEach((file, i) => {
    const size = (file.size / 1024 / 1024).toFixed(1);
    list.innerHTML += `
      <div class="file-item">
        <span>📄 ${file.name}</span>
        <span style="color:#bbb;margin-left:4px;">(${size}MB)</span>
        <span class="file-item-remove" onclick="removeFile(${i})">×</span>
      </div>`;
  });
}

function removeFile(index) {
  const newDt = new DataTransfer();
  Array.from(dt.files).forEach((f, i) => { if (i !== index) newDt.items.add(f); });
  dt.items.clear();
  Array.from(newDt.files).forEach(f => dt.items.add(f));
  document.getElementById('portfolioFiles').files = dt.files;
  renderFileList();
}

/* ── 드래그 앤 드롭 ── */
const dropZone = document.querySelector('.file-drop');
dropZone.addEventListener('dragover', e => { e.preventDefault(); dropZone.style.borderColor = '#c0392b'; });
dropZone.addEventListener('dragleave', () => { dropZone.style.borderColor = ''; });
dropZone.addEventListener('drop', e => {
  e.preventDefault();
  dropZone.style.borderColor = '';
  const input = document.getElementById('portfolioFiles');
  Array.from(e.dataTransfer.files).forEach(f => { if (dt.files.length < 3) dt.items.add(f); });
  input.files = dt.files;
  renderFileList();
});

/* ── 개인정보처리방침 팝업 (AJAX) ── */
function openPrivacyModal(e) {
  e.preventDefault();
  const content = document.getElementById('privacyModalContent');
  content.innerHTML = '<div style="padding:20px;text-align:center;color:#aaa;">불러오는 중...</div>';
  document.getElementById('privacyModal').classList.add('show');
  fetch('/supports/privacy?ajax=1')
    .then(r => r.text())
    .then(html => { content.innerHTML = html; })
    .catch(() => { content.innerHTML = '<div style="padding:20px;color:#c0392b;">내용을 불러오지 못했습니다.</div>'; });
}

function closePrivacyModal() {
  document.getElementById('privacyModal').classList.remove('show');
}

function agreePrivacyAndClose() {
  document.getElementById('inp-agree').checked = true;
  clearErr('err-agree');
  closePrivacyModal();
}
</script>
