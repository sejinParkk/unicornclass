<?php
/**
 * 관리자 1:1 문의 상세
 * @var array  $contact
 * @var string $csrfToken
 */
$catLabel = ['class' => '강의', 'payment' => '결제', 'account' => '계정', 'tech' => '기술', 'etc' => '기타'];
?>

<?php if (isset($_GET['answered'])): ?>
<div class="toast-msg toast-success">✓ 답변이 저장되었습니다.</div>
<?php endif; ?>
<?php if (isset($_GET['error'])): ?>
<div class="toast-msg toast-error"><?= htmlspecialchars($_GET['error']) ?></div>
<?php endif; ?>

<div class="form-card">
		<h3>
			문의 상세
			<span class="badge badge-<?= $contact['status'] ?>"><?= $contact['status'] === 'wait' ? '미답변' : '답변완료' ?></span>
		</h3>
		<div class="qna-meta">
			<span>분류: <strong><?= $catLabel[$contact['category']] ?? $contact['category'] ?></strong></span>
			<span>회원: <a href="/admin/members/<?= $contact['member_idx'] ?>" style="color:#c0392b;font-weight:600;"><?= htmlspecialchars($contact['mb_name']) ?></a> (<?= htmlspecialchars($contact['mb_id']) ?>)</span>
			<span>접수: <?= date('Y-m-d H:i', strtotime($contact['created_at'])) ?></span>
		</div>
		<h4 style="font-size:14px;font-weight:600;margin-bottom:10px;color:#1a202c;"><?= htmlspecialchars($contact['title']) ?></h4>
		<div class="qna-content"><?= htmlspecialchars($contact['content']) ?></div>
		<?php if ($contact['file_path']): ?>
		<div style="margin-top:12px;font-size:12.5px;">
			첨부파일: <a href="/uploads/materials/<?= htmlspecialchars($contact['file_path']) ?>" style="color:#c0392b;"><?= htmlspecialchars($contact['file_path']) ?></a>
		</div>
		<?php endif; ?>
</div>

<div class="form-card">
		<h3>관리자 답변</h3>
		<?php if ($contact['status'] === 'done' && $contact['answer']): ?>
			<div class="answered-box"><?= htmlspecialchars($contact['answer']) ?></div>
			<div class="answered-meta">답변일: <?= date('Y-m-d H:i', strtotime($contact['answered_at'])) ?></div>
			<div style="margin-top:16px;">
				<p style="font-size:12.5px;color:#718096;margin-bottom:10px;">답변을 수정하려면 아래에서 다시 작성하세요.</p>
				<form method="POST" action="/admin/contacts/<?= $contact['qna_idx'] ?>/answer">
					<input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
					<div class="form-group">
							<textarea name="answer" class="form-control"><?= htmlspecialchars($contact['answer']) ?></textarea>
					</div>
					<div class="form-actions">
						<a href="/admin/contacts" class="btn-back">목록</a>
						<button type="submit" class="btn-save">답변 수정</button>
					</div>
				</form>
			</div>
		<?php else: ?>
			<form method="POST" action="/admin/contacts/<?= $contact['qna_idx'] ?>/answer">
				<input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
				<div class="form-group">
					<label>답변 내용 <span style="color:#c0392b">*</span></label>
					<textarea name="answer" class="form-control" placeholder="답변을 입력하세요."></textarea>
				</div>
				<div class="form-actions">
					<a href="/admin/contacts" class="btn-back">목록</a>
					<button type="submit" class="btn-save">답변 저장</button>
				</div>
			</form>
		<?php endif; ?>
</div>
<script>
(function(){
	var u = sessionStorage.getItem('back_contacts');
	if (u) document.querySelectorAll('.btn-back').forEach(function(el){ el.href = u; });
})();
</script>
