<?php
/**
 * 관리자 사이트 설정
 * @var array  $settings   config_key => config_value
 * @var string $csrfToken
 * @var bool   $saved
 */
$s = fn(string $key) => htmlspecialchars($settings[$key] ?? '');
?>

<?php if ($saved): ?>
<div class="toast-msg toast-success">✓ 설정이 저장되었습니다.</div>
<?php endif; ?>
<?php if (isset($_GET['error'])): ?>
<div class="toast-msg toast-error"><?= htmlspecialchars($_GET['error']) ?></div>
<?php endif; ?>

<form method="POST" action="/admin/settings" enctype="multipart/form-data">
	<input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">

	<div class="form-layout ver2">
		<div>
			<!-- 기본 정보 -->
			<div class="form-card">
					<h3>기본 정보</h3>
					<div class="form-grid">
							<div class="form-group">
									<label>사이트명</label>
									<input type="text" name="site_name" class="form-control"
													value="<?= $s('site_name') ?>" placeholder="유니콘클래스">
							</div>
							<div class="form-group">
									<label>상호명</label>
									<input type="text" name="company_name" class="form-control"
													value="<?= $s('company_name') ?>" placeholder="(주)유니콘클래스">
							</div>
							<div class="form-group">
									<label>대표자명</label>
									<input type="text" name="ceo_name" class="form-control"
													value="<?= $s('ceo_name') ?>" placeholder="홍길동">
							</div>
							<div class="form-group">
									<label>사업자등록번호</label>
									<input type="text" name="business_no" class="form-control"
													value="<?= $s('business_no') ?>" placeholder="000-00-00000">
							</div>
							<div class="form-group">
									<label>대표전화</label>
									<input type="text" name="phone" class="form-control"
													value="<?= $s('phone') ?>" placeholder="02-0000-0000">
							</div>
							<div class="form-group">
									<label>이메일</label>
									<input type="email" name="email" class="form-control"
													value="<?= $s('email') ?>" placeholder="help@unicornclass.com">
							</div>
							<div class="form-group full">
									<label>주소</label>
									<input type="text" name="address" class="form-control"
													value="<?= $s('address') ?>" placeholder="서울특별시 강남구 ...">
							</div>
							<div class="form-group full">
									<label>저작권 문구 (푸터)</label>
									<input type="text" name="footer_copy" class="form-control"
													value="<?= $s('footer_copy') ?>" placeholder="© 2025 유니콘클래스. All rights reserved.">
							</div>
					</div>
			</div>
		</div>

		<div>
			<!-- 로고 / 파비콘 -->
			<div class="form-card">
					<h3>로고 / 파비콘</h3>
					<div class="form-grid">
							<div class="form-group">
									<label>로고 이미지</label>
									<?php if (!empty($settings['logo'])): ?>
											<img src="/uploads/site/<?= htmlspecialchars($settings['logo']) ?>"
														alt="현재 로고" class="preview-img" id="logoPreview">
									<?php else: ?>
											<img src="" alt="" class="preview-img" id="logoPreview" style="display:none">
									<?php endif; ?>
									<input type="file" name="logo" id="logoInput"
													accept="image/jpeg,image/png,image/webp,image/svg+xml">
									<div class="hint">jpg, png, webp, svg / 최대 2MB</div>
							</div>
							<div class="form-group">
									<label>파비콘</label>
									<?php if (!empty($settings['favicon'])): ?>
											<img src="/uploads/site/<?= htmlspecialchars($settings['favicon']) ?>"
														alt="현재 파비콘" class="preview-img" id="faviconPreview">
									<?php else: ?>
											<img src="" alt="" class="preview-img" id="faviconPreview" style="display:none">
									<?php endif; ?>
									<input type="file" name="favicon" id="faviconInput"
													accept="image/x-icon,image/png,image/svg+xml">
									<div class="hint">ico, png, svg / 최대 2MB / 권장 32×32px</div>
							</div>
					</div>
			</div>

			<!-- SNS 링크 -->
			<div class="form-card">
					<h3>SNS 링크</h3>
					<div class="sns-row">
							<div class="form-group">
									<label>인스타그램</label>
									<input type="url" name="sns_instagram" class="form-control"
													value="<?= $s('sns_instagram') ?>" placeholder="https://instagram.com/...">
							</div>
							<div class="form-group">
									<label>유튜브</label>
									<input type="url" name="sns_youtube" class="form-control"
													value="<?= $s('sns_youtube') ?>" placeholder="https://youtube.com/@...">
							</div>
							<div class="form-group">
									<label>페이스북</label>
									<input type="url" name="sns_facebook" class="form-control"
													value="<?= $s('sns_facebook') ?>" placeholder="https://facebook.com/...">
							</div>
							<div class="form-group">
									<label>블로그</label>
									<input type="url" name="sns_blog" class="form-control"
													value="<?= $s('sns_blog') ?>" placeholder="https://blog.naver.com/...">
							</div>
					</div>
			</div>
		</div>
	</div>

	<div class="form-actions">
			<button type="submit" class="btn-save">저장</button>
	</div>
</form>

<script>
function previewImage(inputId, previewId) {
	document.getElementById(inputId).addEventListener('change', function () {
		const preview = document.getElementById(previewId);
		const file = this.files[0];
		if (!file){ 
			preview.src = '';
			return;
		}		
		preview.src = URL.createObjectURL(file);
		preview.style.display = 'block';
	});
}
previewImage('logoInput', 'logoPreview');
previewImage('faviconInput', 'faviconPreview');
</script>
