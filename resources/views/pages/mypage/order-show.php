<?php
/**
 * 결제내역 상세
 * 변수: $order, $canRefund, $csrfToken
 */

$statusLabel = [
    'paid'       => '결제완료',
    'refund_req' => '환불신청중',
    'refunded'   => '환불완료',
    'free'       => '수강신청완료',
];

$now       = new \DateTimeImmutable();

// null / zero-date 안전 헬퍼
function safeDtOrder(?string $val): ?\DateTimeImmutable {
    if (empty($val) || $val === '0000-00-00 00:00:00') return null;
    try { return new \DateTimeImmutable($val); } catch (\Throwable $e) { return null; }
}

$paidAtObj = safeDtOrder($order['paid_at'] ?? null);

// 진도율 계산
$rate = 0;
if ((int)$order['total_episodes'] > 0) {
    $rate = (int) round((int)$order['done_count'] / (int)$order['total_episodes'] * 100);
}

// 환불 계산 변수 초기화
$refundAmount = 0;
$refundType   = '';
$isFullRefund = false;
$totalDays    = 0;
$elapsedDays  = 0;
$remainDays   = 0;
$perDay       = 0;
$startDt      = null;
$expireDt     = null;

if ($canRefund && $paidAtObj && (int)$order['amount'] > 0) {
    // 수강 기간 정보는 전액/비례 공통으로 항상 조회
    $enrollRow = \App\Core\DB::selectOne(
        "SELECT enrolled_at, expire_at FROM lc_enroll
         WHERE member_idx = ? AND class_idx = ? LIMIT 1",
        [(int)$order['member_idx'], (int)$order['class_idx']]
    );
    if ($enrollRow && $enrollRow['expire_at']) {
        $startDt     = safeDtOrder($enrollRow['enrolled_at']) ?? $now;
        $expireDt    = safeDtOrder($enrollRow['expire_at'])   ?? $now;
        $totalDays   = (int) $startDt->diff($expireDt)->days;
        $elapsedDays = min((int) $startDt->diff($now)->days, $totalDays);
        $remainDays  = max(0, $totalDays - $elapsedDays);
        $perDay      = $totalDays > 0 ? (int)$order['amount'] / $totalDays : 0;
    }

    if ($rate < 33 && $paidAtObj->modify('+7 days') >= $now) {
        $refundAmount = (int)$order['amount'];
        $refundType   = '전액 환불';
        $isFullRefund = true;
    } else {
        $refundAmount = (int) round($perDay * $remainDays);
        $refundType   = '잔여기간 비례 환불';
        $isFullRefund = false;
    }
}

$isFree    = $order['status'] === 'free';
$isRefunded = $order['status'] === 'refunded';

$refundedMsg = '';
if (isset($_GET['refund'])) $refundedMsg = 'success';
if (isset($_GET['err']))    $refundedMsg = $_GET['err'];
?>

<div class="sub_index profile_area">
  <div class="inner">
    <?php require VIEW_PATH . '/components/mp-user-area.php'; ?>
    <div class="sub_page_flex">
      <?php require VIEW_PATH . '/components/mp-subnav.php'; ?>
      <div class="sub_page_contents">
        <div class="page-section-title">결제 내역 상세</div>

        <?php if ($refundedMsg === 'success'): ?>
        <div style="background:#edf7f0;border:1px solid #b2dfdb;border-radius:8px;padding:12px 16px;margin-bottom:16px;font-size:13px;color:#27ae60;font-weight:600;">
          환불 신청이 접수되었습니다. 관리자 검토 후 3~5 영업일 내 처리됩니다.
        </div>
        <?php elseif ($refundedMsg === 'reason'): ?>
        <div style="background:#fef0ee;border:1px solid #f5c6c6;border-radius:8px;padding:12px 16px;margin-bottom:16px;font-size:13px;color:#c0392b;">
          환불 사유를 선택해주세요.
        </div>
        <?php elseif ($refundedMsg === 'fail'): ?>
        <div style="background:#fef0ee;border:1px solid #f5c6c6;border-radius:8px;padding:12px 16px;margin-bottom:16px;font-size:13px;color:#c0392b;">
          환불 신청에 실패했습니다. 이미 처리된 주문이거나 환불 불가 상태입니다.
        </div>
        <?php endif; ?>

        <!-- 강의 정보 -->
        <div class="apply-section">
          <div class="apply-section-title">강의 정보</div>
          <div class="order-item-body">
            <div class="order-thumb">
              <?php if ($order['thumbnail']): ?>
                <img src="/uploads/class/<?= htmlspecialchars($order['thumbnail']) ?>" alt="" class="real_img">
              <?php else: ?>
                <img src="/assets/img/logo.svg" alt="" class="none_img">
              <?php endif; ?>

              <p class="class_badge"><img src="/assets/img/<?=$order['class_type']?>_badge.png" alt=""></p>
            </div>
            <div class="order-info">
              <div class="order-class-type <?= $order['class_type'] === 'free' ? 'order-class-free' : 'order-class-premium' ?>">                  
                <?= $order['class_type'] === 'free' ? '무료' : '프리미엄' ?>                  
              </div>
              <div class="order-class-title"><?= htmlspecialchars($order['class_title']) ?></div>                
              <p class="order-class-inst">
                <?= htmlspecialchars($order['instructor_name'] ?? '') ?>
                <?php if (!empty($order['category_name'])): ?>
                · <?= htmlspecialchars($order['category_name']) ?>
                <?php endif; ?>
              </p>
              <!-- 상태: <strong><?= $statusLabel[$order['status']] ?? $order['status'] ?></strong> -->
            </div>              
          </div>
        </div>

        <!-- 결제 정보 -->
        <div class="apply-section">
          <div class="apply-section-title">결제 정보</div>
          <div class="detail-row">
            <span class="detail-label">주문번호</span>
            <span class="detail-value">
              <?= $order['toss_order_id'] ? htmlspecialchars($order['toss_order_id']) : 'ORD-' . str_pad((string)$order['order_idx'], 8, '0', STR_PAD_LEFT) ?>
            </span>
          </div>
          <div class="detail-row">
            <span class="detail-label"><?= $isFree ? '신청일시' : '결제일시' ?></span>
            <span class="detail-value">
              <?= $paidAtObj ? $paidAtObj->format('Y.m.d H:i') : '-' ?>
            </span>
          </div>
          <?php if (!$isFree): ?>
          <div class="detail-row">
            <span class="detail-label">결제수단</span>
            <span class="detail-value">
              <?php
                $method  = $order['pay_method']   ?? null;
                $company = $order['card_company']  ?? null;
                $type    = $order['card_type']     ?? null;
                $number  = $order['card_number']   ?? null;
                $install = (int) ($order['card_install'] ?? 0);

                if ($method === '카드' && $company) {
                    // 카드 종류: "신용" → "신용카드", "체크" → "체크카드", 없으면 그냥 "카드"
                    $typeLabel = $type ? htmlspecialchars($type) . '카드' : '카드';
                    echo $typeLabel;

                    // "(신한카드 1234)" — 마스킹 번호 끝 4자리 추출
                    $last4 = $number ? preg_replace('/.*(\d{4})$/', '$1', str_replace(['-', ' '], '', $number)) : '';
                    echo ' (' . htmlspecialchars($company) . '카드' . ($last4 ? ' ' . $last4 : '') . ')';

                    echo $install > 0 ? ' · ' . $install . '개월 할부' : ' · 일시불';
                } elseif ($method) {
                    echo htmlspecialchars($method);
                } else {
                    echo '토스페이먼츠';
                }
              ?>
            </span>
          </div>
          <?php endif; ?>
          <?php if (!$isFree): ?>
          <div class="detail-row">
            <span class="detail-label">정가</span>
            <span class="detail-value"><?= number_format((int)$order['amount_origin']) ?>원</span>
          </div>
          <?php
            $amountOrigin   = (int)$order['amount_origin'];
            $amountPaid     = (int)$order['amount'];
            $discountAmount = $amountOrigin - $amountPaid;
            $discountRate   = $amountOrigin > 0 ? round($discountAmount / $amountOrigin * 100) : 0;
          ?>
          <?php if ($discountAmount > 0): ?>
          <div class="detail-row">
            <span class="detail-label">할인금액</span>
            <span class="detail-value detail-discount">
              -<?= number_format($discountAmount) ?>원 (<?= $discountRate ?>%)
            </span>
          </div>
          <?php endif; ?>
          <div class="detail-row">
            <span class="detail-label">결제금액</span>
            <span class="detail-value detail-amount"><?= number_format((int)$order['amount']) ?>원</span>
          </div>
          <?php else: ?>
          <div class="detail-row">
            <span class="detail-label">결제금액</span>
            <span class="detail-value">무료</span>
          </div>
          <?php endif; ?>
          <?php if ($order['status'] === 'refunded' && $order['refunded_at']): ?>
          <div class="detail-row">
            <span class="detail-label">환불일시</span>
            <span class="detail-value">
              <?= (safeDtOrder($order['refunded_at']) ?? $now)->format('Y.m.d H:i') ?>
            </span>
          </div>
          <?php endif; ?>
          <?php if ($order['refund_reason']): ?>
          <div class="detail-row">
            <span class="detail-label">환불사유</span>
            <span class="detail-value"><?= htmlspecialchars($order['refund_reason']) ?></span>
          </div>
          <?php endif; ?>
        </div>

        <!-- 환불 안내 + 신청 -->
        <?php if ($canRefund): ?>
        <div class="apply-section">
          <div class="apply-section-title"><?= $isFullRefund ? '전액 환불 안내' : '비례환불 계산' ?></div>
          <ul class="refund-policy-box">
            <li>결제일로부터 7일 이내 &amp; 수강률 33% 미만 &rarr; 전액 환불</li>
            <li>이후 또는 수강률 33% 이상 &rarr; 잔여 수강기간 비례 환불</li>
            <li>현재 수강률: <strong><?= $rate ?>%</strong>
              &middot; <?= $isFullRefund ? '<span style="color:#27ae60;font-weight:700">전액 환불 가능</span>' : '<span style="color:#888">잔여 기간 비례 환불 적용</span>' ?></li>
          </ul>

          <div class="refund_ex">
            <p class="refund_ex_title">📊 <?= $isFullRefund ? '전액 환불' : '비례환불 계산' ?> (실제 데이터 기준)</p>
            <div class="refund_ex_box">
              <p class="refund_ex_title">조건</p>
              <p class="refund_ex_txt">: 결제금액 <?= number_format((int)$order['amount']) ?>원
                <?php if (!$isFullRefund && $totalDays > 0): ?>
                &middot; 수강기간 <?= $totalDays ?>일
                &middot; 수강 시작일 <?= $startDt ? $startDt->format('Y.m.d') : '-' ?>
                &middot; 환불 신청일 <?= $now->format('Y.m.d') ?>
                <?php endif; ?>
              </p>
            </div>
            <?php if ($isFullRefund): ?>
            <ul class="refund_ex_calc">
              <li>
                <span>환불 금액</span>
                <strong><b><?= number_format($refundAmount) ?>원 (전액)</b></strong>
              </li>
            </ul>
            <?php elseif ($totalDays > 0): ?>
            <ul class="refund_ex_calc">
              <li>
                <span>전체 수강기간</span>
                <strong><?= $totalDays ?>일</strong>
              </li>
              <li>
                <span>경과 수강일</span>
                <strong><?= $elapsedDays ?>일 (<?= $startDt ? $startDt->format('n.j') : '' ?> ~ <?= $now->format('n.j') ?>)</strong>
              </li>
              <li>
                <span>잔여 수강일</span>
                <strong><?= $remainDays ?>일</strong>
              </li>
              <li>
                <span>1일당 금액</span>
                <strong><?= number_format((int)$order['amount']) ?> &divide; <?= $totalDays ?> = <?= number_format((int)$perDay) ?>원</strong>
              </li>
              <li>
                <span>환불 금액</span>
                <strong><b><?= number_format((int)$perDay) ?> &times; <?= $remainDays ?> = <?= number_format($refundAmount) ?>원</b></strong>
              </li>
            </ul>
            <?php endif; ?>
          </div>
        </div>
        
        <div class="profile_btn_box">
          <button class="btn-next btn-save-profile btn-error" onclick="document.getElementById('refund-modal').style.display='flex'">
            환불 신청하기
          </button>
        </div>
        
        <?php endif; ?>

      </div>
    </div>
  </div>
</div>

<!-- 환불 신청 모달 -->
<?php if ($canRefund): ?>
<div id="refund-modal" class="refund-modal">
  <div class="refund-modal-wrap">
    <button onclick="document.getElementById('refund-modal').style.display='none'" class="refund-modal-x"></button>
    
    <div class="refund-modal-title">환불 신청</div>
    <div class="refund-modal-desc"><?= htmlspecialchars($order['class_title']) ?></div>
    
    <form method="POST" action="/mypage/orders/<?= (int)$order['order_idx'] ?>/refund">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">

      <div class="refund-modal-scroll">
        <!-- 예상 환불금액 -->
        <div class="refund-modal-info-area">
          <div class="refund-modal-info">
            <span>결제 금액</span>
            <strong><?= number_format((int)$order['amount']) ?>원</strong>
          </div>
          <div class="refund-modal-info">
            <span>환불 유형</span>
            <strong><?= $refundType ?? '확인 중' ?></strong>
          </div>
          <?php if ($refundAmount > 0): ?>
          <div class="refund-modal-info ver2">
            <span>예상 환불 금액</span>
            <strong><?= number_format($refundAmount) ?>원</strong>
          </div>  
          <?php endif; ?>
        </div>
        
        <div class="field-block auth-input-group">
          <div class="field-label">환불 사유 <span class="required">*</span></div>
          <select name="reason" required>
            <option value="">사유를 선택해 주세요</option>
            <option>강의 내용이 기대와 달라요</option>
            <option>강의 품질 문제 (영상/음질)</option>
            <option>실수로 결제했어요</option>
            <option>개인 사정으로 수강이 어려워요</option>
            <option>기타</option>
          </select>          
        </div>

        <div class="field-block auth-input-group">
          <div class="field-label">상세 내용</div>
          <textarea name="detail" placeholder="추가 내용을 입력해 주세요"></textarea>   
        </div>

        <p class="refund-alert">환불 신청 후 관리자 검토를 거쳐 영업일 기준 3~5일 내 처리됩니다.</p>
      </div>
      <button type="submit" class="btn-next btn-error">환불 신청</button>
    </form>
  </div>
</div>
<?php endif; ?>
