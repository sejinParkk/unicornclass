<?php
// $csrfToken, $error 가 컨트롤러에서 전달됨
use App\Core\Csrf;
?>
<style>
#sub-banner{position:relative;background:linear-gradient(105deg,#0d0d0d 0%,#1a1a2e 60%,#0f2027 100%);padding:36px 32px 32px;overflow:hidden}
.sub-banner-bg{position:absolute;inset:0;background:linear-gradient(105deg,rgba(192,57,43,.15) 0%,transparent 60%);pointer-events:none}
.sub-banner-label{font-size:11px;color:rgba(255,255,255,.5);letter-spacing:2px;text-transform:uppercase;margin-bottom:8px}
.sub-banner-title{font-size:32px;font-weight:900;color:#fff;letter-spacing:-1px;line-height:1.2}
.sub-banner-desc{font-size:14px;color:rgba(255,255,255,.6);margin-top:8px;line-height:1.6}
.apply-wrap{max-width:700px;margin:0 auto;padding:40px 32px 64px}
.apply-section{background:#fff;border:1px solid #eee;border-radius:12px;padding:24px 28px;margin-bottom:16px}
.apply-section-title{font-size:14px;font-weight:800;color:#1a1a1a;margin-bottom:20px;padding-bottom:12px;border-bottom:2px solid #f0f0f0;display:flex;align-items:center;gap:8px}
.apply-section-title::before{content:'';width:4px;height:15px;background:#c0392b;border-radius:2px;flex-shrink:0}
.form-field{margin-bottom:16px}
.form-label{font-size:12px;font-weight:600;color:#444;margin-bottom:6px;display:block}
.form-label .req{color:#c0392b;margin-left:2px}
.form-label .opt{font-size:11px;color:#aaa;font-weight:400;margin-left:4px}
.form-input{width:100%;height:44px;border:1px solid #e0e0e0;border-radius:8px;padding:0 14px;font-size:13px;color:#333;font-family:inherit;outline:none;box-sizing:border-box;transition:border-color .15s}
.form-input:focus{border-color:#c0392b}
.form-input::placeholder{color:#bbb}
.form-textarea{width:100%;min-height:110px;border:1px solid #e0e0e0;border-radius:8px;padding:11px 14px;font-size:13px;color:#333;font-family:inherit;outline:none;resize:vertical;box-sizing:border-box;transition:border-color .15s;line-height:1.7}
.form-textarea:focus{border-color:#c0392b}
.form-textarea::placeholder{color:#bbb}
.form-select{width:100%;height:44px;border:1px solid #e0e0e0;border-radius:8px;padding:0 14px;font-size:13px;color:#333;font-family:inherit;outline:none;box-sizing:border-box;background:#fff;transition:border-color .15s}
.form-select:focus{border-color:#c0392b}
.form-hint{font-size:11px;color:#aaa;margin-top:5px;padding-left:2px;line-height:1.6}
.form-row{display:grid;grid-template-columns:1fr 1fr;gap:12px}
.social-row{display:flex;align-items:center;gap:10px;margin-bottom:8px}
.social-label{font-size:12px;font-weight:600;color:#888;width:60px;flex-shrink:0}
.file-drop{border:2px dashed #ddd;border-radius:8px;padding:22px;text-align:center;background:#fafafa;cursor:pointer;transition:border-color .15s}
.file-drop:hover{border-color:#c0392b}
.file-drop-icon{font-size:26px;margin-bottom:6px}
.file-drop-text{font-size:13px;color:#888}
.file-drop-hint{font-size:11px;color:#bbb;margin-top:4px}
.file-list{margin-top:10px}
.file-item{display:flex;align-items:center;gap:8px;padding:6px 10px;background:#f8f9fa;border-radius:6px;margin-bottom:4px;font-size:12px;color:#555}
.file-item-remove{margin-left:auto;cursor:pointer;color:#aaa;font-size:14px;line-height:1}
.file-item-remove:hover{color:#c0392b}
.agree-box{display:flex;align-items:flex-start;gap:10px;padding:14px 16px;background:#f8f9fa;border-radius:8px;border:1px solid #e8e8e8;cursor:pointer;margin-bottom:18px;transition:border-color .15s}
.agree-box:hover{border-color:#c0392b}
.agree-box input[type="checkbox"]{width:16px;height:16px;margin-top:3px;accent-color:#c0392b;flex-shrink:0}
.agree-text{font-size:12.5px;color:#444;line-height:1.7}
.agree-text a{color:#c0392b;text-decoration:underline}
.btn-submit{width:100%;height:54px;background:#c0392b;color:#fff;border-radius:10px;font-size:15px;font-weight:800;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:8px;transition:background .15s;border:none;font-family:inherit}
.btn-submit:hover{background:#a93226}
.btn-submit:disabled{background:#e0a0a0;cursor:not-allowed}
.apply-note{font-size:11.5px;color:#aaa;text-align:center;margin-top:10px;line-height:1.7}
.error-banner{background:#fdecea;border:1px solid #f5c6c6;border-radius:8px;padding:12px 16px;margin-bottom:16px;font-size:13px;color:#c0392b}
/* 개인정보 팝업 (회원가입 동일 구조) */
.modal-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.6);z-index:999;align-items:center;justify-content:center}
.modal-overlay.active,.modal-overlay.show{display:flex}
.modal-sheet{background:#fff;border-radius:16px 16px 0 0;width:100%;max-width:480px;max-height:80vh;display:flex;flex-direction:column;position:fixed;bottom:0;left:50%;transform:translateX(-50%);box-shadow:0 -4px 40px rgba(0,0,0,.18)}
.modal-close-btn{position:absolute;top:14px;right:16px;background:none;border:none;font-size:22px;color:#888;cursor:pointer;line-height:1;padding:4px}
.modal-close-btn:hover{color:#333}
.modal-sheet-title{font-size:15px;font-weight:800;color:#1a1a1a;padding:20px 24px 14px;border-bottom:1px solid #f0f0f0;flex-shrink:0}
.terms-content{flex:1;overflow-y:auto;padding:16px 24px;font-size:12.5px;color:#555;line-height:1.9;white-space:pre-wrap;word-break:break-word}
.btn-terms-agree{width:calc(100% - 32px);margin:12px 16px 16px;height:46px;background:#c0392b;color:#fff;border:none;border-radius:10px;font-size:14px;font-weight:700;cursor:pointer;font-family:inherit;flex-shrink:0}
.btn-terms-agree:hover{background:#a93226}
/* 완료 모달 */
.modal-card{background:#fff;border-radius:20px;width:360px;padding:40px 32px 32px;text-align:center;box-shadow:0 24px 80px rgba(0,0,0,.3)}
.modal-icon{font-size:52px;margin-bottom:14px}
.modal-title{font-size:20px;font-weight:900;color:#1a1a1a;margin-bottom:10px}
.modal-desc{font-size:13px;color:#666;line-height:1.8;margin-bottom:28px}
.modal-btn-primary{width:100%;height:48px;background:#c0392b;color:#fff;border-radius:10px;font-size:14px;font-weight:700;cursor:pointer;margin-bottom:10px;border:none;font-family:inherit}
.modal-btn-secondary{width:100%;height:42px;background:#f5f5f5;color:#555;border-radius:10px;font-size:13px;font-weight:600;cursor:pointer;border:none;font-family:inherit}
</style>

<!-- 서브 배너 -->
<div id="sub-banner">
  <div class="sub-banner-bg"></div>
  <div class="sub-banner-label">유니콘클래스와 함께</div>
  <div class="sub-banner-title">강사 지원하기</div>
  <div class="sub-banner-desc">당신의 노하우를 수강생들과 나눠보세요.<br>지원 후 담당자 검토를 거쳐 영업일 기준 3~5일 내 안내드립니다.</div>
</div>

<div class="apply-wrap">

  <?php if ($error): ?>
  <div class="error-banner"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <?php if (isset($_GET['success'])): ?>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      document.getElementById('apply-modal').classList.add('active');
    });
  </script>
  <?php endif; ?>

  <form method="POST" action="/instructors/apply" enctype="multipart/form-data" id="applyForm">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">

    <!-- 섹션 1: 기본 정보 -->
    <div class="apply-section">
      <div class="apply-section-title">기본 정보</div>

      <div class="form-row">
        <div class="form-field">
          <label class="form-label">이름 <span class="req">*</span></label>
          <input class="form-input" type="text" name="name" placeholder="홍길동" required>
        </div>
        <div class="form-field">
          <label class="form-label">연락처 <span class="req">*</span></label>
          <input class="form-input" type="tel" name="phone" placeholder="01000000000" required>
        </div>
      </div>

      <div class="form-field">
        <label class="form-label">이메일 <span class="req">*</span></label>
        <input class="form-input" type="email" name="email" placeholder="example@email.com" required>
      </div>

      <div class="form-row">
        <div class="form-field">
          <label class="form-label">강의 분야 <span class="req">*</span></label>
          <select class="form-select" name="teach_field" required>
            <option value="">분야를 선택해주세요</option>
            <option value="커머스/쇼핑몰">커머스 / 쇼핑몰</option>
            <option value="AI/자동화">AI / 자동화</option>
            <option value="SNS/마케팅">SNS / 마케팅</option>
            <option value="유튜브/콘텐츠">유튜브 / 콘텐츠</option>
            <option value="부동산/투자">부동산 / 투자</option>
            <option value="창업/비즈니스">창업 / 비즈니스</option>
            <option value="기타">기타</option>
          </select>
        </div>
        <div class="form-field">
          <label class="form-label">강의 경력 <span class="req">*</span></label>
          <select class="form-select" name="teach_exp" required>
            <option value="">강의 경력을 선택해주세요</option>
            <option value="없음">없음 (처음 도전)</option>
            <option value="1년미만">1년 미만</option>
            <option value="1~3년">1~3년</option>
            <option value="3~5년">3~5년</option>
            <option value="5년이상">5년 이상</option>
          </select>
        </div>
      </div>
    </div>

    <!-- 섹션 2: 강사 소개 -->
    <div class="apply-section">
      <div class="apply-section-title">강사 소개</div>

      <div class="form-field">
        <label class="form-label">주요 경력 / 자기소개 <span class="req">*</span></label>
        <textarea class="form-textarea" name="bio" placeholder="주요 경력, 성과, 보유 역량 등을 자유롭게 작성해 주세요." style="min-height:130px;" required></textarea>
        <div class="form-hint">최소 100자 이상 작성을 권장합니다.</div>
      </div>

      <div class="form-field">
        <label class="form-label">강의 계획 / 커리큘럼 아이디어 <span class="req">*</span></label>
        <textarea class="form-textarea" name="curriculum" placeholder="어떤 강의를 진행하고 싶으신가요? 대략적인 커리큘럼이나 강의 방향을 작성해 주세요." required></textarea>
      </div>

      <div class="form-field">
        <label class="form-label">희망 강의 형태 <span class="req">*</span></label>
        <select class="form-select" name="teach_format" required>
          <option value="">선택해주세요</option>
          <option value="free_webinar">무료 웨비나 (카카오 오픈채팅 기반)</option>
          <option value="paid_vod">유료 VOD (Vimeo 영상 기반)</option>
          <option value="mixed">무료 + 유료 혼합</option>
        </select>
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
        <input type="checkbox" name="agree" value="1" required>
        <div class="agree-text">
          <strong>[필수]</strong> 개인정보 수집 및 이용에 동의합니다.<br>
          <span style="font-size:11px;color:#aaa;">수집 항목: 이름, 연락처, 이메일, 강의 관련 정보 · 보유 기간: 검토 완료 후 1년</span>
          &nbsp;<a href="#" onclick="openPrivacyModal(event)">개인정보처리방침 보기</a>
        </div>
      </label>

      <button type="submit" class="btn-submit" id="submitBtn">
        🎓 강사 지원서 제출하기
      </button>
      <div class="apply-note">제출 후 영업일 기준 3~5일 내 연락처 또는 이메일로 안내드립니다.</div>
    </div>

  </form>
</div>

<!-- 개인정보처리방침 팝업 (회원가입과 동일 구조) -->
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
// 파일 업로드 핸들러
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

// 드래그 앤 드롭
const dropZone = document.querySelector('.file-drop');
dropZone.addEventListener('dragover', e => { e.preventDefault(); dropZone.style.borderColor = '#c0392b'; });
dropZone.addEventListener('dragleave', () => { dropZone.style.borderColor = ''; });
dropZone.addEventListener('drop', e => {
  e.preventDefault();
  dropZone.style.borderColor = '';
  const input = document.getElementById('portfolioFiles');
  const files = Array.from(e.dataTransfer.files);
  files.forEach(f => { if (dt.files.length < 3) dt.items.add(f); });
  input.files = dt.files;
  renderFileList();
});

// 개인정보처리방침 팝업
const PRIVACY_CONTENT = `제1조 (목적)\n본 방침은 유니콘클래스(이하 "회사")가 강사 지원 검토를 위해 수집하는 개인정보의 처리 방법과 보호 조치를 규정합니다.\n\n제2조 (수집하는 개인정보 항목)\n회사는 강사 지원 검토를 위해 아래 정보를 수집합니다.\n· 필수 항목: 이름, 연락처(전화번호), 이메일 주소, 강의 분야, 강의 경력, 자기소개, 강의 계획\n· 선택 항목: SNS 채널 URL, 포트폴리오 파일, 외부 링크\n\n제3조 (개인정보의 수집 및 이용 목적)\n· 강사 지원서 검토 및 적합성 평가\n· 지원 결과 안내 (전화 또는 이메일)\n\n제4조 (개인정보의 보유 및 이용 기간)\n검토 완료 후 1년간 보유하며, 이후 즉시 파기합니다.\n단, 관련 법령에 의해 보존이 필요한 경우 해당 기간 동안 보유합니다.\n\n제5조 (개인정보의 파기)\n보유 기간이 경과하거나 목적이 달성된 경우 지체 없이 파기합니다.\n전자 파일은 복구 불가능한 방법으로 영구 삭제하며, 출력물은 분쇄 또는 소각합니다.\n\n제6조 (동의 거부 권리)\n개인정보 수집·이용에 동의를 거부할 권리가 있습니다.\n단, 동의 거부 시 강사 지원이 제한될 수 있습니다.`;

function openPrivacyModal(e) {
  e.preventDefault();
  document.getElementById('privacyModalContent').textContent = PRIVACY_CONTENT;
  document.getElementById('privacyModal').classList.add('show');
}

function closePrivacyModal() {
  document.getElementById('privacyModal').classList.remove('show');
}

function agreePrivacyAndClose() {
  document.querySelector('input[name="agree"]').checked = true;
  closePrivacyModal();
}

// 중복 제출 방지
document.getElementById('applyForm').addEventListener('submit', function() {
  const btn = document.getElementById('submitBtn');
  btn.disabled = true;
  btn.textContent = '제출 중...';
});
</script>
