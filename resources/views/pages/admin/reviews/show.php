<?php
/**
 * 관리자 후기 상세
 * @var array  $review
 * @var array  $images
 * @var string $csrfToken
 */

$rating  = (int) $review['rating'];
$starPct = $rating * 20;
?>

<?php if (isset($_GET['saved'])): ?>
<div class="toast-msg toast-success">✓ 저장되었습니다.</div>
<?php endif; ?>

<div class="page-actions" style="margin-bottom:16px">
	<a href="/admin/reviews" class="btn-back">← 목록</a>
</div>

<div class="detail-card">

	<!-- 강의 정보 -->
	<div class="detail-section">
		<div class="detail-section-title">강의 정보</div>
		<div class="detail-row">
			<div class="detail-label">강의명</div>
			<div class="detail-value"><?= htmlspecialchars($review['class_title']) ?></div>
		</div>
		<div class="detail-row">
			<div class="detail-label">작성자</div>
			<div class="detail-value">
				<?= htmlspecialchars($review['member_name']) ?>
				<span style="color:#aaa;font-size:12px;margin-left:6px"><?= htmlspecialchars($review['member_email']) ?></span>
			</div>
		</div>
		<div class="detail-row">
			<div class="detail-label">작성일</div>
			<div class="detail-value"><?= date('Y-m-d H:i', strtotime($review['created_at'])) ?></div>
		</div>
		<?php if ($review['updated_at']): ?>
		<div class="detail-row">
			<div class="detail-label">수정일</div>
			<div class="detail-value"><?= date('Y-m-d H:i', strtotime($review['updated_at'])) ?></div>
		</div>
		<?php endif; ?>
	</div>

	<!-- 후기 내용 -->
	<div class="detail-section">
		<div class="detail-section-title">후기 내용</div>
		<div class="detail-row">
			<div class="detail-label">별점</div>
			<div class="detail-value" style="display:flex;align-items:center;gap:8px">
				<span style="font-size:18px;color:#FFA92C">
					<?php for ($i = 1; $i <= 5; $i++): ?>
						<?= $i <= $rating ? '★' : '☆' ?>
					<?php endfor; ?>
				</span>
				<span style="color:#888;font-size:14px"><?= $rating ?>점</span>
			</div>
		</div>
		<div class="detail-row">
			<div class="detail-label">제목</div>
			<div class="detail-value"><?= htmlspecialchars($review['title'] ?? '') ?></div>
		</div>
		<div class="detail-row" style="align-items:flex-start">
			<div class="detail-label">내용</div>
			<div class="detail-value" style="white-space:pre-wrap;line-height:1.6"><?= htmlspecialchars($review['content']) ?></div>
		</div>

		<?php if (!empty($images)): ?>
		<div class="detail-row" style="align-items:flex-start">
			<div class="detail-label">첨부 이미지</div>
			<div class="detail-value">
				<div style="display:flex;flex-wrap:wrap;gap:8px">
					<?php foreach ($images as $img): ?>
					<a href="/uploads/review/<?= htmlspecialchars($img['image_path']) ?>" target="_blank">
						<img src="/uploads/review/<?= htmlspecialchars($img['image_path']) ?>"
						     style="width:120px;height:80px;object-fit:cover;border-radius:6px;border:1px solid #eee">
					</a>
					<?php endforeach; ?>
				</div>
			</div>
		</div>
		<?php endif; ?>
	</div>

	<!-- 노출 설정 -->
	<div class="detail-section">
		<div class="detail-section-title">노출 설정</div>
		<div class="detail-row">
			<div class="detail-label">현재 상태</div>
			<div class="detail-value">
				<span class="badge badge-<?= $review['is_active'] ? 'active' : 'inactive' ?>">
					<?= $review['is_active'] ? '노출' : '숨김' ?>
				</span>
			</div>
		</div>
	</div>

</div>

<!-- 액션 버튼 -->
<div class="form-actions" style="margin-top:24px;display:flex;gap:10px">
	<form method="POST" action="/admin/reviews/<?= (int)$review['review_idx'] ?>/active">
		<input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
		<button type="submit" class="btn-save">
			<?= $review['is_active'] ? '숨김 처리' : '노출 처리' ?>
		</button>
	</form>
	<form method="POST" action="/admin/reviews/<?= (int)$review['review_idx'] ?>/delete"
	      onsubmit="return confirm('후기를 완전히 삭제하시겠습니까?\n삭제 후 복구할 수 없습니다.')">
		<input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
		<button type="submit" class="btn-del" style="background:#e74c3c;color:#fff;border:none;padding:8px 18px;border-radius:6px;cursor:pointer">
			후기 삭제
		</button>
	</form>
</div>
