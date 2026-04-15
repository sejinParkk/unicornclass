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

			<!-- 히어로 배너 동영상 -->
			<div class="form-card">
					<h3>히어로 배너 동영상</h3>
					<?php if (!empty($settings['hero_video'])): ?>
					<div style="margin-bottom:12px">
						<video src="/uploads/site/<?= htmlspecialchars($settings['hero_video']) ?>"
							   controls muted style="max-width:100%;max-height:180px;border-radius:6px;display:block"></video>
						<label style="display:flex;align-items:center;gap:6px;margin-top:8px;font-size:13px;cursor:pointer">
							<input type="checkbox" name="delete_hero_video" value="1"> 현재 영상 삭제
						</label>
					</div>
					<?php endif; ?>
					<input type="file" name="hero_video" id="heroVideoInput" accept="video/mp4">
					<div class="hint">mp4 / 최대 200MB / 자동재생·음소거로 재생됩니다</div>

					<hr style="border:none;border-top:1px solid #eee;margin:20px 0">

					<h4 style="font-size:13px;font-weight:700;color:#333;margin-bottom:4px;">
						포스터 이미지
						<span style="font-size:11px;font-weight:400;color:#888;margin-left:6px;">영상 로드 전 표시되는 배경 이미지 (검은 화면 방지)</span>
					</h4>
					<?php if (!empty($settings['hero_poster'])): ?>
					<div style="margin-bottom:10px">
						<img src="/uploads/site/<?= htmlspecialchars($settings['hero_poster']) ?>"
							 alt="현재 포스터" style="max-width:100%;max-height:120px;object-fit:cover;border-radius:6px;display:block;border:1px solid #eee;">
						<label style="display:flex;align-items:center;gap:6px;margin-top:8px;font-size:13px;cursor:pointer">
							<input type="checkbox" name="delete_hero_poster" value="1"> 현재 포스터 삭제
						</label>
					</div>
					<?php endif; ?>
					<input type="file" name="hero_poster" id="heroPosterInput"
						   accept="image/jpeg,image/png,image/webp">
					<div class="hint">jpg·png·webp / 최대 2MB / 히어로 배너와 동일한 비율 권장</div>
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

			<!-- 카카오 채널 -->
			<div class="form-card">
					<h3>카카오 채널</h3>
					<div class="form-group">
							<label>카카오 채널 URL
								<span style="font-size:11px;font-weight:400;color:#888;margin-left:6px;">
									입력 시 메인 우측 하단에 플로팅 버튼이 표시됩니다
								</span>
							</label>
							<input type="url" name="kakao_channel_url" class="form-control"
									value="<?= $s('kakao_channel_url') ?>"
									placeholder="https://pf.kakao.com/_xxxxx">
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
