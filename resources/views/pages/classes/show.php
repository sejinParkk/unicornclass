<?php
/**
 * 클래스 상세 페이지
 *
 * 컨트롤러에서 전달되는 변수:
 *   $class         — 강의 데이터 (files[], chapters[], instructor_intros[], instructor_careers[] 포함)
 *   $member        — 로그인 회원 정보 (비로그인 시 null)
 *   $enroll        — lc_enroll 행 (미수강 시 null)
 *   $isEnrolled    — bool
 *   $isWished      — bool
 *   $btnStatus     — 'apply'|'enrolled'|'closed'|'login_required'
 *   $discountRate  — 할인율 (int, 0이면 할인 없음)
 *   $csrfToken     — CSRF 토큰
 */

$isFree    = $class['type'] === 'free';
$isPremium = $class['type'] === 'premium';

// 강의일 / 판매 마감일 (sale_end_at)
$saleEndAt     = $class['sale_end_at'] ?? null;
$saleEndTs     = $saleEndAt ? strtotime($saleEndAt) : null;
$saleEndFmt    = $saleEndTs ? date('Y.m.d (D) H:i', $saleEndTs) : null;

// 회원 기본정보 (정보 입력 필드 자동완성용)
$ordererName  = htmlspecialchars($member['mb_name']  ?? '');
$ordererEmail = htmlspecialchars($member['mb_email'] ?? '');
$ordererPhone = htmlspecialchars($member['mb_phone'] ?? '');

// 유효 파일 목록
$files = $class['files'] ?? [];
?>
<style>
/* ── 브레드크럼 ── */
.cd-breadcrumb{padding:10px 32px;font-size:12px;color:#aaa;border-bottom:1px solid #f5f5f5}
.cd-breadcrumb a{color:#aaa;text-decoration:none}.cd-breadcrumb a:hover{color:#c0392b}
.cd-breadcrumb span{margin:0 6px;color:#ccc}
.cd-breadcrumb .cur{color:#555;font-weight:600}

/* ── 2단 레이아웃 ── */
.cd-wrap{display:flex;gap:28px;padding:28px 32px 0;align-items:flex-start}
.cd-left{flex:1;min-width:0}
.cd-right{width:268px;flex-shrink:0;position:sticky;top:80px;align-self:flex-start}

/* ── 썸네일 ── */
.cd-thumb{width:100%;aspect-ratio:16/9;border-radius:6px;overflow:hidden;background:#1a1a2e;margin-bottom:0}
.cd-thumb img{width:100%;height:100%;object-fit:cover;display:block}

/* ── 내용 탭 ── */
.cd-tabs{display:flex;border-bottom:2px solid #eee;position:sticky;top:60px;z-index:90;background:#fff}
.cd-tab{flex:1;text-align:center;height:44px;display:flex;align-items:center;justify-content:center;font-size:13.5px;font-weight:600;color:#999;cursor:pointer;border-bottom:3px solid transparent;margin-bottom:-2px;transition:all .15s}
.cd-tab.active{color:#c0392b;border-bottom-color:#c0392b}

/* ── 클래스 소개 본문 ── */
.cd-desc-body{padding:20px 0;min-height:200px;line-height:1.8;font-size:14px;color:#333}
.cd-desc-body img{max-width:100%;height:auto}
.cd-desc-empty{padding:40px 20px;text-align:center;color:#aaa;font-size:13px;background:#f9f9f9;border-radius:6px}

/* ── 강사 소개 섹션 ── */
.cd-inst-box{border:1px solid #eee;border-radius:8px;padding:20px;margin-top:4px}
.cd-inst-head{font-size:14px;font-weight:800;color:#1a1a1a;margin-bottom:16px;padding-bottom:10px;border-bottom:1px solid #f0f0f0}
.cd-inst-row{display:flex;align-items:center;justify-content:space-between}
.cd-inst-profile{display:flex;align-items:center;gap:12px}
.cd-inst-avatar{width:40px;height:40px;border-radius:50%;background:linear-gradient(135deg,#c0392b,#e74c3c);display:flex;align-items:center;justify-content:center;color:#fff;font-size:14px;font-weight:700;flex-shrink:0;overflow:hidden}
.cd-inst-avatar img{width:100%;height:100%;object-fit:cover}
.cd-inst-name{font-size:14px;font-weight:800}
.cd-inst-field{font-size:11.5px;color:#888;margin-top:2px}
.cd-inst-toggle{width:28px;height:28px;border-radius:50%;border:1px solid #ddd;display:flex;align-items:center;justify-content:center;font-size:16px;color:#888;cursor:pointer;transition:all .15s;flex-shrink:0}
.cd-inst-toggle:hover{border-color:#c0392b;color:#c0392b}
.cd-inst-detail{display:none;margin-top:16px;padding-top:16px;border-top:1px solid #f0f0f0}
.cd-inst-detail.open{display:block}
.bullet-list{list-style:none}
.bullet-list li{font-size:13px;color:#444;line-height:1.8;padding-left:14px;position:relative}
.bullet-list li::before{content:'•';position:absolute;left:0;color:#c0392b;font-weight:700}
.cd-inst-section-title{font-size:12px;font-weight:700;color:#555;margin:14px 0 6px}
.cd-inst-link{display:inline-flex;align-items:center;gap:6px;margin-top:10px;text-decoration:none;color:#c0392b;font-size:12.5px;font-weight:600;border:1px solid #c0392b;border-radius:20px;padding:4px 12px;transition:all .15s}
.cd-inst-link:hover{background:#c0392b;color:#fff}

/* ── 마케팅/면책 ── */
.cd-terms{padding:32px 32px 0}
.cd-terms-block{margin-bottom:16px}
.cd-terms-title{font-size:13px;font-weight:800;color:#1a1a1a;margin-bottom:6px}
.cd-terms-body{font-size:12px;color:#666;line-height:1.8;background:#f9f9f9;padding:12px 14px;border-radius:6px;border:1px solid #eee}

/* ─────────────────────────────────────────────
   우측 신청/결제 박스
───────────────────────────────────────────── */
.cd-box{border:1px solid #e0e0e0;border-radius:8px;overflow:hidden;background:#fff;box-shadow:0 2px 12px rgba(0,0,0,.08)}
.cd-box-badges{padding:14px 16px 0;display:flex;gap:4px}
.dbadge{font-size:10px;font-weight:700;padding:2px 7px;border-radius:2px}
.db-hot{background:#fdecea;color:#c0392b}
.db-new{background:#e8f5e9;color:#27ae60}
.db-free{background:#e8f0ff;color:#3b5bdb}
.db-premium{background:#f3e8ff;color:#8e44ad}
.cd-box-title{padding:10px 16px 14px;font-size:14px;font-weight:800;color:#1a1a1a;line-height:1.45;border-bottom:1px solid #f0f0f0}
.cd-box-actions{display:flex;gap:6px;padding:10px 16px;border-bottom:1px solid #f0f0f0}
.action-btn{display:flex;align-items:center;gap:4px;height:30px;padding:0 10px;border-radius:15px;border:1px solid #e0e0e0;background:#fff;font-size:12px;font-weight:600;color:#555;cursor:pointer;transition:all .15s}
.action-btn:hover{border-color:#c0392b;color:#c0392b}
.action-btn.wished{border-color:#c0392b;color:#c0392b;background:#fff5f5}
.cd-box-row{padding:10px 16px;display:flex;justify-content:space-between;font-size:12.5px;color:#888;border-bottom:1px solid #f0f0f0}
.cd-box-row span{color:#333;font-weight:600}

/* 카운트다운 타이머 */
.cd-timer-wrap{padding:10px 16px;background:#fff9f9;border-bottom:1px solid #f0f0f0;text-align:center;font-size:11.5px;color:#888}
.cd-timer-val{font-size:17px;font-weight:900;color:#c0392b;letter-spacing:1px;margin-top:2px}

/* 가격 */
.cd-price-wrap{padding:12px 16px;border-bottom:1px solid #f0f0f0}
.cd-price-origin{font-size:12px;color:#bbb;text-decoration:line-through;margin-bottom:2px}
.cd-price-row{display:flex;align-items:baseline;gap:6px}
.cd-price-rate{font-size:14px;font-weight:900;color:#c0392b}
.cd-price-val{font-size:20px;font-weight:900;color:#1a1a1a}
.cd-price-free{font-size:20px;font-weight:900;color:#3b5bdb;padding:12px 16px;border-bottom:1px solid #f0f0f0}

/* 할부 안내 */
.cd-install-wrap{padding:10px 16px;border-top:1px solid #f5f5f5;background:#fafafa;border-bottom:1px solid #f0f0f0}
.cd-install-label{font-size:11px;color:#888;margin-bottom:6px}
.cd-install-chips{display:flex;gap:5px;flex-wrap:wrap}
.cd-install-chip{font-size:11px;background:#f0f0f0;border-radius:4px;padding:3px 8px;color:#555}
.cd-install-note{font-size:10.5px;color:#bbb;margin-top:5px}

/* 강의 자료 */
.cd-files-wrap{padding:10px 16px;border-bottom:1px solid #f0f0f0}
.cd-files-label{font-size:11px;color:#888;margin-bottom:8px}
.cd-file-row{display:flex;align-items:center;justify-content:space-between;background:#f8f9fa;border:1px solid #e0e0e0;border-radius:6px;padding:8px 12px;margin-bottom:6px}
.cd-file-row:last-child{margin-bottom:0}
.cd-file-name{font-size:12px;font-weight:600;color:#333;margin-bottom:2px}
.cd-file-meta{font-size:10.5px;color:#aaa}
.cd-file-row.link{background:#f0f4ff;border-color:#c5d5f5}
.cd-file-row.link .cd-file-name{color:#1a3a8a}
.cd-file-row.link .cd-file-meta{color:#7a8ab8}
.btn-file-dl{height:30px;padding:0 12px;background:#1a3a5c;color:#fff;border-radius:5px;font-size:11px;font-weight:700;border:none;cursor:pointer;white-space:nowrap}
.btn-file-link{height:30px;padding:0 12px;background:#3b5bdb;color:#fff;border-radius:5px;font-size:11px;font-weight:700;text-decoration:none;display:flex;align-items:center}

/* 신청 버튼 영역 */
.cd-box-btn{padding:14px 16px 16px}
.btn-apply{width:100%;height:46px;border-radius:6px;background:#c0392b;color:#fff;font-size:15px;font-weight:800;cursor:pointer;transition:background .15s;border:none;margin-bottom:8px}
.btn-apply:hover:not(:disabled){background:#a93226}
.btn-apply.free-color{background:#3b5bdb}
.btn-apply.free-color:hover:not(:disabled){background:#2f4abf}
.btn-apply:disabled{background:#ccc;cursor:not-allowed}
.btn-go-learn{width:100%;height:46px;border-radius:6px;background:#27ae60;color:#fff;font-size:15px;font-weight:800;cursor:pointer;border:none;margin-bottom:8px;text-decoration:none;display:flex;align-items:center;justify-content:center}
.btn-go-learn:hover{background:#219a52}

/* 상태 메시지 박스 */
.state-box{margin:0 16px 14px;padding:10px 12px;border-radius:6px;font-size:12px;line-height:1.6;display:flex;gap:8px;align-items:flex-start}
.state-box a{font-weight:700;text-decoration:underline}
.state-box.info{background:#e8f0ff;color:#1a3a8a;border:1px solid #c5d5f5}
.state-box.info a{color:#1a3a8a}
.state-box.warn{background:#fff8e1;color:#7a5800;border:1px solid #ffe082}
.state-box.warn a{color:#7a5800}
.state-box.error{background:#fdecea;color:#922b21;border:1px solid #f5c6c2}
.state-box.success{background:#e8f5e9;color:#1a6b2e;border:1px solid #c3e6cb}
.state-icon{font-size:14px;flex-shrink:0;margin-top:1px}

/* 로그인 필요 안내 */
.cd-login-notice{margin:0 16px 6px;padding:7px 12px;background:#fff8e1;border-bottom:1px solid #ffe082;font-size:11px;font-weight:700;color:#7a5800;border-radius:6px;display:flex;align-items:center;gap:6px}

/* ─────────────────────────────────────────────
   모달 오버레이 (무료 신청 / 유료 결제 공통)
───────────────────────────────────────────── */
.modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:200;display:flex;align-items:flex-end;justify-content:center;opacity:0;pointer-events:none;transition:opacity .25s}
.modal-overlay.open{opacity:1;pointer-events:auto}
.modal-sheet{width:100%;max-width:540px;background:#fff;border-radius:16px 16px 0 0;padding:0 0 env(safe-area-inset-bottom);transform:translateY(100%);transition:transform .3s ease;max-height:90vh;overflow-y:auto}
.modal-overlay.open .modal-sheet{transform:translateY(0)}

/* 모달 헤더 */
.modal-head{padding:20px 24px 0;display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid #f0f0f0;padding-bottom:14px}
.modal-title{font-size:16px;font-weight:800;color:#1a1a1a}
.modal-close{width:32px;height:32px;border:none;background:none;font-size:22px;color:#888;cursor:pointer;display:flex;align-items:center;justify-content:center;border-radius:50%;transition:background .15s}
.modal-close:hover{background:#f0f0f0}
.modal-body{padding:20px 24px}

/* ── 스텝 인디케이터 ── */
.pay-steps{display:flex;align-items:center;gap:0;margin-bottom:24px}
.pay-step{flex:1;text-align:center;position:relative}
.pay-step-circle{width:28px;height:28px;border-radius:50%;background:#e0e0e0;color:#999;font-size:11px;font-weight:700;display:flex;align-items:center;justify-content:center;margin:0 auto 5px;position:relative;z-index:1}
.pay-step.done .pay-step-circle{background:#27ae60;color:#fff}
.pay-step.active .pay-step-circle{background:#c0392b;color:#fff;box-shadow:0 0 0 4px rgba(192,57,43,.15)}
.pay-step.active.free-step .pay-step-circle{background:#3b5bdb;box-shadow:0 0 0 4px rgba(59,91,219,.15)}
.pay-step-label{font-size:10px;color:#aaa;font-weight:600}
.pay-step.active .pay-step-label{color:#c0392b;font-weight:700}
.pay-step.active.free-step .pay-step-label{color:#3b5bdb}
.pay-step.done .pay-step-label{color:#27ae60}
.pay-step-line{position:absolute;top:14px;left:calc(50% + 14px);right:calc(-50% + 14px);height:2px;background:#e0e0e0;z-index:0}
.pay-step.done .pay-step-line{background:#27ae60}

/* ── 패널 ── */
.step-panel{display:none}
.step-panel.active{display:block}

/* ── 주문 상품 확인 ── */
.order-product{display:flex;gap:14px;align-items:center;padding:14px;background:#fff;border:1px solid #e8e8e8;border-radius:6px;margin-bottom:12px}
.order-thumb{width:80px;height:45px;border-radius:4px;flex-shrink:0;background:linear-gradient(135deg,#1a1a2e,#0f2027);overflow:hidden}
.order-thumb img{width:100%;height:100%;object-fit:cover}
.order-info-title{font-size:13px;font-weight:700;color:#1a1a1a;margin-bottom:3px;line-height:1.4}
.order-info-meta{font-size:11.5px;color:#888}
.order-row{display:flex;justify-content:space-between;align-items:center;padding:10px 14px;background:#fff;border:1px solid #e8e8e8;border-radius:6px;margin-bottom:6px;font-size:13px}
.order-row .lbl{color:#666}
.order-row .val{font-weight:700;color:#1a1a1a}
.order-row.total{background:#fff5f5;border-color:#f5c6c2}
.order-row.total .val{color:#c0392b;font-size:15px}
.order-row.total-free{background:#eff4ff;border-color:#c5d5f5}
.order-row.total-free .val{color:#3b5bdb;font-size:15px}

/* ── 결제 수단 ── */
.pay-method-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:8px;margin-bottom:16px}
.pay-method-item{border:2px solid #e0e0e0;border-radius:6px;padding:10px 6px;text-align:center;cursor:pointer;transition:all .15s;background:#fff}
.pay-method-item:hover{border-color:#c0392b}
.pay-method-item.selected{border-color:#c0392b;background:#fff5f5}
.pay-method-icon{font-size:20px;margin-bottom:4px}
.pay-method-label{font-size:11.5px;font-weight:600;color:#333}
.pay-method-note{font-size:10px;color:#999;margin-top:2px}
.pg-notice{padding:10px 14px;background:#fff8e1;border:1px solid #ffe082;border-radius:6px;font-size:11.5px;color:#7a5800;line-height:1.6;margin-bottom:14px}

/* ── 정보 입력 ── */
.pay-field{margin-bottom:10px}
.pay-field label{display:block;font-size:12px;font-weight:700;color:#555;margin-bottom:3px}
.pay-field input,.pay-field select{width:100%;height:36px;border:1px solid #ddd;border-radius:5px;padding:0 10px;font-size:13px;font-family:inherit;color:#333;outline:none;transition:border-color .15s;background:#fff;box-sizing:border-box}
.pay-field input:focus,.pay-field select:focus{border-color:#c0392b}
.pay-field-row{display:flex;gap:8px}
.pay-field-row .pay-field{flex:1;min-width:0}
.pay-card-section{font-size:12px;font-weight:700;color:#1a3a5c;margin-bottom:10px}
.pay-card-section span{font-weight:400;color:#aaa;font-size:11px}
.pay-autofill-note{font-size:11.5px;color:#aaa;margin-bottom:12px;padding:6px 10px;background:#f9f9f9;border-radius:4px}

/* ── 약관 동의 ── */
.agree-all{padding:10px 14px;background:#f5f5f5;border-radius:6px;margin-bottom:8px;display:flex;align-items:center;gap:8px;font-size:13px;font-weight:700;cursor:pointer}
.agree-item{padding:6px 14px;display:flex;align-items:center;gap:8px;font-size:12px;color:#555;cursor:pointer}
.agree-item a{color:#c0392b;text-decoration:underline;margin-left:4px}
input[type=checkbox]{accent-color:#c0392b;width:14px;height:14px;cursor:pointer;flex-shrink:0}
.final-amount-row{display:flex;justify-content:space-between;align-items:center;padding:12px 14px;background:#fff5f5;border:1px solid #f5c6c2;border-radius:6px;margin-bottom:14px}
.final-amount-row .lbl{font-size:13px;font-weight:700;color:#555}
.final-amount-row .val{font-size:18px;font-weight:900;color:#c0392b}

/* ── 완료 화면 ── */
.pay-result{text-align:center;padding:24px 0 8px}
.pay-result-icon{font-size:48px;margin-bottom:14px}
.pay-result-title{font-size:18px;font-weight:900;color:#1a1a1a;margin-bottom:8px}
.pay-result-desc{font-size:13px;color:#888;line-height:1.7;margin-bottom:16px}
.pay-result-detail{background:#f9f9f9;border:1px solid #eee;border-radius:6px;padding:14px 16px;text-align:left;margin-bottom:16px}
.pay-result-row{display:flex;justify-content:space-between;font-size:12.5px;padding:4px 0;border-bottom:1px solid #f0f0f0}
.pay-result-row:last-child{border-bottom:none}
.pay-result-row .rl{color:#888}
.pay-result-row .rv{font-weight:700;color:#1a1a1a}
.btn-kakao{display:flex;align-items:center;justify-content:center;gap:8px;width:100%;height:44px;border-radius:6px;background:#fee500;color:#3c1e1e;font-size:13px;font-weight:800;text-decoration:none;margin-bottom:8px;border:none;cursor:pointer}
.btn-vimeo{display:flex;align-items:center;justify-content:center;gap:8px;width:100%;height:44px;border-radius:6px;background:#1ab7ea;color:#fff;font-size:13px;font-weight:800;text-decoration:none;margin-bottom:8px;border:none;cursor:pointer}
.pay-link-note{font-size:11px;color:#aaa;text-align:center;margin-bottom:16px}

/* ── 결제 에러 상태 ── */
.pay-error-box{padding:14px;background:#fdecea;border:1px solid #f5c6c2;border-radius:6px;margin-bottom:14px;font-size:13px;color:#922b21;text-align:center;display:none}

/* ── 내비 버튼 ── */
.pay-nav{display:flex;gap:10px;margin-top:16px}
.pay-nav-btn{flex:1;height:42px;border-radius:6px;font-size:14px;font-weight:700;cursor:pointer;border:none;transition:all .15s}
.pay-nav-btn.prev{background:#f0f0f0;color:#555}
.pay-nav-btn.prev:hover{background:#e0e0e0}
.pay-nav-btn.next{background:#c0392b;color:#fff}
.pay-nav-btn.next:hover{background:#a93226}
.pay-nav-btn.next:disabled{background:#ccc;cursor:not-allowed}
.pay-nav-btn.free-btn{background:#3b5bdb;color:#fff}
.pay-nav-btn.free-btn:hover{background:#2f4abf}
.pay-nav-btn.free-btn:disabled{background:#ccc;cursor:not-allowed}
.pay-nav-btn.green-btn{background:#27ae60;color:#fff}
.pay-nav-btn.green-btn:hover{background:#219a52}

/* ── Loading 스피너 ── */
.pay-loading{text-align:center;padding:40px 0;display:none}
.pay-loading.show{display:block}
.spinner{width:36px;height:36px;border:3px solid #f0f0f0;border-top-color:#c0392b;border-radius:50%;animation:spin .7s linear infinite;margin:0 auto 12px}
@keyframes spin{to{transform:rotate(360deg)}}

/* ── 반응형 ── */
@media(max-width:960px){
  .cd-wrap{flex-direction:column}
  .cd-right{width:100%;position:static}
}
@media(max-width:600px){
  .cd-wrap,.cd-terms{padding-left:16px;padding-right:16px}
  .cd-breadcrumb{padding-left:16px;padding-right:16px}
}
</style>

<?php /* ──────────────── 브레드크럼 ──────────────── */ ?>
<div class="cd-breadcrumb">
  <a href="/">홈</a><span>›</span>
  <a href="/classes?type=<?= $class['type'] ?>"><?= $isFree ? '무료강의' : '프리미엄강의' ?></a><span>›</span>
  <span class="cur"><?= htmlspecialchars(mb_strimwidth($class['title'], 0, 60, '...')) ?></span>
</div>

<?php /* ──────────────── 2단 레이아웃 ──────────────── */ ?>
<div class="cd-wrap">

  <?php /* ── 좌: 썸네일 + 내용 탭 ── */ ?>
  <div class="cd-left">

    <?php /* 썸네일 */ ?>
    <div class="cd-thumb">
      <?php if (!empty($class['thumbnail'])): ?>
      <img src="/uploads/class/<?= htmlspecialchars($class['thumbnail']) ?>"
           alt="<?= htmlspecialchars($class['title']) ?>">
      <?php else: ?>
      <div style="width:100%;height:100%;background:linear-gradient(135deg,#0f2027,#203a43,#2c5364)"></div>
      <?php endif; ?>
    </div>

    <?php /* 내용 탭 */ ?>
    <div class="cd-tabs">
      <div class="cd-tab active" onclick="switchDetailTab('section-class', this)">클래스 소개</div>
      <div class="cd-tab" onclick="switchDetailTab('section-inst', this)">강사 소개</div>
    </div>

    <?php /* 클래스 소개 */ ?>
    <div id="section-class">
      <?php if (!empty($class['description'])): ?>
      <div class="cd-desc-body"><?= $class['description'] ?></div>
      <?php else: ?>
      <div class="cd-desc-empty">강의 소개 내용이 준비 중입니다.</div>
      <?php endif; ?>
    </div>

    <?php /* 강사 소개 */ ?>
    <div id="section-inst" style="display:none">
      <div class="cd-inst-box">
        <div class="cd-inst-head">강사 소개</div>
        <div class="cd-inst-row">
          <div class="cd-inst-profile">
            <div class="cd-inst-avatar">
              <?php if (!empty($class['instructor_photo'])): ?>
              <img src="/uploads/instructor/<?= htmlspecialchars($class['instructor_photo']) ?>"
                   alt="<?= htmlspecialchars($class['instructor_name']) ?>">
              <?php else: ?>
              <?= mb_substr($class['instructor_name'], 0, 1) ?>
              <?php endif; ?>
            </div>
            <div>
              <div class="cd-inst-name"><?= htmlspecialchars($class['instructor_name']) ?></div>
              <div class="cd-inst-field"><?= htmlspecialchars($class['instructor_field'] ?? '') ?></div>
            </div>
          </div>
          <div class="cd-inst-toggle" onclick="toggleInstDetail(this)">+</div>
        </div>

        <div class="cd-inst-detail" id="instDetail">
          <?php if (!empty($class['instructor_intros'])): ?>
          <div class="cd-inst-section-title">소개</div>
          <ul class="bullet-list">
            <?php foreach ($class['instructor_intros'] as $intro): ?>
            <li><?= htmlspecialchars($intro['content']) ?></li>
            <?php endforeach; ?>
          </ul>
          <?php endif; ?>

          <?php if (!empty($class['instructor_careers'])): ?>
          <div class="cd-inst-section-title">경력</div>
          <ul class="bullet-list">
            <?php foreach ($class['instructor_careers'] as $career): ?>
            <li><?= htmlspecialchars($career['content']) ?></li>
            <?php endforeach; ?>
          </ul>
          <?php endif; ?>

          <a href="/instructors/<?= $class['instructor_idx'] ?>" class="cd-inst-link">
            강사 상세 페이지 →
          </a>
        </div>
      </div>
    </div>

  </div><!-- /cd-left -->

  <?php /* ── 우: 신청/결제 박스 ── */ ?>
  <div class="cd-right">
    <div class="cd-box">

      <?php /* 배지 */ ?>
      <div class="cd-box-badges">
        <?php if ($class['badge_hot']): ?><span class="dbadge db-hot">HOT</span><?php endif; ?>
        <?php if ($class['badge_new']): ?><span class="dbadge db-new">NEW</span><?php endif; ?>
        <?php if ($isFree): ?><span class="dbadge db-free">무료강의</span>
        <?php else: ?><span class="dbadge db-premium">Premium</span><?php endif; ?>
      </div>

      <?php /* 제목 */ ?>
      <div class="cd-box-title"><?= htmlspecialchars($class['title']) ?></div>

      <?php /* 찜하기 / 링크복사 */ ?>
      <div class="cd-box-actions">
        <button class="action-btn <?= $isWished ? 'wished' : '' ?>"
                id="wishBtn"
                onclick="toggleWish()">
          <span id="wishIcon"><?= $isWished ? '❤️' : '🤍' ?></span>
          <span id="wishText"><?= $isWished ? ' 찜완료' : ' 찜하기' ?></span>
        </button>
        <button class="action-btn" onclick="copyLink()">
          <span>🔗</span> 링크복사
        </button>
      </div>

      <?php /* 강사 */ ?>
      <div class="cd-box-row">
        강사 <span><?= htmlspecialchars($class['instructor_name']) ?></span>
      </div>

      <?php /* ─── 무료강의 전용 ─── */ ?>
      <?php if ($isFree): ?>

      <?php /* 강의일 (sale_end_at 기준) */ ?>
      <?php if ($saleEndFmt): ?>
      <div class="cd-box-row">
        📅 강의일 <span><?= $saleEndFmt ?></span>
      </div>
      <?php if ($btnStatus === 'apply' && $saleEndTs > time()): ?>
      <div class="cd-timer-wrap">
        강의까지
        <div class="cd-timer-val" id="countdown">계산 중...</div>
      </div>
      <?php endif; ?>
      <?php endif; ?>

      <div class="cd-price-free">🎟 무료</div>

      <?php /* ─── 프리미엄강의 전용 ─── */ ?>
      <?php else: ?>

      <?php /* 가격 */ ?>
      <div class="cd-price-wrap">
        <?php if ($discountRate > 0): ?>
        <div class="cd-price-origin"><?= number_format($class['price_origin']) ?>원</div>
        <div class="cd-price-row">
          <span class="cd-price-rate"><?= $discountRate ?>%</span>
          <span class="cd-price-val"><?= number_format($class['price']) ?>원</span>
        </div>
        <?php else: ?>
        <div class="cd-price-row">
          <span class="cd-price-val"><?= number_format($class['price']) ?>원</span>
        </div>
        <?php endif; ?>
      </div>

      <?php /* 할부 안내 */ ?>
      <div class="cd-install-wrap">
        <div class="cd-install-label">할부 안내</div>
        <div class="cd-install-chips">
          <span class="cd-install-chip">일시불</span>
          <span class="cd-install-chip">2개월</span>
          <span class="cd-install-chip">3개월</span>
          <span class="cd-install-chip">6개월</span>
          <span class="cd-install-chip">12개월</span>
        </div>
        <div class="cd-install-note">무이자 할부 가능 여부는 카드사마다 상이</div>
      </div>

      <?php /* 강의 자료 */ ?>
      <?php if (!empty($files)): ?>
      <div class="cd-files-wrap">
        <div class="cd-files-label">📎 강의 자료</div>
        <?php foreach ($files as $f): ?>
        <?php if ($f['file_type'] === 'file'): ?>
        <div class="cd-file-row">
          <div>
            <div class="cd-file-name"><?= htmlspecialchars($f['title']) ?></div>
            <div class="cd-file-meta">
              <?= $f['file_size'] ? number_format($f['file_size'] / 1048576, 1) . 'MB · ' : '' ?>
              <?= $member ? '' : '로그인 후 다운로드 가능' ?>
            </div>
          </div>
          <?php if ($member): ?>
          <a href="/uploads/materials/<?= htmlspecialchars($f['file_path']) ?>"
             class="btn-file-dl" download>다운로드</a>
          <?php else: ?>
          <button class="btn-file-dl" onclick="requireLogin()">다운로드</button>
          <?php endif; ?>
        </div>
        <?php else: ?>
        <div class="cd-file-row link">
          <div>
            <div class="cd-file-name"><?= htmlspecialchars($f['title']) ?></div>
            <div class="cd-file-meta">외부 링크 · <?= $member ? '' : '로그인 후 이동 가능' ?></div>
          </div>
          <?php if ($member): ?>
          <a href="<?= htmlspecialchars($f['external_url']) ?>" target="_blank" rel="noopener"
             class="btn-file-link">열기 →</a>
          <?php else: ?>
          <button class="btn-file-dl" onclick="requireLogin()" style="background:#3b5bdb">열기 →</button>
          <?php endif; ?>
        </div>
        <?php endif; ?>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>

      <?php endif; /* end premium */ ?>

      <?php /* ─────── 버튼 상태별 분기 ─────── */ ?>

      <?php if ($btnStatus === 'login_required'): ?>
      <?php /* CASE 1 — 비로그인 */ ?>
      <div class="cd-login-notice">🔒 로그인 후 <?= $isFree ? '신청' : '결제' ?> 가능합니다</div>
      <div class="cd-box-btn">
        <button class="btn-apply <?= $isFree ? 'free-color' : '' ?>"
                onclick="requireLogin()">
          <?= $isFree ? '무료강의 신청하기' : '결제하기' ?>
        </button>
      </div>

      <?php elseif ($btnStatus === 'enrolled' && $isFree): ?>
      <?php /* CASE 2 — 무료 신청 완료 */ ?>
      <div class="cd-box-btn">
        <button class="btn-apply free-color" disabled>신청 완료</button>
      </div>
      <div class="state-box success" style="margin-top:-8px">
        <span class="state-icon">✅</span>
        <div>이미 신청하셨습니다.<br>강의 당일 카카오톡으로 링크가 전송됩니다.</div>
      </div>
      <?php if (!empty($enroll['kakao_url'])): ?>
      <div style="padding:0 16px 14px">
        <a href="<?= htmlspecialchars($enroll['kakao_url']) ?>"
           onclick="logOpenchat()" target="_blank" rel="noopener"
           class="btn-kakao">💬 카카오 오픈채팅 입장하기</a>
      </div>
      <?php endif; ?>

      <?php elseif ($btnStatus === 'enrolled' && $isPremium): ?>
      <?php /* CASE 3 — 유료 구매 완료 */ ?>
      <div class="cd-box-btn">
        <a href="/classes/<?= $class['class_idx'] ?>/learn" class="btn-go-learn">▶ 수강하러 가기</a>
        <button class="btn-apply" disabled>구매 완료</button>
      </div>
      <div class="state-box success" style="margin-top:-8px">
        <span class="state-icon">✅</span>
        <div>이미 구매하셨습니다. 마이페이지 &gt; 내 강의에서 수강하실 수 있습니다.</div>
      </div>

      <?php elseif ($btnStatus === 'closed'): ?>
      <?php /* CASE 5 — 마감 */ ?>
      <div class="cd-box-btn">
        <button class="btn-apply" disabled><?= $isFree ? '신청 마감' : '판매 종료' ?></button>
      </div>
      <div class="state-box error" style="margin-top:-8px">
        <span class="state-icon">✖</span>
        <div><?= $isFree ? '신청이 마감되었습니다.' : '판매가 종료된 강의입니다.' ?>
          <br><a href="/classes">다른 강의 보러 가기 →</a></div>
      </div>

      <?php else: ?>
      <?php /* CASE 6 / 정상 — 신청/결제 가능 */ ?>
      <?php if ($isFree): ?>
      <div class="cd-box-btn">
        <button class="btn-apply free-color" onclick="openFreeModal()">무료강의 신청하기</button>
      </div>
      <?php else: ?>
      <div class="cd-box-btn">
        <button class="btn-apply" onclick="openPayModal()">결제하기</button>
      </div>
      <?php endif; ?>
      <?php endif; ?>

    </div><!-- /cd-box -->
  </div><!-- /cd-right -->

</div><!-- /cd-wrap -->

<?php /* ──────────────── 마케팅 수신·면책조항 ──────────────── */ ?>
<div class="cd-terms">
  <?php if ($isFree): ?>
  <div class="cd-terms-block">
    <div class="cd-terms-title">마케팅 수신 동의</div>
    <div class="cd-terms-body">
      무료강의를 신청하면 개인정보 수집 및 마케팅 메시지 수신에 동의하는 것으로 간주합니다.<br>
      이를 원하지 않을 경우 무료강의 신청이 어려울 점 양해 부탁드립니다.
    </div>
  </div>
  <?php endif; ?>
  <div class="cd-terms-block">
    <div class="cd-terms-title">면책조항</div>
    <div class="cd-terms-body">
      저희는 콘텐츠 이득을 약속하지 않습니다.<br>
      제시된 모든 기준은 불법이 아닌 도덕·윤리를 준수할 수 있는 것으로 간주합니다.<br>
      이 강의는 '당신만의 역량'이 아닌 반드시 되고자 하는 기술의 습득 가능성을 설명합니다.<br>
      저희는 결과가 사용자의 노력에 따라 달라진다는 면책 조항을 포함합니다.
    </div>
  </div>
</div>

<div style="height:60px"></div>

<?php /* ════════════════════════════════════════
  무료강의 신청 모달 (3단계)
  — $isFree && $btnStatus === 'apply' 일 때만 생성
════════════════════════════════════════ */ ?>
<?php if ($isFree && $btnStatus === 'apply'): ?>
<div class="modal-overlay" id="freeModal">
  <div class="modal-sheet">
    <div class="modal-head">
      <div class="modal-title">무료강의 신청</div>
      <button class="modal-close" onclick="closeFreeModal()">×</button>
    </div>
    <div class="modal-body">

      <?php /* 스텝 인디케이터 */ ?>
      <div class="pay-steps" id="freeSteps">
        <div class="pay-step active free-step" id="fs-ind-1">
          <div class="pay-step-circle">1</div>
          <div class="pay-step-label">신청 확인</div>
          <div class="pay-step-line"></div>
        </div>
        <div class="pay-step free-step" id="fs-ind-2">
          <div class="pay-step-circle">2</div>
          <div class="pay-step-label">동의</div>
          <div class="pay-step-line"></div>
        </div>
        <div class="pay-step free-step" id="fs-ind-3">
          <div class="pay-step-circle">3</div>
          <div class="pay-step-label">완료</div>
        </div>
      </div>

      <?php /* STEP 1: 신청 확인 */ ?>
      <div class="step-panel active" id="fp-1">
        <div class="order-product">
          <div class="order-thumb">
            <?php if (!empty($class['thumbnail'])): ?>
            <img src="/uploads/class/<?= htmlspecialchars($class['thumbnail']) ?>"
                 alt="<?= htmlspecialchars($class['title']) ?>">
            <?php endif; ?>
          </div>
          <div>
            <div class="order-info-title"><?= htmlspecialchars($class['title']) ?></div>
            <div class="order-info-meta">
              <?= htmlspecialchars($class['instructor_name']) ?> ·
              <?= htmlspecialchars($class['category_name'] ?? '') ?> · 무료강의
            </div>
          </div>
        </div>
        <?php if ($saleEndFmt): ?>
        <div class="order-row">
          <span class="lbl">강의일</span>
          <span class="val"><?= $saleEndFmt ?></span>
        </div>
        <?php endif; ?>
        <div class="order-row">
          <span class="lbl">수강 방식</span>
          <span class="val">카카오 오픈채팅 링크 (신청 완료 후 공개)</span>
        </div>
        <div class="order-row total-free">
          <span class="lbl" style="font-weight:700">수강료</span>
          <span class="val">무료</span>
        </div>
        <div class="pay-nav">
          <button class="pay-nav-btn prev" onclick="closeFreeModal()">취소</button>
          <button class="pay-nav-btn free-btn" onclick="goFreeStep(2)">다음 — 동의하기</button>
        </div>
      </div>

      <?php /* STEP 2: 동의 */ ?>
      <div class="step-panel" id="fp-2">
        <div style="background:#fff9f0;border:1px solid #ffe0b2;border-radius:6px;padding:12px 14px;font-size:12px;color:#7a4a00;line-height:1.7;margin-bottom:14px;">
          ⚠ 무료강의를 신청하면 아래 항목에 동의하는 것으로 간주합니다.
        </div>
        <label class="agree-all"><input type="checkbox" id="f-chk-all" onchange="toggleFreeAll(this)"> 아래 항목에 모두 동의합니다</label>
        <label class="agree-item"><input type="checkbox" class="f-chk-item"> [필수] 개인정보 수집 및 이용 동의<a href="/supports/privacy" target="_blank">보기</a></label>
        <label class="agree-item"><input type="checkbox" class="f-chk-item"> [필수] 마케팅 메시지 수신 동의 (카카오·문자·이메일)<a href="/supports/terms" target="_blank">보기</a></label>
        <label class="agree-item"><input type="checkbox" class="f-chk-item"> [필수] 면책조항 확인<a href="#" onclick="event.preventDefault();alert('면책조항: 강의 결과는 개인의 노력에 따라 달라집니다.')">보기</a></label>
        <div id="f-agree-error" class="state-box error" style="display:none;margin:10px 0 0">
          <span class="state-icon">⚠</span><div>필수 항목에 모두 동의해 주세요.</div>
        </div>
        <div class="pay-nav">
          <button class="pay-nav-btn prev" onclick="goFreeStep(1)">이전</button>
          <button class="pay-nav-btn free-btn" onclick="submitFreeEnroll()">무료강의 신청하기</button>
        </div>
      </div>

      <?php /* STEP 3: 완료 (JS에서 채워넣음) */ ?>
      <div class="step-panel" id="fp-3">
        <div class="pay-loading show" id="freeLoading">
          <div class="spinner"></div>
          <div style="font-size:13px;color:#888">신청 처리 중...</div>
        </div>
        <div class="pay-result" id="freeSuccess" style="display:none">
          <div class="pay-result-icon">✅</div>
          <div class="pay-result-title">신청이 완료되었습니다!</div>
          <div class="pay-result-desc">
            신청해 주셔서 감사합니다.<br>
            오픈채팅방에 입장하시면 강의 당일 알림을 받으실 수 있습니다.
          </div>
          <div id="freeResultKakao"></div>
          <div class="pay-link-note">링크는 마이페이지 &gt; 내 강의에서도 확인 가능합니다</div>
          <div class="pay-nav">
            <button class="pay-nav-btn prev" onclick="location.href='/'">← 메인으로</button>
            <button class="pay-nav-btn green-btn" onclick="location.href='/mypage/my-class'">내 강의 보기</button>
          </div>
        </div>
        <div class="pay-result" id="freeError" style="display:none">
          <div class="pay-result-icon">❌</div>
          <div class="pay-result-title">신청에 실패했습니다</div>
          <div class="pay-result-desc" id="freeErrorMsg"></div>
          <div class="pay-nav">
            <button class="pay-nav-btn prev" onclick="closeFreeModal()">닫기</button>
            <button class="pay-nav-btn free-btn" onclick="goFreeStep(2)">다시 시도</button>
          </div>
        </div>
      </div>

    </div><!-- /modal-body -->
  </div><!-- /modal-sheet -->
</div><!-- /freeModal -->
<?php endif; ?>

<?php /* ════════════════════════════════════════
  유료 결제 모달 (5단계)
  — $isPremium && $btnStatus === 'apply' 일 때만 생성
════════════════════════════════════════ */ ?>
<?php if ($isPremium && $btnStatus === 'apply'): ?>
<div class="modal-overlay" id="payModal">
  <div class="modal-sheet">
    <div class="modal-head">
      <div class="modal-title">결제하기</div>
      <button class="modal-close" onclick="closePayModal()">×</button>
    </div>
    <div class="modal-body">

      <?php /* 스텝 인디케이터 */ ?>
      <div class="pay-steps">
        <div class="pay-step active" id="ps-ind-1"><div class="pay-step-circle">1</div><div class="pay-step-label">주문 확인</div><div class="pay-step-line"></div></div>
        <div class="pay-step" id="ps-ind-2"><div class="pay-step-circle">2</div><div class="pay-step-label">결제수단</div><div class="pay-step-line"></div></div>
        <div class="pay-step" id="ps-ind-3"><div class="pay-step-circle">3</div><div class="pay-step-label">정보 입력</div><div class="pay-step-line"></div></div>
        <div class="pay-step" id="ps-ind-4"><div class="pay-step-circle">4</div><div class="pay-step-label">약관 동의</div><div class="pay-step-line"></div></div>
        <div class="pay-step" id="ps-ind-5"><div class="pay-step-circle">5</div><div class="pay-step-label">완료</div></div>
      </div>

      <?php /* STEP 1: 주문 확인 */ ?>
      <div class="step-panel active" id="pp-1">
        <div class="order-product">
          <div class="order-thumb">
            <?php if (!empty($class['thumbnail'])): ?>
            <img src="/uploads/class/<?= htmlspecialchars($class['thumbnail']) ?>"
                 alt="<?= htmlspecialchars($class['title']) ?>">
            <?php endif; ?>
          </div>
          <div>
            <div class="order-info-title"><?= htmlspecialchars($class['title']) ?></div>
            <div class="order-info-meta">
              <?= htmlspecialchars($class['instructor_name']) ?> · 프리미엄강의
            </div>
          </div>
        </div>
        <?php if ($discountRate > 0): ?>
        <div class="order-row">
          <span class="lbl">정가</span>
          <span class="val" style="text-decoration:line-through;color:#bbb"><?= number_format($class['price_origin']) ?>원</span>
        </div>
        <div class="order-row">
          <span class="lbl">할인 (<?= $discountRate ?>%)</span>
          <span class="val" style="color:#27ae60">-<?= number_format($class['price_origin'] - $class['price']) ?>원</span>
        </div>
        <?php endif; ?>
        <div class="order-row total">
          <span class="lbl" style="font-weight:700">최종 결제금액</span>
          <span class="val"><?= number_format($class['price']) ?>원</span>
        </div>
        <div class="pay-nav">
          <button class="pay-nav-btn prev" onclick="closePayModal()">취소</button>
          <button class="pay-nav-btn next" onclick="goPayStep(2)">다음 — 결제수단 선택</button>
        </div>
      </div>

      <?php /* STEP 2: 결제 수단 */ ?>
      <div class="step-panel" id="pp-2">
        <div class="pg-notice">
          ⚠ PG사 미정 — 실제 연동 시 PG사 SDK 팝업 또는 인라인 결제창으로 대체됩니다.<br>
          현재는 UI 기획 목적의 화면입니다.
        </div>
        <div class="pay-method-grid">
          <div class="pay-method-item selected" onclick="selectPayMethod(this,'card')" data-method="card">
            <div class="pay-method-icon">💳</div>
            <div class="pay-method-label">신용/체크카드</div>
            <div class="pay-method-note">국내외 카드</div>
          </div>
          <div class="pay-method-item" onclick="selectPayMethod(this,'easyPay')" data-method="easyPay">
            <div class="pay-method-icon">📱</div>
            <div class="pay-method-label">간편결제</div>
            <div class="pay-method-note">카카오·네이버·토스</div>
          </div>
          <div class="pay-method-item" onclick="selectPayMethod(this,'transfer')" data-method="transfer">
            <div class="pay-method-icon">🏦</div>
            <div class="pay-method-label">계좌이체</div>
            <div class="pay-method-note">실시간 이체</div>
          </div>
          <div class="pay-method-item" onclick="selectPayMethod(this,'phone')" data-method="phone">
            <div class="pay-method-icon">📲</div>
            <div class="pay-method-label">휴대폰 결제</div>
            <div class="pay-method-note">통신사 소액결제</div>
          </div>
          <div class="pay-method-item" onclick="selectPayMethod(this,'vbank')" data-method="vbank">
            <div class="pay-method-icon">🏧</div>
            <div class="pay-method-label">가상계좌</div>
            <div class="pay-method-note">입금 확인 후 처리</div>
          </div>
          <div class="pay-method-item" onclick="selectPayMethod(this,'coupon')" data-method="coupon">
            <div class="pay-method-icon">🎟</div>
            <div class="pay-method-label">쿠폰/포인트</div>
            <div class="pay-method-note">보유 시 적용</div>
          </div>
        </div>
        <div class="pay-nav">
          <button class="pay-nav-btn prev" onclick="goPayStep(1)">이전</button>
          <button class="pay-nav-btn next" onclick="goPayStep(3)">다음 — 정보 입력</button>
        </div>
      </div>

      <?php /* STEP 3: 정보 입력 */ ?>
      <div class="step-panel" id="pp-3">
        <div class="pay-autofill-note">ℹ 회원 정보에서 자동 불러왔습니다. 수정 시 이 주문에만 적용됩니다.</div>
        <div class="pay-field">
          <label>이름 <span style="color:#c0392b">*</span></label>
          <input type="text" id="ordererName" value="<?= $ordererName ?>" placeholder="홍길동">
        </div>
        <div class="pay-field-row">
          <div class="pay-field">
            <label>이메일 <span style="color:#c0392b">*</span></label>
            <input type="email" id="ordererEmail" value="<?= $ordererEmail ?>" placeholder="hello@email.com">
          </div>
          <div class="pay-field">
            <label>연락처 <span style="color:#c0392b">*</span></label>
            <input type="tel" id="ordererPhone" value="<?= $ordererPhone ?>" placeholder="010-0000-0000">
          </div>
        </div>
        <div class="pay-card-section">
          카드 정보
          <span>(PG사 연동 후 팝업 또는 인라인 입력으로 대체)</span>
        </div>
        <div class="pay-field">
          <label>카드 번호</label>
          <input type="text" id="cardNum" placeholder="0000 - 0000 - 0000 - 0000">
        </div>
        <div class="pay-field-row">
          <div class="pay-field"><label>유효기간</label><input type="text" placeholder="MM / YY"></div>
          <div class="pay-field"><label>CVC</label><input type="text" placeholder="000"></div>
          <div class="pay-field">
            <label>할부</label>
            <select id="installment">
              <option value="0">일시불</option>
              <option value="2">2개월</option>
              <option value="3">3개월</option>
              <option value="6">6개월</option>
              <option value="12">12개월</option>
            </select>
          </div>
        </div>
        <div class="pay-nav">
          <button class="pay-nav-btn prev" onclick="goPayStep(2)">이전</button>
          <button class="pay-nav-btn next" onclick="validateInfoAndNext()">다음 — 약관 동의</button>
        </div>
      </div>

      <?php /* STEP 4: 약관 동의 */ ?>
      <div class="step-panel" id="pp-4">
        <div class="final-amount-row">
          <span class="lbl">최종 결제금액</span>
          <span class="val"><?= number_format($class['price']) ?>원</span>
        </div>
        <label class="agree-all"><input type="checkbox" id="p-chk-all" onchange="togglePayAll(this)"> 아래 약관에 모두 동의합니다</label>
        <label class="agree-item"><input type="checkbox" class="p-chk-item"> [필수] 구매조건 확인 및 결제 진행 동의<a href="/supports/terms" target="_blank">보기</a></label>
        <label class="agree-item"><input type="checkbox" class="p-chk-item"> [필수] 개인정보 제3자 제공 동의 (PG사)<a href="/supports/privacy" target="_blank">보기</a></label>
        <label class="agree-item"><input type="checkbox" class="p-chk-item"> [필수] 전자금융거래 이용약관<a href="/supports/terms" target="_blank">보기</a></label>
        <label class="agree-item"><input type="checkbox" class="p-chk-item optional"> [선택] 마케팅 정보 수신 동의<a href="/supports/privacy" target="_blank">보기</a></label>
        <div id="p-agree-error" class="state-box error" style="display:none;margin:10px 0 0">
          <span class="state-icon">⚠</span><div>필수 약관에 모두 동의해 주세요.</div>
        </div>
        <div class="pay-nav">
          <button class="pay-nav-btn prev" onclick="goPayStep(3)">이전</button>
          <button class="pay-nav-btn next" onclick="submitPayment()"><?= number_format($class['price']) ?>원 결제하기</button>
        </div>
      </div>

      <?php /* STEP 5: 완료/실패 */ ?>
      <div class="step-panel" id="pp-5">
        <div class="pay-loading show" id="payLoading">
          <div class="spinner"></div>
          <div style="font-size:13px;color:#888">결제 처리 중...</div>
        </div>
        <div class="pay-result" id="paySuccess" style="display:none">
          <div class="pay-result-icon">🎉</div>
          <div class="pay-result-title">결제가 완료되었습니다!</div>
          <div class="pay-result-desc">강의 수강 정보가 가입된 이메일로 발송되었습니다.</div>
          <div class="pay-result-detail" id="payResultDetail"></div>
          <div id="payResultLinks"></div>
          <div class="pay-link-note">위 링크는 마이페이지 &gt; 내 강의에서도 확인 가능합니다</div>
          <div class="pay-nav">
            <button class="pay-nav-btn prev" onclick="location.href='/'">← 메인으로</button>
            <button class="pay-nav-btn next" onclick="location.href='/mypage/orders'">결제 내역 보기</button>
          </div>
        </div>
        <div class="pay-result" id="payFail" style="display:none">
          <div class="pay-result-icon">❌</div>
          <div class="pay-result-title">결제에 실패했습니다</div>
          <div class="pay-result-desc" id="payFailMsg">카드 정보를 확인하거나 다른 결제수단을 이용해 주세요.</div>
          <div class="pay-nav">
            <button class="pay-nav-btn prev" onclick="closePayModal()">닫기</button>
            <button class="pay-nav-btn next" onclick="goPayStep(2)">다시 시도</button>
          </div>
        </div>
      </div>

    </div><!-- /modal-body -->
  </div><!-- /modal-sheet -->
</div><!-- /payModal -->
<?php endif; ?>

<script>
/* ───────────────────────────────────────────────────
   기본 설정값 (PHP에서 주입)
─────────────────────────────────────────────────── */
const CLASS_IDX   = <?= (int) $class['class_idx'] ?>;
const CSRF_TOKEN  = '<?= htmlspecialchars($csrfToken) ?>';
const IS_LOGGED_IN= <?= $member ? 'true' : 'false' ?>;
const IS_FREE     = <?= $isFree ? 'true' : 'false' ?>;
const RETURN_URL  = '/classes/' + CLASS_IDX;

<?php if ($saleEndTs && $isFree && $btnStatus === 'apply'): ?>
/* ── 카운트다운 타이머 ── */
const TARGET_TS = <?= $saleEndTs ?> * 1000;
(function tick() {
  const diff = TARGET_TS - Date.now();
  if (diff <= 0) { document.getElementById('countdown').textContent = '신청 마감'; return; }
  const d = Math.floor(diff / 86400000);
  const h = Math.floor((diff % 86400000) / 3600000);
  const m = Math.floor((diff % 3600000) / 60000);
  const s = Math.floor((diff % 60000) / 1000);
  document.getElementById('countdown').textContent =
    (d > 0 ? d + '일 ' : '') + String(h).padStart(2,'0') + '시간 ' +
    String(m).padStart(2,'0') + '분 ' + String(s).padStart(2,'0') + '초';
  setTimeout(tick, 1000);
})();
<?php endif; ?>

/* ── 내용 탭 전환 ── */
function switchDetailTab(sectionId, el) {
  document.querySelectorAll('.cd-tab').forEach(t => t.classList.remove('active'));
  el.classList.add('active');
  document.getElementById('section-class').style.display = sectionId === 'section-class' ? '' : 'none';
  document.getElementById('section-inst').style.display  = sectionId === 'section-inst'  ? '' : 'none';
}

/* ── 강사 소개 아코디언 ── */
function toggleInstDetail(btn) {
  const detail = document.getElementById('instDetail');
  const isOpen = detail.classList.toggle('open');
  btn.textContent = isOpen ? '−' : '+';
}

/* ── 로그인 필요 ── */
function requireLogin() {
  location.href = '/login?returnUrl=' + encodeURIComponent(RETURN_URL);
}

/* ── 링크 복사 ── */
function copyLink() {
  navigator.clipboard.writeText(location.href).then(() => alert('링크가 복사되었습니다.'));
}

/* ── 찜하기 토글 (AJAX) ── */
function toggleWish() {
  if (!IS_LOGGED_IN) { requireLogin(); return; }
  fetch('/api/wish/' + CLASS_IDX, {
    method: 'POST',
    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    body: 'csrf_token=' + encodeURIComponent(CSRF_TOKEN),
  })
  .then(r => r.json())
  .then(data => {
    if (!data.success) { alert(data.error || '오류가 발생했습니다.'); return; }
    const btn  = document.getElementById('wishBtn');
    const icon = document.getElementById('wishIcon');
    const text = document.getElementById('wishText');
    if (data.wished) {
      btn.classList.add('wished');
      icon.textContent = '❤️';
      text.textContent = ' 찜완료';
    } else {
      btn.classList.remove('wished');
      icon.textContent = '🤍';
      text.textContent = ' 찜하기';
    }
  });
}

/* ── 오픈채팅 클릭 로그 ── */
function logOpenchat() {
  navigator.sendBeacon('/api/openchat-log/' + CLASS_IDX,
    new URLSearchParams({csrf_token: CSRF_TOKEN}));
}

/* ═══════════════════════════════════════════
   무료 신청 모달
══════════════════════════════════════════= */
<?php if ($isFree && $btnStatus === 'apply'): ?>
let currentFreeStep = 1;

function openFreeModal() {
  document.getElementById('freeModal').classList.add('open');
  document.body.style.overflow = 'hidden';
}
function closeFreeModal() {
  document.getElementById('freeModal').classList.remove('open');
  document.body.style.overflow = '';
}

function goFreeStep(step) {
  [1,2,3].forEach(i => {
    document.getElementById('fp-'+i).classList.toggle('active', i === step);
    const ind = document.getElementById('fs-ind-'+i);
    ind.classList.remove('active','done');
    if (i < step) ind.classList.add('done');
    if (i === step) ind.classList.add('active');
  });
  currentFreeStep = step;
}

function toggleFreeAll(el) {
  document.querySelectorAll('.f-chk-item').forEach(c => c.checked = el.checked);
}

function submitFreeEnroll() {
  // 필수 체크 확인
  const items = document.querySelectorAll('.f-chk-item');
  if ([...items].some(c => !c.checked)) {
    document.getElementById('f-agree-error').style.display = 'flex';
    return;
  }
  document.getElementById('f-agree-error').style.display = 'none';

  goFreeStep(3);
  document.getElementById('freeLoading').classList.add('show');
  document.getElementById('freeSuccess').style.display = 'none';
  document.getElementById('freeError').style.display   = 'none';

  fetch('/classes/' + CLASS_IDX + '/enroll', {
    method: 'POST',
    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    body: 'csrf_token=' + encodeURIComponent(CSRF_TOKEN),
  })
  .then(r => r.json())
  .then(data => {
    document.getElementById('freeLoading').classList.remove('show');
    if (data.success) {
      // 오픈채팅 링크 삽입
      if (data.kakao_url) {
        document.getElementById('freeResultKakao').innerHTML =
          '<a href="' + escHtml(data.kakao_url) + '" onclick="logOpenchat()" target="_blank" rel="noopener" class="btn-kakao">💬 카카오 오픈채팅 입장하기</a>';
      }
      document.getElementById('freeSuccess').style.display = 'block';
      // 부모 버튼 상태 업데이트 (페이지 새로고침 없이)
      const applyBtn = document.querySelector('.btn-apply');
      if (applyBtn) { applyBtn.disabled = true; applyBtn.textContent = '신청 완료'; }
    } else {
      document.getElementById('freeErrorMsg').textContent = data.error || '신청 중 오류가 발생했습니다.';
      document.getElementById('freeError').style.display = 'block';
    }
  })
  .catch(() => {
    document.getElementById('freeLoading').classList.remove('show');
    document.getElementById('freeErrorMsg').textContent = '네트워크 오류가 발생했습니다. 다시 시도해 주세요.';
    document.getElementById('freeError').style.display = 'block';
  });
}
<?php endif; ?>

/* ═══════════════════════════════════════════
   유료 결제 모달
══════════════════════════════════════════= */
<?php if ($isPremium && $btnStatus === 'apply'): ?>
let currentPayStep = 1;
let selectedMethod = 'card';

function openPayModal() {
  document.getElementById('payModal').classList.add('open');
  document.body.style.overflow = 'hidden';
}
function closePayModal() {
  document.getElementById('payModal').classList.remove('open');
  document.body.style.overflow = '';
}

function goPayStep(step) {
  [1,2,3,4,5].forEach(i => {
    document.getElementById('pp-'+i).classList.toggle('active', i === step);
    const ind = document.getElementById('ps-ind-'+i);
    if (!ind) return;
    ind.classList.remove('active','done');
    if (i < step) ind.classList.add('done');
    if (i === step) ind.classList.add('active');
  });
  currentPayStep = step;
}

function selectPayMethod(el, method) {
  document.querySelectorAll('.pay-method-item').forEach(i => i.classList.remove('selected'));
  el.classList.add('selected');
  selectedMethod = method;
}

function togglePayAll(el) {
  document.querySelectorAll('.p-chk-item').forEach(c => c.checked = el.checked);
}

function validateInfoAndNext() {
  const name  = document.getElementById('ordererName').value.trim();
  const email = document.getElementById('ordererEmail').value.trim();
  const phone = document.getElementById('ordererPhone').value.trim();
  if (!name || !email || !phone) {
    alert('이름, 이메일, 연락처를 모두 입력해 주세요.');
    return;
  }
  goPayStep(4);
}

function submitPayment() {
  // 필수 약관 확인 (optional 클래스 제외)
  const required = [...document.querySelectorAll('.p-chk-item:not(.optional)')];
  if (required.some(c => !c.checked)) {
    document.getElementById('p-agree-error').style.display = 'flex';
    return;
  }
  document.getElementById('p-agree-error').style.display = 'none';

  goPayStep(5);
  document.getElementById('payLoading').classList.add('show');
  document.getElementById('paySuccess').style.display = 'none';
  document.getElementById('payFail').style.display    = 'none';

  const body = new URLSearchParams({
    csrf_token:    CSRF_TOKEN,
    pay_method:    selectedMethod,
    orderer_name:  document.getElementById('ordererName').value,
    orderer_email: document.getElementById('ordererEmail').value,
    orderer_phone: document.getElementById('ordererPhone').value,
  });

  fetch('/classes/' + CLASS_IDX + '/checkout', {
    method: 'POST',
    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    body: body.toString(),
  })
  .then(r => r.json())
  .then(data => {
    document.getElementById('payLoading').classList.remove('show');
    if (data.success) {
      // 결과 상세 삽입
      const methodLabel = {
        card:'신용카드', easyPay:'간편결제', transfer:'계좌이체',
        phone:'휴대폰결제', vbank:'가상계좌', coupon:'쿠폰/포인트'
      }[data.method] || data.method;

      document.getElementById('payResultDetail').innerHTML =
        '<div class="pay-result-row"><span class="rl">주문번호</span><span class="rv">' + escHtml(data.order_no) + '</span></div>' +
        '<div class="pay-result-row"><span class="rl">결제금액</span><span class="rv" style="color:#c0392b">' + Number(data.amount).toLocaleString() + '원</span></div>' +
        '<div class="pay-result-row"><span class="rl">결제수단</span><span class="rv">' + methodLabel + '</span></div>' +
        '<div class="pay-result-row"><span class="rl">결제일시</span><span class="rv">' + escHtml(data.paid_at) + '</span></div>';

      let links = '';
      if (data.kakao_url) {
        links += '<a href="' + escHtml(data.kakao_url) + '" onclick="logOpenchat()" target="_blank" class="btn-kakao">💬 카카오 오픈채팅 입장</a>';
      }
      if (data.vimeo_url) {
        links += '<a href="' + escHtml(data.vimeo_url) + '" target="_blank" class="btn-vimeo">▶ 비메오 영상 보기</a>';
      }
      document.getElementById('payResultLinks').innerHTML = links;
      document.getElementById('paySuccess').style.display = 'block';
    } else {
      document.getElementById('payFailMsg').textContent = data.error || '결제에 실패했습니다.';
      document.getElementById('payFail').style.display = 'block';
    }
  })
  .catch(() => {
    document.getElementById('payLoading').classList.remove('show');
    document.getElementById('payFailMsg').textContent = '네트워크 오류가 발생했습니다. 다시 시도해 주세요.';
    document.getElementById('payFail').style.display = 'block';
  });
}
<?php endif; ?>

/* ── XSS 방지 헬퍼 ── */
function escHtml(str) {
  return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

/* ── 모달 외부 클릭 닫기 ── */
document.querySelectorAll('.modal-overlay').forEach(el => {
  el.addEventListener('click', function(e) {
    if (e.target === this) {
      this.classList.remove('open');
      document.body.style.overflow = '';
    }
  });
});
</script>
