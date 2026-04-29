<?php
/**
 * 관리자 결제 목록
 * @var array  $orders
 * @var int    $total
 * @var int    $page
 * @var int    $totalPages
 * @var array  $filters
 */

$statusLabel = fn(string $s) => match($s) {
  'paid'       => ['text' => '결제완료', 'cls' => 'badge-paid'],
  'refund_req' => ['text' => '환불신청', 'cls' => 'badge-refund-req'],
  'refunded'   => ['text' => '환불완료', 'cls' => 'badge-refunded'],
  'free'       => ['text' => '무료수강', 'cls' => 'badge-free'],
  default      => ['text' => $s,         'cls' => 'badge-default'],
};
?>

<form method="GET" action="/admin/orders" class="filter-bar">
  <input type="text" name="q" value="<?= htmlspecialchars($filters['q']) ?>" placeholder="회원ID·이름·이메일·강의명">
  <select name="status">
	<option value="">전체 상태</option>
	<option value="paid"       <?= $filters['status'] === 'paid'       ? 'selected' : '' ?>>결제완료</option>
	<option value="refund_req" <?= $filters['status'] === 'refund_req' ? 'selected' : '' ?>>환불신청</option>
	<option value="refunded"   <?= $filters['status'] === 'refunded'   ? 'selected' : '' ?>>환불완료</option>
	<option value="free"       <?= $filters['status'] === 'free'       ? 'selected' : '' ?>>무료수강</option>
  </select>
  <input type="date" name="date_from" value="<?= htmlspecialchars($filters['date_from']) ?>">
  <span style="font-size:12px;color:#aaa;align-self:center;">~</span>
  <input type="date" name="date_to" value="<?= htmlspecialchars($filters['date_to']) ?>">
  <button type="submit" class="btn-search">검색</button>
  <a href="/admin/orders" class="btn-reset">초기화</a>
</form>

<div class="top-bar">
  <div class="total-label">전체 <strong><?= number_format($total) ?></strong>건</div>
</div>

<div class="tbl-wrap">
  <colgroup>
		<col width="5%">
		<col width="10%">
		<col width="">
		<col width="10%">           
		<col width="7%">
		<col width="10%">
		<col width="10%">
	</colgroup>
  <table class="data-table">
	<thead>
	  <tr>
		<th>NO</th>
		<th>회원</th>
		<th class="text_left">강의</th>
		<th>결제금액</th>
		<th>상태</th>
		<th>결제일시</th>
		<th>관리</th>
	  </tr>
	</thead>
	<tbody>
	  <?php if (empty($orders)): ?>
		<tr class="empty-row"><td colspan="7">결제 내역이 없습니다.</td></tr>
	  <?php else: ?>
		<?php foreach ($orders as $_i => $o): $sl = $statusLabel($o['status']); ?>
		<tr>
		  <td><?= $total - ($page-1)*$limit - $_i ?></td>
		  <td>
			<a href="/admin/members/<?= $o['member_idx'] ?>" style="color:#5E81F4;font-weight:600;"><?= htmlspecialchars($o['mb_name']) ?></a>
			<div style="font-size:12px;color:#AEB9E1;"><?= htmlspecialchars($o['mb_id']) ?></div>
		  </td>
		  <td style="max-width:220px;" class="text_left">
			<div style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?= htmlspecialchars($o['class_title']) ?></div>
		  </td>
		  <td style="font-weight:600;"><?= number_format($o['amount']) ?>원</td>
		  <td><span class="badge <?= $sl['cls'] ?>"><?= $sl['text'] ?></span></td>
		  <td>
			<?= $o['paid_at'] ? date('Y-m-d H:i', strtotime($o['paid_at'])) : '-' ?>
		  </td>        
		  <td><a href="/admin/orders/<?= $o['order_idx'] ?>" class="act-btn act-view">보기</a></td>
		</tr>
		<?php endforeach; ?>
	  <?php endif; ?>
	</tbody>
  </table>
</div>

<?php if ($totalPages > 1): ?>
<div class="pagination">
  <?php for ($i = 1; $i <= $totalPages; $i++): ?>
	<?php $q = http_build_query(array_merge($filters, ['page' => $i])); ?>
	<?php if ($i === $page): ?>
	  <span class="active"><?= $i ?></span>
	<?php else: ?>
	  <a href="/admin/orders?<?= $q ?>"><?= $i ?></a>
	<?php endif; ?>
  <?php endfor; ?>
</div>
<?php endif; ?>
<script>sessionStorage.setItem('back_orders', location.href);</script>
