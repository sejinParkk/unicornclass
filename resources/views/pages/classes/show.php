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
 *   $btnStatus     — 'apply'|'enrolled'|'closed'|'waiting'|'login_required'
 *   $discountRate  — 할인율 (int, 0이면 할인 없음)
 *   $csrfToken     — CSRF 토큰
 */

$isFree    = $class['type'] === 'free';
$isPremium = $class['type'] === 'premium';

// 강의일 / 판매 마감일 (sale_end_at)
$saleEndAt     = $class['sale_end_at'] ?? null;
$saleEndTs     = $saleEndAt ? strtotime($saleEndAt) : null;
//$saleEndFmt    = $saleEndTs ? date('Y.m.d (D) H:i', $saleEndTs) : null;
$weekDays = ['일', '월', '화', '수', '목', '금', '토'];
$saleEndFmt = $saleEndTs ? date('y/m/d', $saleEndTs) . ' (' . $weekDays[date('w', $saleEndTs)] . ') ' . date('H시 i분', $saleEndTs) : null;

// 판매 시작일 (enroll_start_at)
$enrollStartAt  = $class['enroll_start_at'] ?? null;
$enrollStartTs  = $enrollStartAt ? strtotime($enrollStartAt) : null;
$enrollStartFmt = null;
if ($enrollStartTs) {
    $ampm = date('H', $enrollStartTs) < 12 ? '오전' : '오후';
    $h    = (int) date('g', $enrollStartTs);
    $i    = date('i', $enrollStartTs);
    $enrollStartFmt = date('Y년 n월 j일 ', $enrollStartTs) . $ampm . ' ' . $h . ':' . $i;
}

// 회원 기본정보 (정보 입력 필드 자동완성용)
$ordererName  = htmlspecialchars($member['mb_name']  ?? '');
$ordererEmail = htmlspecialchars($member['mb_email'] ?? '');
$ordererPhone = htmlspecialchars($member['mb_phone'] ?? '');

// 유효 파일 목록
$files = $class['files'] ?? [];
?>
<div class="inner">
  <!-- <?php /* ──────────────── 브레드크럼 ──────────────── */ ?>
  <div class="cd-breadcrumb">
    <a href="/">홈</a><span>›</span>
    <a href="/classes?type=<?= $class['type'] ?>"><?= $isFree ? '무료강의' : '프리미엄강의' ?></a><span>›</span>
    <span class="cur"><?= htmlspecialchars(mb_strimwidth($class['title'], 0, 60, '...')) ?></span>
  </div> -->
  <nav class="page_navi">
    <span>홈</span>
    <img src="/assets/img/icon_page_navi_arr.svg" alt="">
    <span><?= $isFree ? '무료강의' : '프리미엄강의' ?></span>
    <img src="/assets/img/icon_page_navi_arr.svg" alt="">
    <span><?= htmlspecialchars(mb_strimwidth($class['title'], 0, 60, '...')) ?></span>
  </nav>


  <?php /* ──────────────── 2단 레이아웃 ──────────────── */ ?>
  <div class="cd-wrap">

    <?php /* ── 좌: 썸네일 + 내용 탭 ── */ ?>
    <div class="cd-left">
      <?php /* 썸네일 */ ?>
      <div class="cd-thumb">
        <?php if (!empty($class['thumbnail'])): ?>
        <img src="/uploads/class/<?= htmlspecialchars($class['thumbnail']) ?>" alt="<?= htmlspecialchars($class['title']) ?>">
        <?php else: ?>        
          <p><img src="/assets/img/logo.svg"></p>
        <?php endif; ?>
        <p class="cd_thumb_ctag">
          <img src="/assets/img/hero_star_fff2.svg" alt="">
          <span>
            <?php if ($isFree): ?>
            무료 강의
            <?php else: ?>
            프리미엄 강의
            <?php endif; ?>
          </span>
        </p>
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
                <!-- <div class="cd-inst-field"><?= htmlspecialchars($class['instructor_field'] ?? '') ?></div> -->
              </div>
            </div>
            <!-- <div class="cd-inst-toggle" onclick="toggleInstDetail(this)">+</div> -->
          </div>

          <div class="cd-inst-detail" id="instDetail">
            <?php if (!empty($class['instructor_intros'])): ?>
            <div class="cd_inst_section">
              <div class="cd-inst-section-title">소개</div>
              <ul class="bullet-list ver2">
                <?php foreach ($class['instructor_intros'] as $intro): ?>
                <li><?= htmlspecialchars($intro['content']) ?></li>
                <?php endforeach; ?>
              </ul>
            </div>
            <?php endif; ?>

            <?php if (!empty($class['instructor_careers'])): ?>
            <div class="cd_inst_section">
              <div class="cd-inst-section-title">경력</div>
              <ul class="bullet-list ver2">
                <?php foreach ($class['instructor_careers'] as $career): ?>
                <li><?= htmlspecialchars($career['content']) ?></li>
                <?php endforeach; ?>
              </ul>
            </div>
            <?php endif; ?>

            <!-- <a href="/instructors/<?= $class['instructor_idx'] ?>" class="cd-inst-link">강사 상세 페이지</a> -->
          </div>
        </div>
      </div>

      <?php /* ──────────────── 마케팅 수신·면책조항 ──────────────── */ ?>
      <div class="cd-terms">
        <?php //if ($isFree): ?>
        <div class="cd-terms-block">
          <div class="cd-inst-head cd-terms-title">마케팅 수신 동의</div>
          <div class="cd-terms-body">
            무료강의를 신청하면 개인정보 수집 및 마케팅 메시지 수신에 동의하는 것으로 간주합니다.<br>
            이를 원하지 않을 경우 무료강의 신청이 어려울 점 양해 부탁드립니다.
          </div>
        </div>
        <?php //endif; ?>
        <div class="cd-terms-block">
          <div class="cd-inst-head cd-terms-title">면책조항</div>
          <div class="cd-terms-body">
            저희는 콘텐츠 이득을 약속하지 않습니다.<br>
            제시된 모든 기준은 불법이 아닌 도덕·윤리를 준수할 수 있는 것으로 간주합니다.<br>
            이 강의는 '당신만의 역량'이 아닌 반드시 되고자 하는 기술의 습득 가능성을 설명합니다.<br>
            저희는 결과가 사용자의 노력에 따라 달라진다는 면책 조항을 포함합니다.
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
        <div class="cd-box-inst-name"><?= htmlspecialchars($class['instructor_name']) ?></div>

        <?php /* 찜하기 / 링크복사 */ ?>
        <div class="cd-box-actions">
          <button class="action-btn <?= $isWished ? 'wished' : '' ?>" id="wishBtn" onclick="toggleWish()">
            <!-- <span id="wishIcon"><?= $isWished ? '❤️' : '🤍' ?></span>
            <span id="wishText"><?= $isWished ? ' 찜완료' : ' 찜하기' ?></span> -->

            <img src="/assets/img/icon_like_<?= $isWished ? 'on' : 'off' ?>.svg" alt="" id="wishIcon">
            <span id="wishText"><?= $isWished ? ' 찜완료' : ' 찜하기' ?></span>
          </button>
          <button class="action-btn" onclick="copyLink()">
            <img src="/assets/img/icon_share.svg" alt="">
            <span>공유하기</span>
          </button>
        </div>

        <?php /* ─── 무료강의 전용 ─── */ ?>
        <?php if ($isFree && $saleEndFmt): ?>
        <div class="class_date_box">
          <div class="class_date_info">
            <p>
              <img src="/assets/img/icon_calendar.svg" alt="">
              <span>무료 강의일</span>
            </p>
            <p><?= $saleEndFmt ?></p>
          </div>
          <?php if ($btnStatus === 'apply' && $saleEndTs > time()): ?>
          <div class="cd-timer-wrap">
            강의까지 <span class="cd-timer-val" id="countdown">계산 중...</span>
          </div>
          <?php endif; ?>
        </div>
        <?php /* ─── 프리미엄강의 전용 ─── */ ?>
        <?php elseif ($isPremium): ?>

        <?php /* 가격 */ ?>
        <div class="cd-price-wrap">
          <p class="cd-price-per"><?= $discountRate > 0 ? $discountRate.'%할인' : ''; ?></p>
          <div class="cd-price-box">
            <?php if ($discountRate > 0): ?>
            <p class="cd-price-origin"><?= number_format($class['price_origin']) ?></p>
            <p class="cd-price-val"><?= number_format($effectivePrice) ?>원</p>
            <?php else: ?>
            <p class="cd-price-val"><?= number_format($effectivePrice) ?>원</p>
            <?php endif; ?>
          </div>

          <!-- <?php if ($discountRate > 0): ?>
          <div class="cd-price-origin"><?= number_format($class['price_origin']) ?>원</div>
          <div class="cd-price-row">
            <span class="cd-price-rate"></span>
            <span class="cd-price-val"><?= number_format($class['price']) ?>원</span>
          </div>
          <?php else: ?>
          <div class="cd-price-row">
            <span class="cd-price-val"><?= number_format($class['price']) ?>원</span>
          </div>
          <?php endif; ?> -->
        </div>

        <?php if ($btnStatus === 'apply' && $saleEndTs && $saleEndTs > time()): ?>
        <div class="cd-timer-wrap">
          판매 종료까지 <span class="cd-timer-val" id="countdown">계산 중...</span>
        </div>
        <?php endif; ?>

        <?php /* 할부 안내 */ ?>
        <div class="cd-install-wrap">
          <button class="cd-install-btn" onclick="toggleInstallPopover(this)" type="button">
            <span>할부 안내</span>
            <img src="/assets/img/icon_info.svg" alt="">
          </button>
          <div class="cd-install-popover" role="tooltip">
            <div class="cd-install-chips">
              <span class="cd-install-chip">일시불</span>
              <span class="cd-install-chip">2개월</span>
              <span class="cd-install-chip">3개월</span>
              <span class="cd-install-chip">6개월</span>
              <span class="cd-install-chip">12개월</span>
            </div>
            <div class="cd-install-note">무이자 할부 가능 여부는 카드사마다 상이</div>
          </div>
        </div>

        <?php endif; /* end free/premium */ ?>

        <?php /* 강의 자료 — 무료/프리미엄 공통 */ ?>
        <?php if (!empty($files)): ?>
        <?php
        // 무료·프리미엄 모두 신청(수강) 완료 후 열람 가능
        $canAccessFiles = $isEnrolled;
        $fileBlockMsg   = !$member
            ? '로그인 후 이용 가능'
            : (!$isEnrolled ? ($isFree ? '신청 후 이용 가능' : '구매 후 열람 가능') : '');
        ?>
        <div class="cd-files-wrap">
          <button class="cd-files-label" onclick="toggleFiles(this)" aria-expanded="false">
            <p>
              <strong>강의 자료</strong>
              <span class="cd-files-count"><?= count($files) ?>개</span>
            </p>
            <span class="cd-files-arrow"><img src="/assets/img/icon_onoff.svg" alt=""></span>
          </button>
          <div class="cd-files-body" style="display:none">
          <?php foreach ($files as $f): ?>
          <?php if ($f['file_type'] === 'file'): ?>
          <div class="cd-file-row">
            <div>
              <div class="cd-file-name"><?= htmlspecialchars($f['title']) ?></div>
              <div class="cd-file-meta">
                <?= $f['file_size'] ? number_format($f['file_size'] / 1048576, 1) . 'MB' : '' ?>
                <?= $fileBlockMsg ? ' · ' . $fileBlockMsg : '' ?>
              </div>
            </div>
            <?php if ($canAccessFiles): ?>
            <a href="/uploads/materials/<?= htmlspecialchars($f['file_path']) ?>"
              class="btn-file-dl" download>다운로드</a>
            <?php elseif (!$member): ?>
            <button class="btn-file-dl" onclick="requireLogin()">다운로드</button>
            <?php elseif ($isFree): ?>
            <button class="btn-file-dl" onclick="openFreeModal()" style="background:#27ae60">신청 후 이용</button>
            <?php else: ?>
            <button class="btn-file-dl" onclick="openPayModal()" style="background:#8e44ad">구매 후 이용</button>
            <?php endif; ?>
          </div>
          <?php else: ?>
          <div class="cd-file-row link">
            <div>
              <div class="cd-file-name"><?= htmlspecialchars($f['title']) ?></div>
              <div class="cd-file-meta">외부 링크<?= $fileBlockMsg ? ' · ' . $fileBlockMsg : '' ?></div>
            </div>
            <?php if ($canAccessFiles): ?>
            <a href="<?= htmlspecialchars($f['external_url']) ?>" target="_blank" rel="noopener"
              class="btn-file-link">열기</a>
            <?php elseif (!$member): ?>
            <button class="btn-file-dl" onclick="requireLogin()" style="background:#3b5bdb">열기</button>
            <?php elseif ($isFree): ?>
            <button class="btn-file-dl" onclick="openFreeModal()" style="background:#27ae60">신청 후 이용</button>
            <?php else: ?>
            <button class="btn-file-dl" onclick="openPayModal()" style="background:#8e44ad">구매 후 이용</button>
            <?php endif; ?>
          </div>
          <?php endif; ?>
          <?php endforeach; ?>
          </div><!-- /.cd-files-body -->
        </div>
        <?php endif; ?>

        <?php /* ─────── 버튼 상태별 분기 ─────── */ ?>

        <?php if ($btnStatus === 'login_required'): ?>
        <?php /* CASE 1 — 비로그인 */ ?>        
        <div class="cd-box-btn">
          <button class="btn-apply <?= $isFree ? 'free-color' : '' ?>"
                  onclick="requireLogin()">
            <?= $isFree ? '무료강의 신청하기' : '결제하기' ?>
          </button>
        </div>
        <div class="state-box not_login">
          ⚠ 로그인 후 이용 가능합니다.<br>          
          <a href="/login">로그인하러 가기 →</a>
        </div>

        <?php elseif ($btnStatus === 'enrolled' && $isFree): ?>
        <?php /* CASE 2 — 무료 신청 완료 */ ?>
        <div class="cd-box-btn">
          <button class="btn-apply free-color" disabled>신청 완료</button>
        </div>
        <div class="state-box success">
          ✅ 이미 신청하셨습니다.<br>
          강의 당일 카카오톡으로 링크가 전송됩니다.
        </div>
        <?php if (!empty($enroll['kakao_url'])): ?>
        <!-- <div style="padding:0 16px 14px">
          <a href="<?= htmlspecialchars($enroll['kakao_url']) ?>"
            onclick="logOpenchat()" target="_blank" rel="noopener"
            class="btn-kakao">💬 카카오 오픈채팅 입장하기</a>
        </div> -->
        <?php endif; ?>

        <?php elseif ($btnStatus === 'enrolled' && $isPremium): ?>
        <?php /* CASE 3 — 유료 구매 완료 */ ?>
        <div class="cd-box-btn">
          <!-- <a href="/classes/<?= $class['class_idx'] ?>/learn" class="btn-go-learn">▶ 수강하러 가기</a> -->
          <button class="btn-apply" disabled>구매 완료</button>
        </div>
        <div class="state-box success">
          ✅ 이미 구매하셨습니다.<br>
          마이페이지 > 나의 강의에서 수강할 수 있습니다.
        </div>

        <?php elseif ($btnStatus === 'closed'): ?>
        <?php /* CASE 5 — 마감 */ ?>
        <div class="cd-box-btn">
          <button class="btn-apply" disabled><?= $isFree ? '신청 마감' : '판매 종료' ?></button>
        </div>
        <div class="state-box error2">
          ⚠ <?= $isFree ? '신청이 마감' : '판매가 종료' ?>되었습니다.<br>
          다음 강의 일정을 확인해 보세요.
          <!-- <a href="/classes">다른 강의 보러 가기 →</a> -->
        </div>

        <?php elseif ($btnStatus === 'waiting'): ?>
        <?php /* CASE 6 — 신청 대기 */ ?>
        <div class="cd-box-btn">
          <button class="btn-apply" disabled>신청 대기 중</button>
        </div>
        <div class="state-box error2">
          ⚠ 아직 신청 가능 기간이 아닙니다.<?php if ($enrollStartFmt): ?><br>
          <span class="waiting-date">판매 시작일 : <?= htmlspecialchars($enrollStartFmt) ?></span><?php endif; ?>
        </div>

        <?php else: ?>
        <?php /* CASE 7 / 정상 — 신청/결제 가능 */ ?>
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
</div>

<?php /* ════════════════════════════════════════
  무료강의 신청 모달 (3단계)
  — $isFree && $btnStatus === 'apply' 일 때만 생성
════════════════════════════════════════ */ ?>
<?php if ($isFree && $btnStatus === 'apply'): ?>
<div class="refund-modal" id="freeModal">
  <div class="refund-modal-wrap">
    <button onclick="closeFreeModal()" class="refund-modal-x"></button>
    <div class="refund-modal-title">무료강의 신청</div>

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
      <div class="refund-modal-scroll">
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
      </div>
      <div class="pay-nav">
        <button class="pay-nav-btn prev" onclick="closeFreeModal()">취소</button>
        <button class="pay-nav-btn free-btn" onclick="goFreeStep(2)">다음</button>
      </div>
    </div>        

    <?php /* STEP 2: 동의 */ ?>
    <div class="step-panel" id="fp-2">
      <div class="refund-modal-scroll">
        <div style="background:#fff9f0;border:1px solid #ffe0b2;border-radius:6px;padding:12px 14px;font-size:12px;color:#7a4a00;line-height:1.7;margin-bottom:14px;">
          무료강의를 신청하면 아래 항목에 동의하는 것으로 간주합니다.
        </div>
        <label class="agree-all"><input type="checkbox" id="f-chk-all" onchange="toggleFreeAll(this)"> 아래 항목에 모두 동의합니다</label>
        <label class="agree-item"><input type="checkbox" class="f-chk-item"> [필수] 개인정보 수집 및 이용 동의<a href="#" onclick="event.preventDefault();openTermsPopup('/supports/privacy','개인정보 수집 및 이용 동의')">보기</a></label>
        <label class="agree-item"><input type="checkbox" class="f-chk-item"> [필수] 마케팅 메시지 수신 동의 (카카오·문자·이메일)<a href="#" onclick="event.preventDefault();openTermsPopup('/supports/policy/marketing','마케팅 메시지 수신 동의')">보기</a></label>
        <label class="agree-item"><input type="checkbox" class="f-chk-item"> [필수] 면책조항 확인<a href="#" onclick="event.preventDefault();openTermsPopup('/supports/policy/disclaimer','면책조항')">보기</a></label>
        <div id="f-agree-error" class="state-box error" style="display:none;">
          필수 항목에 모두 동의해 주세요.
        </div>
      </div>
      <div class="pay-nav">
        <button class="pay-nav-btn prev" onclick="goFreeStep(1)">이전</button>
        <button class="pay-nav-btn free-btn" onclick="submitFreeEnroll()">신청하기</button>
      </div>
    </div>

    <?php /* STEP 3: 완료 (JS에서 채워넣음) */ ?>
    <div class="step-panel" id="fp-3">
      <div class="refund-modal-scroll">
        <div class="pay-loading show" id="freeLoading">
          <div class="spinner"></div>
          <div style="font-size:13px;color:#888">신청 처리 중...</div>
        </div>
        <div class="pay-result" id="freeSuccess" style="display:none">          
          <div class="pay-result-title">신청이 완료되었습니다!</div>
          <div class="pay-result-desc">
            신청해 주셔서 감사합니다.<br>
            오픈채팅방에 입장하시면 강의 당일<br>
            알림을 받으실 수 있습니다.
          </div>
          <div id="freeResultKakao"></div>
          <div class="pay-link-note">링크는 마이페이지 &gt; 나의 강의에서도 확인 가능합니다</div>
          <div class="pay-nav">
            <button class="pay-nav-btn prev" onclick="location.href='/'">메인으로</button>
            <button class="pay-nav-btn green-btn" onclick="location.href='/mypage/my-class'">나의 강의</button>
          </div>
        </div>
      </div>
      <div class="pay-result" id="freeError" style="display:none">        
        <div class="pay-result-title">신청에 실패했습니다</div>
        <div class="pay-result-desc" id="freeErrorMsg"></div>
        <div class="pay-nav">
          <button class="pay-nav-btn prev" onclick="closeFreeModal()">닫기</button>
          <button class="pay-nav-btn free-btn" onclick="goFreeStep(2)">다시 시도</button>
        </div>
      </div>
    </div>
  </div>
</div><!-- /freeModal -->
<?php endif; ?>

<?php /* ════════════════════════════════════════
  유료 결제 모달 (5단계)
  — $isPremium && $btnStatus === 'apply' 일 때만 생성
════════════════════════════════════════ */ ?>
<?php if ($isPremium && $btnStatus === 'apply'): ?>
<div class="refund-modal" id="payModal">
  <div class="refund-modal-wrap">
    <button onclick="closePayModal()" class="refund-modal-x"></button>
    <div class="refund-modal-title">결제하기</div>

    <?php /* 스텝 인디케이터 */ ?>
    <div class="pay-steps ver2">
      <div class="pay-step active" id="ps-ind-1"><div class="pay-step-circle">1</div><div class="pay-step-label">주문 확인</div><div class="pay-step-line"></div></div>
      <div class="pay-step" id="ps-ind-2"><div class="pay-step-circle">2</div><div class="pay-step-label">결제수단</div><div class="pay-step-line"></div></div>
      <div class="pay-step" id="ps-ind-3"><div class="pay-step-circle">3</div><div class="pay-step-label">정보 입력</div><div class="pay-step-line"></div></div>
      <div class="pay-step" id="ps-ind-4"><div class="pay-step-circle">4</div><div class="pay-step-label">약관 동의</div><div class="pay-step-line"></div></div>
      <div class="pay-step" id="ps-ind-5"><div class="pay-step-circle">5</div><div class="pay-step-label">완료</div></div>
    </div>          

    <?php /* STEP 1: 주문 확인 */ ?>
    <div class="step-panel active" id="pp-1">
      <div class="refund-modal-scroll">
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
          <span class="val" style="color:#27ae60">-<?= number_format($class['price_origin'] - $effectivePrice) ?>원</span>
        </div>
        <?php endif; ?>
        <div class="order-row total">
          <span class="lbl" style="font-weight:700">최종 결제금액</span>
          <span class="val"><?= number_format($effectivePrice) ?>원</span>
        </div>
       </div>
      <div class="pay-nav">
        <button class="pay-nav-btn prev" onclick="closePayModal()">취소</button>
        <button class="pay-nav-btn next" onclick="goPayStep(2)">다음</button>
      </div>
    </div>

    <?php /* STEP 2: 결제 수단 */ ?>
    <div class="step-panel" id="pp-2">
      <div class="refund-modal-scroll">
        <div class="pg-notice">
          PG사 미정 — 실제 연동 시 PG사 SDK 팝업 또는 인라인 결제창으로 대체될 예정입니다.<br>      
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
      </div>
      <div class="pay-nav">
        <button class="pay-nav-btn prev" onclick="goPayStep(1)">이전</button>
        <button class="pay-nav-btn next" onclick="goPayStep(3)">다음</button>
      </div>
    </div>

    <?php /* STEP 3: 정보 입력 */ ?>
    <div class="step-panel" id="pp-3">
      <div class="refund-modal-scroll">
        <!-- <div class="pay-autofill-note">회원 정보에서 자동 불러왔습니다. 수정 시 이 주문에만 적용됩니다.</div> -->
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
        <div class="pay-card-section mgt12">
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
      </div>
      <div class="pay-nav">
        <button class="pay-nav-btn prev" onclick="goPayStep(2)">이전</button>
        <button class="pay-nav-btn next" onclick="validateInfoAndNext()">다음</button>
      </div>
    </div>

    <?php /* STEP 4: 약관 동의 */ ?>
    <div class="step-panel" id="pp-4">
      <div class="refund-modal-scroll">
        <div class="final-amount-row">
          <span class="lbl">최종 결제금액</span>
          <span class="val"><?= number_format($effectivePrice) ?>원</span>
        </div>
        <label class="agree-all"><input type="checkbox" id="p-chk-all" onchange="togglePayAll(this)"> 아래 약관에 모두 동의합니다</label>
        <label class="agree-item"><input type="checkbox" class="p-chk-item"> [필수] 구매조건 확인 및 결제 진행 동의<a href="#" onclick="event.preventDefault();openTermsPopup('/supports/policy/purchase','구매조건 확인 및 결제 진행 동의')">보기</a></label>
        <label class="agree-item"><input type="checkbox" class="p-chk-item"> [필수] 개인정보 제3자 제공 동의 (PG사)<a href="#" onclick="event.preventDefault();openTermsPopup('/supports/policy/privacy_third','개인정보 제3자 제공 동의 (PG사)')">보기</a></label>
        <label class="agree-item"><input type="checkbox" class="p-chk-item"> [필수] 전자금융거래 이용약관<a href="#" onclick="event.preventDefault();openTermsPopup('/supports/policy/ecommerce','전자금융거래 이용약관')">보기</a></label>
        <label class="agree-item"><input type="checkbox" class="p-chk-item optional"> [선택] 마케팅 정보 수신 동의<a href="#" onclick="event.preventDefault();openTermsPopup('/supports/policy/marketing','마케팅 정보 수신 동의')">보기</a></label>
        <div id="p-agree-error" class="state-box error" style="display:none;margin:10px 0 0">
          <span class="state-icon">⚠</span><div>필수 약관에 모두 동의해 주세요.</div>
        </div>
      </div>
      <div class="pay-nav">
        <button class="pay-nav-btn prev" onclick="goPayStep(3)">이전</button>
        <button class="pay-nav-btn next" onclick="submitPayment()">결제하기</button>
        <!-- <?= number_format($effectivePrice) ?>원  -->
      </div>
      <div class="pay-inline-error" id="payInlineError" style="display:none"></div>
    </div>

    <?php /* STEP 5: 완료/실패 */ ?>
    <div class="step-panel" id="pp-5">
      <div class="refund-modal-scroll">
        <div class="pay-loading show" id="payLoading">
          <div class="spinner"></div>
          <div style="font-size:13px;color:#888">결제 처리 중...</div>
        </div>
        <div class="pay-result" id="paySuccess" style="display:none">          
          <div class="pay-result-title">결제가 완료되었습니다!</div>
          <div class="pay-result-desc">강의 수강 정보가 가입된 이메일로 발송되었습니다.</div>
          <div class="pay-result-detail" id="payResultDetail"></div>
          <div id="payResultLinks"></div>
          <div class="pay-link-note">위 링크는 마이페이지 &gt; 나의 강의에서도 확인 가능합니다</div>
          <div class="pay-nav">
            <button class="pay-nav-btn prev" onclick="location.href='/'">메인으로</button>
            <button class="pay-nav-btn next" onclick="location.href='/mypage/orders'">결제 내역</button>
          </div>
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
  </div>
</div><!-- /payModal -->
<?php endif; ?>

<?php /* ── 약관 팝업 ── */ ?>
<div class="terms-popup-overlay" id="termsPopup">
  <div class="terms-popup-sheet">
    <div class="terms-popup-head">
      <div class="terms-popup-title" id="termsPopupTitle"></div>
      <button class="terms-popup-close" onclick="closeTermsPopup()"></button>
    </div>
    <div class="terms-popup-body" id="termsPopupBody"></div>
  </div>
</div>

<script>
/* ───────────────────────────────────────────────────
   기본 설정값 (PHP에서 주입)
─────────────────────────────────────────────────── */
const CLASS_IDX   = <?= (int) $class['class_idx'] ?>;
const CSRF_TOKEN  = '<?= htmlspecialchars($csrfToken) ?>';
const IS_LOGGED_IN= <?= $member ? 'true' : 'false' ?>;
const IS_FREE     = <?= $isFree ? 'true' : 'false' ?>;
const RETURN_URL  = '/classes/' + CLASS_IDX;

<?php if ($saleEndTs && $btnStatus === 'apply'): ?>
/* ── 카운트다운 타이머 ── */
const TARGET_TS   = <?= $saleEndTs ?> * 1000;
const EXPIRE_LABEL = IS_FREE ? '신청 마감' : '판매 종료';
(function tick() {
  const diff = TARGET_TS - Date.now();
  if (diff <= 0) { document.getElementById('countdown').textContent = EXPIRE_LABEL; return; }
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

  const section = document.getElementById(sectionId);
  const headerH = document.getElementById('site-header')?.offsetHeight ?? 70;
  const tabsH   = document.querySelector('.cd-tabs')?.offsetHeight ?? 70;
  const top     = section.getBoundingClientRect().top + window.scrollY - headerH - tabsH;
  window.scrollTo({ top, behavior: 'smooth' });
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

/* ── 할부 안내 팝오버 ── */
function toggleInstallPopover(btn) {
  const pop = btn.nextElementSibling;
  const isOpen = pop.classList.contains('open');
  closeInstallPopover();
  if (!isOpen) pop.classList.add('open');
}
function closeInstallPopover() {
  document.querySelectorAll('.cd-install-popover.open').forEach(p => p.classList.remove('open'));
}
document.addEventListener('click', function(e) {
  if (!e.target.closest('.cd-install-wrap')) closeInstallPopover();
});

/* ── 강의 자료 토글 ── */
function toggleFiles(btn) {
  const body = btn.nextElementSibling;
  const arrow = btn.querySelector('.cd-files-arrow');
  const open  = body.style.display === 'none';
  body.style.display  = open ? '' : 'none';
  arrow.style.transform = open ? 'rotate(180deg)' : '';
  btn.setAttribute('aria-expanded', open);
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
      //icon.textContent = '❤️';
      icon.src = '/assets/img/icon_like_on.svg';
      text.textContent = ' 찜완료';
    } else {
      btn.classList.remove('wished');
      //icon.textContent = '🤍';
      icon.src = '/assets/img/icon_like_off.svg';
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
  //document.body.style.overflow = 'hidden';
}
function closePayModal() {
  document.getElementById('payModal').classList.remove('open');
  //document.body.style.overflow = '';
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

  document.getElementById('payInlineError').style.display = 'none';
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
      if (data.learn_url) {
        links += '<a href="' + escHtml(data.learn_url) + '" class="btn-vimeo">▶ 강의 수강하기</a>';
      } else if (data.vimeo_url) {
        links += '<a href="' + escHtml(data.vimeo_url) + '" target="_blank" class="btn-vimeo">▶ 비메오 영상 보기</a>';
      }
      document.getElementById('payResultLinks').innerHTML = links;
      document.getElementById('paySuccess').style.display = 'block';
    } else {
      goPayStep(4);
      const errEl = document.getElementById('payInlineError');
      errEl.textContent = '❌ ' + (data.error || '결제에 실패했습니다. 카드 정보를 확인하거나 다른 수단을 이용해 주세요.');
      errEl.style.display = 'block';
      errEl.scrollIntoView({behavior:'smooth', block:'nearest'});
    }
  })
  .catch(() => {
    goPayStep(4);
    const errEl = document.getElementById('payInlineError');
    errEl.textContent = '❌ 네트워크 오류가 발생했습니다. 다시 시도해 주세요.';
    errEl.style.display = 'block';
    errEl.scrollIntoView({behavior:'smooth', block:'nearest'});
  });
}
<?php endif; ?>

/* ── 약관 팝업 ── */
function openTermsPopup(url, title) {
  const body = document.getElementById('termsPopupBody');
  document.getElementById('termsPopupTitle').textContent = title;
  body.innerHTML = '<div class="terms-popup-loading">불러오는 중...</div>';
  document.getElementById('termsPopup').classList.add('open');

  fetch(url + (url.includes('?') ? '&' : '?') + 'ajax=1')
    .then(r => { if (!r.ok) throw new Error(); return r.text(); })
    .then(html => { body.innerHTML = html; body.scrollTop = 0; })
    .catch(() => { body.innerHTML = '<div class="terms-popup-error">내용을 불러오지 못했습니다.</div>'; });
}
function closeTermsPopup() {
  document.getElementById('termsPopup').classList.remove('open');
}
document.getElementById('termsPopup').addEventListener('click', function(e) {
  if (e.target === this) closeTermsPopup();
});

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
