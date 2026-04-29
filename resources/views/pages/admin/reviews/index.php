<?php
/**
 * 관리자 후기 목록
 * @var array  $reviews
 * @var int    $total
 * @var int    $page
 * @var int    $totalPages
 * @var array  $filters
 * @var string $csrfToken
 */
?>

<?php if (isset($_GET['deleted'])): ?>
<div class="toast-msg toast-success">✓ 후기가 삭제되었습니다.</div>
<?php endif; ?>

<form method="GET" action="/admin/reviews" class="filter-bar">
	<input type="text" name="q" value="<?= htmlspecialchars($filters['q']) ?>" placeholder="제목·내용·회원명·강의명 검색">
	<select name="is_active">
		<option value="">노출 전체</option>
		<option value="1" <?= $filters['is_active'] === '1' ? 'selected' : '' ?>>노출</option>
		<option value="0" <?= $filters['is_active'] === '0' ? 'selected' : '' ?>>숨김</option>
	</select>
	<select name="rating">
		<option value="">별점 전체</option>
		<?php for ($i = 5; $i >= 1; $i--): ?>
		<option value="<?= $i ?>" <?= $filters['rating'] === (string)$i ? 'selected' : '' ?>><?= $i ?>점</option>
		<?php endfor; ?>
	</select>
	<button type="submit" class="btn-search">검색</button>
	<a href="/admin/reviews" class="btn-reset">초기화</a>
</form>

<div class="top-bar">
	<div class="total-label">전체 <strong><?= number_format($total) ?></strong>건</div>
</div>

<div class="tbl-wrap">
	<table class="data-table">
		<colgroup>
			<col width="5%">
			<col width="">
			<col width="18%">
			<col width="12%">
			<col width="6%">
			<col width="7%">
			<col width="10%">
			<col width="10%">
		</colgroup>
		<thead>
			<tr>
				<th>NO</th>
				<th class="text_left">제목 / 내용</th>
				<th class="text_left">강의명</th>
				<th>회원명</th>
				<th>별점</th>
				<th>노출</th>
				<th>작성일</th>
				<th>관리</th>
			</tr>
		</thead>
		<tbody>
		<?php if (empty($reviews)): ?>
			<tr class="empty-row"><td colspan="8">후기가 없습니다.</td></tr>
		<?php else: ?>
			<?php foreach ($reviews as $_i => $r): ?>
			<tr>
				<td><?= $total - ($page - 1) * 20 - $_i ?></td>
				<td class="text_left" style="max-width:260px;">
					<div style="font-weight:600;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
						<?= htmlspecialchars($r['title'] ?? '') ?>
					</div>
					<div style="font-size:12px;color:#888;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;margin-top:2px">
						<?= htmlspecialchars(mb_substr($r['content'], 0, 60)) ?>
					</div>
				</td>
				<td class="text_left" style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-size:13px">
					<?= htmlspecialchars($r['class_title']) ?>
				</td>
				<td><?= htmlspecialchars($r['member_name']) ?></td>
				<td><?= (int)$r['rating'] ?>점</td>
				<td>
					<span class="badge badge-<?= $r['is_active'] ? 'active' : 'inactive' ?>">
						<?= $r['is_active'] ? '노출' : '숨김' ?>
					</span>
				</td>
				<td><?= date('Y-m-d', strtotime($r['created_at'])) ?></td>
				<td>
					<div class="act-btn-wrap">
						<a href="/admin/reviews/<?= (int)$r['review_idx'] ?>" class="act-btn act-edit">상세</a>
					</div>
				</td>
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
			<a href="/admin/reviews?<?= $q ?>"><?= $i ?></a>
		<?php endif; ?>
	<?php endfor; ?>
</div>
<?php endif; ?>
