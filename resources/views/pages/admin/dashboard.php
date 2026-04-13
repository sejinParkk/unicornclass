<?php
/**
 * 관리자 대시보드
 *
 * 변수 (컨트롤러에서 주입):
 * @var array $stats  {
 *   today_members: int, today_orders: int, today_revenue: int,
 *   total_enrolls: int, pending_contacts: int, pending_applies: int
 * }
 * @var array $recentOrders  최근 결제 5건
 * @var array $recentMembers 최근 가입 5명
 */

$pageTitle  = '대시보드';
$pageDesc   = '오늘의 주요 지표와 최근 활동을 확인하세요.';
$activeMenu = 'dashboard';

// 더미 데이터 (컨트롤러 연결 전 UI 확인용)
$stats ??= [
	'today_members'   => 12,
	'today_orders'    => 8,
	'today_revenue'   => 1_584_000,
	'total_enrolls'   => 347,
	'pending_contacts'=> 5,
	'pending_applies' => 2,
];
$recentOrders ??= [];
$recentMembers ??= [];

ob_start();
?>

<!-- Quick Actions -->
<div class="quick-actions">
	<a href="/admin/classes/create" class="quick-btn">
		<svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
			<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
		</svg>
		강의 등록
	</a>
	<a href="/admin/members" class="quick-btn">
		<svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
			<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
				  d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
		</svg>
		회원 조회
	</a>
	<a href="/admin/orders?status=refund_req" class="quick-btn">
		<svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
			<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
				  d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
		</svg>
		환불 대기
	</a>
	<a href="/admin/contacts?status=wait" class="quick-btn">
		<svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
			<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
				  d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
		</svg>
		미답변 문의
		<?php if ($stats['pending_contacts'] > 0): ?>
			<span style="background:#c0392b;color:#fff;border-radius:10px;padding:1px 6px;font-size:11px;font-weight:700;">
				<?= $stats['pending_contacts'] ?>
			</span>
		<?php endif; ?>
	</a>
</div>

<!-- Stats Grid -->
<div class="stats-grid ver2">
	<div class="stat-card">
		<div class="stat-icon blue">
			<svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
				<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
					  d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
			</svg>
		</div>
		<div class="stat-body">
			<div class="stat-label">오늘 신규 가입</div>
			<div class="stat-value"><?= number_format($stats['today_members']) ?></div>
		</div>
	</div>

	<div class="stat-card">
		<div class="stat-icon green">
			<svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
				<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
			</svg>
		</div>
		<div class="stat-body">
			<div class="stat-label">오늘 결제</div>
			<div class="stat-value"><?= number_format($stats['today_orders']) ?>건</div>
		</div>
	</div>

	<div class="stat-card">
		<div class="stat-icon red">
			<svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
				<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
					  d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
			</svg>
		</div>
		<div class="stat-body">
			<div class="stat-label">오늘 매출</div>
			<div class="stat-value">₩<?= number_format($stats['today_revenue']) ?></div>
		</div>
	</div>

	<div class="stat-card">
		<div class="stat-icon purple">
			<svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
				<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
					  d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
			</svg>
		</div>
		<div class="stat-body">
			<div class="stat-label">전체 수강자</div>
			<div class="stat-value"><?= number_format($stats['total_enrolls']) ?></div>
		</div>
	</div>

	<div class="stat-card">
		<div class="stat-icon orange">
			<svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
				<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
					  d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
			</svg>
		</div>
		<div class="stat-body">
			<div class="stat-label">미답변 문의</div>
			<div class="stat-value"><?= number_format($stats['pending_contacts']) ?></div>
			<?php if ($stats['pending_contacts'] > 0): ?>
				<span class="stat-badge">처리 필요</span>
			<?php endif; ?>
		</div>
	</div>

	<div class="stat-card">
		<div class="stat-icon teal">
			<svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
				<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
					  d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
			</svg>
		</div>
		<div class="stat-body">
			<div class="stat-label">강사 지원 대기</div>
			<div class="stat-value"><?= number_format($stats['pending_applies']) ?></div>
			<?php if ($stats['pending_applies'] > 0): ?>
				<span class="stat-badge">검토 필요</span>
			<?php endif; ?>
		</div>
	</div>
</div>

<!-- Bottom Row -->
<div class="dashboard-row">
	<!-- 최근 결제 -->
	<div class="table-card">
		<div class="table-card-header">
			<h3 class="table-card-title">최근 결제</h3>
			<a href="/admin/orders" class="table-link">전체 보기</a>
		</div>
		<?php if (empty($recentOrders)): ?>
			<div class="empty-state">최근 결제 내역이 없습니다.</div>
		<?php else: ?>
		<table class="data-table">
			<thead>
				<tr>
					<th>회원</th>
					<th>강의</th>
					<th>금액</th>
					<th>상태</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($recentOrders as $order): ?>
				<tr>
					<td><?= htmlspecialchars($order['mb_id']) ?></td>
					<td style="max-width:120px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
						<?= htmlspecialchars($order['class_title']) ?>
					</td>
					<td>₩<?= number_format($order['amount']) ?></td>
					<td>
						<?php
						$statusMap = [
							'paid'        => ['badge-paid',    '결제완료'],
							'pending'     => ['badge-pending', '대기중'],
							'refund_req'  => ['badge-refund',  '환불요청'],
							'refunded'    => ['badge-refund',  '환불완료'],
						];
						[$cls, $label] = $statusMap[$order['payment_status']] ?? ['badge-pending', $order['payment_status']];
						?>
						<span class="status-badge <?= $cls ?>"><?= $label ?></span>
					</td>
				</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		<?php endif; ?>
	</div>

	<!-- 최근 가입 -->
	<div class="table-card">
		<div class="table-card-header">
			<h3 class="table-card-title">최근 가입 회원</h3>
			<a href="/admin/members" class="table-link">전체 보기</a>
		</div>
		<?php if (empty($recentMembers)): ?>
			<div class="empty-state">최근 가입 회원이 없습니다.</div>
		<?php else: ?>
		<table class="data-table">
			<thead>
				<tr>
					<th>아이디</th>
					<th>이름</th>
					<th>가입유형</th>
					<th>가입일</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($recentMembers as $member): ?>
				<tr>
					<td><?= htmlspecialchars($member['mb_id']) ?></td>
					<td><?= htmlspecialchars($member['mb_name']) ?></td>
					<td>
						<?php
						$typeMap = [
							'email'  => '이메일',
							'kakao'  => '카카오',
							'naver'  => '네이버',
						];
						echo htmlspecialchars($typeMap[$member['signup_type']] ?? $member['signup_type']);
						?>
					</td>
					<td style="white-space:nowrap;font-size:12px;color:#8898aa;">
						<?= htmlspecialchars(date('m/d H:i', strtotime($member['created_at']))) ?>
					</td>
				</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		<?php endif; ?>
	</div>
</div>

<?php
$content = ob_get_clean();

// 레이아웃 렌더링
require VIEW_PATH . '/layout/admin.php';
