<?php
/**
 * 관리자 1:1 문의 목록
 * @var array  $contacts
 * @var int    $total
 * @var int    $page
 * @var int    $totalPages
 * @var array  $filters
 */
$catLabel = ['class' => '강의', 'payment' => '결제', 'account' => '계정', 'tech' => '기술', 'etc' => '기타'];
?>

<form method="GET" action="/admin/contacts" class="filter-bar">
	<input type="text" name="q" value="<?= htmlspecialchars($filters['q']) ?>" placeholder="회원ID·이름·제목 검색">
	<select name="status">
		<option value="">전체 상태</option>
		<option value="wait" <?= $filters['status'] === 'wait' ? 'selected' : '' ?>>미답변</option>
		<option value="done" <?= $filters['status'] === 'done' ? 'selected' : '' ?>>답변완료</option>
	</select>
	<select name="category">
		<option value="">전체 분류</option>
		<?php foreach ($catLabel as $k => $v): ?>
		<option value="<?= $k ?>" <?= $filters['category'] === $k ? 'selected' : '' ?>><?= $v ?></option>
		<?php endforeach; ?>
	</select>
	<button type="submit" class="btn-search">검색</button>
	<a href="/admin/contacts" class="btn-reset">초기화</a>
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
				<th>분류</th>
				<th class="text_left">제목</th>
				<th>회원</th>
				<th>상태</th>
				<th>접수일</th>
				<th>관리</th>
			</tr>
		</thead>
		<tbody>
		<?php if (empty($contacts)): ?>
			<tr class="empty-row"><td colspan="7">문의 내역이 없습니다.</td></tr>
		<?php else: ?>
			<?php foreach ($contacts as $_i => $c): ?>
			<tr>
				<td><?= $total - ($page-1)*$limit - $_i ?></td>
				<td><?= $catLabel[$c['category']] ?? $c['category'] ?></td>
				<td class="title-cell text_left"><?= htmlspecialchars($c['title']) ?></td>
				<td>
					<span style="font-weight:600;"><?= htmlspecialchars($c['mb_name']) ?></span>
					<div style="font-size:11.5px;color:#a0aec0;"><?= htmlspecialchars($c['mb_id']) ?></div>
				</td>
				<td>
					<span class="badge badge-<?= $c['status'] ?>">
						<?= $c['status'] === 'wait' ? '미답변' : '답변완료' ?>
					</span>
				</td>
				<td><?= date('Y-m-d', strtotime($c['created_at'])) ?></td>
				<td><a href="/admin/contacts/<?= $c['qna_idx'] ?>" class="act-btn act-view">보기</a></td>
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
			<a href="/admin/contacts?<?= $q ?>"><?= $i ?></a>
		<?php endif; ?>
	<?php endfor; ?>
</div>
<?php endif; ?>
<script>sessionStorage.setItem('back_contacts', location.href);</script>
