<?php
/**
 * 관리자 FAQ 목록
 * @var array  $faqs
 * @var array  $categories  [key => label]
 * @var string $category    현재 필터
 */
?>
<style>

</style>

<?php if (isset($_GET['saved'])): ?>
<div class="toast-msg toast-success">✓ 저장되었습니다.</div>
<?php endif; ?>
<?php if (isset($_GET['deleted'])): ?>
<div class="toast-msg toast-success">✓ 삭제되었습니다.</div>
<?php endif; ?>
<?php if (isset($_GET['error'])): ?>
<div class="toast-msg toast-error"><?= htmlspecialchars($_GET['error']) ?></div>
<?php endif; ?>

<!-- 카테고리 탭 -->
<div class="cat-tabs">
	<a href="/admin/faqs" class="cat-tab <?= $category === '' ? 'active' : '' ?>">전체</a>
	<?php foreach ($categories as $k => $v): ?>
	<a href="/admin/faqs?category=<?= $k ?>" class="cat-tab <?= $category === $k ? 'active' : '' ?>"><?= $v ?></a>
	<?php endforeach; ?>
</div>

<div class="top-bar">
	<div style="font-size:13px;color:#8898aa;">총 <strong><?= count($faqs) ?></strong>개</div>
	<a href="/admin/faqs/create" class="btn-create">
		<svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
			<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
		</svg>
		FAQ 등록
	</a>
</div>

<div class="tbl-wrap">
	<table class="data-table">
		<colgroup>
			<col width="5%">
			<col width="10%">
			<col width="">
			<col width="7%">            
			<col width="7%">
			<col width="10%">
		</colgroup>
		<thead>
			<tr>
				<th>NO</th>
				<th>카테고리</th>
				<th style="text-align:left;">질문</th>
				<th>순서</th>
				<th>상태</th>
				<th>관리</th>
			</tr>
		</thead>
		<tbody>
			<?php if (empty($faqs)): ?>
				<tr class="empty-row"><td colspan="6">FAQ가 없습니다.</td></tr>
			<?php else: ?>
				<?php foreach ($faqs as $_i => $f): ?>
				<tr>
					<td><?= count($faqs) - $_i ?></td>
					<td><?= htmlspecialchars($categories[$f['category']] ?? $f['category']) ?></td>
					<td class="q-cell"><?= htmlspecialchars($f['question']) ?></td>
					<td><?= $f['sort_order'] ?></td>
					<td><span class="badge badge-<?= $f['is_active'] ? 'active' : 'inactive' ?>"><?= $f['is_active'] ? '활성' : '비활성' ?></span></td>
					<td style="white-space:nowrap;">
						<div class="act-btn-wrap">
							<a href="/admin/faqs/<?= $f['faq_idx'] ?>/edit" class="act-btn act-edit">수정</a>
							<form method="POST" action="/admin/faqs/<?= $f['faq_idx'] ?>/delete"
								onsubmit="return confirm('FAQ를 삭제하시겠습니까?')">
								<input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
								<button type="submit" class="act-btn act-del">삭제</button>
							</form>
						</div>
					</td>
				</tr>
				<?php endforeach; ?>
			<?php endif; ?>
		</tbody>
	</table>
</div>
<script>sessionStorage.setItem('back_faqs', location.href);</script>
