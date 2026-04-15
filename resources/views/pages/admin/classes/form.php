<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/lang/summernote-ko-KR.min.js"></script>
<?php
/**
 * 강의 등록/수정 폼 (공용)
 * @var array|null  $class       null=신규, array=수정
 * @var array       $instructors 강사 목록
 * @var array       $categories  카테고리 목록
 * @var array       $chapters    챕터 목록 (duration_display 포함)
 * @var array       $materials   강의 자료 목록
 * @var array       $errors      유효성 에러
 * @var string      $csrfToken
 */
$isEdit   = !is_null($class);
$classIdx = $isEdit ? (int)$class['class_idx'] : 0;

// 폼 값: 수정 시 $class, 에러 시 $_POST, 신규는 기본값
$classData = $class ?? [];
$v = fn(string $key, $default = '') => htmlspecialchars(
	(string)($_POST[$key] ?? ($classData[$key] ?? $default))
);

// 챕터 초기 데이터 (JS 로 전달)
// 에러 재표시 시 $_POST['chapters_json'] 우선
$initChapters = [];
if ($isEdit) {
	foreach ($chapters as $ch) {
			$initChapters[] = [
					'chapter_idx'      => (int) $ch['chapter_idx'],
					'title'            => $ch['title'],
					'vimeo_url'        => $ch['vimeo_url'] ?? '',
					'duration'         => \App\Repositories\ChapterRepository::secondsToDisplay((int)$ch['duration']),
			];
	}
} elseif (!empty($_POST['chapters_json'])) {
	$decoded = json_decode($_POST['chapters_json'], true);
	if (is_array($decoded)) $initChapters = $decoded;
}
?>

<?php if (isset($_GET['created'])): ?>
<div class="toast-msg toast-success">✓ 강의가 등록되었습니다.</div>
<?php elseif (isset($_GET['updated'])): ?>
<div class="toast-msg toast-success">✓ 강의 정보가 저장되었습니다.</div>
<?php endif; ?>

<form method="POST"
		action="<?= $isEdit ? "/admin/classes/{$classIdx}" : '/admin/classes' ?>"
		enctype="multipart/form-data"
		id="classForm">
	<input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
	<input type="hidden" name="chapters_json" id="chaptersJson" value="">
	<input type="hidden" name="delete_file_ids" id="deleteFileIds" value="">

	<div class="form-layout">
			<!-- ── 좌측: 주요 내용 ───────────────────────────── -->
			<div>
					<div class="form-card">
							<h3>기본 정보</h3>

							<div class="form-group">
									<label class="form-label">강의명 <span class="req">*</span></label>
									<input type="text" name="title"
													class="form-control <?= isset($errors['title']) ? 'has-error' : '' ?>"
													value="<?= $v('title') ?>" placeholder="강의 제목 입력">
									<?php if (isset($errors['title'])): ?>
											<p class="field-error"><?= htmlspecialchars($errors['title']) ?></p>
									<?php endif; ?>
							</div>

							<div class="form-group">
									<label class="form-label">한줄 요약</label>
									<input type="text" name="summary" class="form-control"
													value="<?= $v('summary') ?>" placeholder="강의 목록에 표시될 짧은 설명 (선택)">
							</div>

							<div class="form-group">
									<label class="form-label">강의 소개</label>
									<textarea id="classDescription" name="description" class="form-control" rows="8"
														placeholder="강의 상세 소개 (HTML 입력 가능)"><?= $v('description') ?></textarea>
							</div>

							<div class="form-group">
									<label class="form-label">카카오 오픈채팅 URL</label>
									<input type="url" name="kakao_url" class="form-control"
													value="<?= $v('kakao_url') ?>" placeholder="https://open.kakao.com/...">
							</div>

							<div class="form-group" id="vimeoGroup"
										style="<?= ($class['type'] ?? '') === 'premium' ? 'display:none' : '' ?>">
									<label class="form-label">Vimeo URL (대표 영상)</label>
									<input type="url" name="vimeo_url" class="form-control"
													value="<?= $v('vimeo_url') ?>" placeholder="https://vimeo.com/...">
									<p class="form-hint">무료 강의의 대표 영상 URL을 입력하세요.</p>
							</div>
					</div>

					<div class="form-card">
							<h3>썸네일</h3>
							<div class="form-group">
									<input type="file" name="thumbnail" accept="image/jpeg,image/png,image/webp"
													class="form-control <?= isset($errors['thumbnail']) ? 'has-error' : '' ?>"
													id="thumbInput">
									<p class="form-hint">jpg, png, webp / 최대 5MB / 권장 1280×720px</p>
									<?php if (isset($errors['thumbnail'])): ?>
											<p class="field-error" id="thumbError"><?= htmlspecialchars($errors['thumbnail']) ?></p>
									<?php endif; ?>
									<div class="thumb-preview">
											<?php if ($isEdit && !empty($class['thumbnail'])): ?>
													<img src="/uploads/class/<?= htmlspecialchars($class['thumbnail']) ?>"
																id="thumbImg" alt="썸네일">
											<?php else: ?>
													<img id="thumbImg" style="display:none" alt="썸네일">
											<?php endif; ?>
									</div>
							</div>
					</div>
			</div>

			<!-- ── 우측: 설정 ────────────────────────────────── -->
			<div>
					<div class="form-card">
							<h3>강의 설정</h3>

							<div class="form-group">
									<label class="form-label">강사 <span class="req">*</span></label>
									<select name="instructor_idx"
													class="form-control <?= isset($errors['instructor_idx']) ? 'has-error' : '' ?>">
											<option value="">강사 선택</option>
											<?php foreach ($instructors as $inst): ?>
											<option value="<?= $inst['instructor_idx'] ?>"
													<?= (string)($class['instructor_idx'] ?? $_POST['instructor_idx'] ?? '') === (string)$inst['instructor_idx'] ? 'selected' : '' ?>>
													<?= htmlspecialchars($inst['name']) ?>
													<?php if ($inst['field']): ?>(<?= htmlspecialchars($inst['field']) ?>)<?php endif; ?>
											</option>
											<?php endforeach; ?>
									</select>
									<?php if (isset($errors['instructor_idx'])): ?>
											<p class="field-error"><?= htmlspecialchars($errors['instructor_idx']) ?></p>
									<?php endif; ?>
							</div>

							<div class="form-group">
									<label class="form-label">카테고리</label>
									<select name="category_idx" class="form-control">
											<option value="">카테고리 없음</option>
											<?php foreach ($categories as $cat): ?>
											<option value="<?= $cat['category_idx'] ?>"
													<?= (string)($class['category_idx'] ?? '') === (string)$cat['category_idx'] ? 'selected' : '' ?>>
													<?= htmlspecialchars($cat['name']) ?>
											</option>
											<?php endforeach; ?>
									</select>
							</div>

							<div class="form-group">
									<label class="form-label">강의 유형 <span class="req">*</span></label>
									<?php if ($isEdit && $this->classRepo->hasEnrollments($classIdx)): ?>
											<input type="text" class="form-control"
															value="<?= $class['type'] === 'free' ? '무료' : '프리미엄' ?>" disabled>
											<input type="hidden" name="type" value="<?= htmlspecialchars($class['type']) ?>">
											<p class="form-hint">수강자가 존재하여 유형을 변경할 수 없습니다.</p>
									<?php else: ?>
											<select name="type"
															class="form-control <?= isset($errors['type']) ? 'has-error' : '' ?>"
															id="typeSelect">
													<option value="">선택</option>
													<option value="free"    <?= ($class['type'] ?? '') === 'free'    ? 'selected' : '' ?>>무료</option>
													<option value="premium" <?= ($class['type'] ?? '') === 'premium' ? 'selected' : '' ?>>프리미엄</option>
											</select>
											<?php if (isset($errors['type'])): ?>
													<p class="field-error"><?= htmlspecialchars($errors['type']) ?></p>
											<?php endif; ?>
									<?php endif; ?>
							</div>

							<div class="form-group" id="priceGroup"
										style="<?= ($class['type'] ?? 'premium') === 'free' ? 'display:none' : '' ?>">
									<label class="form-label">정가 (원)</label>
									<input type="text" name="price_origin" class="form-control"
													value="<?= number_format($v('price_origin', '0')) ?>"
													onkeyup="inputNumberFormat(this)"
													>
							</div>

							<div class="form-group" id="discountGroup"
										style="<?= ($class['type'] ?? 'premium') === 'free' ? 'display:none' : '' ?>">
									<label class="form-label">할인가 (원)</label>
									<input type="text" name="price" class="form-control"
													value="<?= number_format($v('price', '0')) ?>"
													onkeyup="inputNumberFormat(this)"
													>
									<p class="form-hint">0이면 정가로 판매됩니다.</p>
							</div>

							<div class="form-group">
									<label class="form-label">수강 기간 (일)</label>
									<input type="number" name="duration_days" class="form-control"
													value="<?= $v('duration_days', '180') ?>" min="1" max="3650">
							</div>

							<div class="form-group">
									<label class="form-label">판매 종료일</label>
									<input type="datetime-local" name="sale_end_at" class="form-control"
													value="<?= $isEdit && $class['sale_end_at'] ? date('Y-m-d\TH:i', strtotime($class['sale_end_at'])) : '' ?>">
									<p class="form-hint">비워두면 무기한 판매됩니다.</p>
									<?php if (isset($errors['sale_end_at'])): ?>
											<p class="field-error"><?= htmlspecialchars($errors['sale_end_at']) ?></p>
									<?php endif; ?>
							</div>

							<div class="form-group">
									<label class="form-label">정렬 순서</label>
									<input type="number" name="sort_order" class="form-control"
													value="<?= $v('sort_order', '0') ?>">
							</div>
					</div>

					<div class="form-card" style="margin-top:16px">
							<h3>배지 / 노출</h3>
							<div class="form-group">
									<div class="toggle-row">
											<span class="toggle-label">활성화 (노출)</span>
											<label class="toggle-switch">
													<input type="checkbox" name="is_active" value="1"
																	<?= ($class['is_active'] ?? 0) ? 'checked' : '' ?>>
													<span class="toggle-slider"></span>
											</label>
									</div>
							</div>
							<div class="form-group">
									<label class="form-label">배지</label>
									<div class="badge-row">
											<label class="badge-check">
													<input type="checkbox" name="badge_hot" value="1"
																	<?= ($class['badge_hot'] ?? 0) ? 'checked' : '' ?>>
													🔥 HOT
											</label>
											<label class="badge-check">
													<input type="checkbox" name="badge_new" value="1"
																	<?= ($class['badge_new'] ?? 0) ? 'checked' : '' ?>>
													✨ NEW
											</label>
									</div>
							</div>
					</div>
			</div>
	</div>

	<!-- ── 챕터 관리 (등록/수정 공통) ─────────────────────── -->
	<div class="form-card chapter-section">
			<h3>챕터 관리
					<span id="chapterCountLabel" style="font-size:12px;color:#a0aec0;font-weight:400"></span>
			</h3>

			<ul class="chapter-list" id="chapterList"></ul>
			<div id="chapterEmpty" class="chapter-empty" style="display:none">
					아직 추가된 챕터가 없습니다. 아래 버튼으로 챕터를 추가하세요.
			</div>

			<button type="button" class="chapter-add-btn" onclick="openAddModal()">
					<svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
					</svg>
					챕터 추가
			</button>
	</div>

	<!-- ── 강의 자료 ─────────────────────────────────────── -->
	<div class="form-card" style="margin-top:20px">
			<h3>강의 자료
					<span style="font-size:12px;color:#a0aec0;font-weight:400">— 수강생에게 제공할 파일 또는 링크</span>
			</h3>

			<!-- 기존 자료 (수정 시) -->
			<?php if ($isEdit && !empty($materials)): ?>
			<div id="existingMaterials" style="margin-bottom:12px">
					<?php foreach ($materials as $m): ?>
					<div class="material-item" id="mat-<?= $m['file_idx'] ?>">
							<span class="mat-icon"><?= $m['file_type'] === 'file' ? '📄' : '🔗' ?></span>
							<div class="mat-body">
									<div class="mat-title"><?= htmlspecialchars($m['title']) ?></div>
									<div class="mat-info">
											<?php if ($m['file_type'] === 'file'): ?>
													<?= $m['file_size'] ? round($m['file_size'] / 1024 / 1024, 2) . ' MB' : '파일' ?>
											<?php else: ?>
													<?= htmlspecialchars($m['external_url'] ?? '') ?>
											<?php endif; ?>
									</div>
							</div>
							<button type="button" class="mat-del"
											onclick="deleteMaterialItem(this, <?= $m['file_idx'] ?>)">삭제</button>
					</div>
					<?php endforeach; ?>
			</div>
			<?php elseif ($isEdit): ?>
			<p style="font-size:13px;color:#a0aec0;margin-bottom:12px">등록된 자료가 없습니다.</p>
			<?php endif; ?>

			<!-- 새로 추가할 항목 -->
			<div id="newMaterialsList"></div>

			<div class="mat-add-btns">
					<button type="button" class="mat-add-btn" onclick="addFileRow()">
							📎 파일 추가
					</button>
					<button type="button" class="mat-add-btn" onclick="addLinkRow()">
							🔗 링크 추가
					</button>
			</div>
			<p class="form-hint" style="margin-top:8px">파일: pdf, doc, xls, ppt, zip, jpg, png (최대 50MB)</p>
	</div>

	<!-- ── 액션 바 ──────────────────────────────────────── -->
	<div class="form-actions">
			<button type="submit" class="btn-save"><?= $isEdit ? '저장' : '등록' ?></button>
			<a href="/admin/classes" class="btn-cancel">취소</a>
	</div>
</form>

<!-- ── 챕터 편집 모달 ────────────────────────────────── -->
<div class="modal-overlay" id="chapterModal">
	<div class="modal-box">
			<h4 id="modalTitle">챕터 추가</h4>
			<input type="hidden" id="modalChapterKey" value="">

			<div class="form-group">
					<label class="form-label">챕터 제목 <span class="req">*</span></label>
					<input type="text" id="modalChTitle" class="form-control" placeholder="챕터 제목">
			</div>
			<div class="form-group">
					<label class="form-label">Vimeo URL</label>
					<input type="text" id="modalVimeoUrl" class="form-control" placeholder="https://vimeo.com/...">
					<span id="vimeoFetchStatus" style="font-size:12px;margin-top:4px;display:block;"></span>
			</div>
			<div class="form-group">
					<label class="form-label">재생 시간 (분:초)</label>
					<input type="text" id="modalDuration" class="form-control" placeholder="예: 12:34">
			</div>

			<div class="modal-actions">
					<button type="button" class="modal-save" onclick="saveChapterLocal()">저장</button>
					<button type="button" class="modal-cancel" onclick="closeModal()">취소</button>
			</div>
	</div>
</div>

<script>
// ============================================================
// 챕터 관리 (로컬 상태, 폼 제출 시 일괄 저장)
// ============================================================
let nextKey = 0;
let chapters = <?= json_encode($initChapters) ?>.map(ch => ({ ...ch, _key: nextKey++ }));
let deleteFileIds = [];

function renderChapterList() {
	const list  = document.getElementById('chapterList');
	const empty = document.getElementById('chapterEmpty');
	const label = document.getElementById('chapterCountLabel');

	list.innerHTML = '';
	chapters.forEach((ch, i) => {
			const li = document.createElement('li');
			li.className = 'chapter-item';
			li.dataset.key = ch._key;
			li.setAttribute('draggable', 'true');
			li.innerHTML = `
					<span class="ch-handle">⠿</span>
					<span class="ch-order">${i + 1}</span>
					<span class="ch-title">${escHtml(ch.title)}</span>
					<span class="ch-vimeo" title="${escHtml(ch.vimeo_url)}">${escHtml(ch.vimeo_url || '—')}</span>
					<span class="ch-duration">${escHtml(ch.duration || '0:00')}</span>
					<div class="ch-actions">
							<button type="button" class="ch-btn ch-btn-edit">수정</button>
							<button type="button" class="ch-btn ch-btn-del">삭제</button>
					</div>
			`;
			li.querySelector('.ch-btn-edit').onclick = () => openEditModal(ch._key);
			li.querySelector('.ch-btn-del').onclick  = () => deleteChapterLocal(ch._key);
			list.appendChild(li);
	});

	const cnt = chapters.length;
	label.textContent = cnt > 0 ? `(${cnt}개 · 드래그로 순서 변경)` : '';
	empty.style.display = cnt === 0 ? '' : 'none';
}

// ── 모달 ──────────────────────────────────────
function openAddModal() {
	document.getElementById('modalTitle').textContent = '챕터 추가';
	document.getElementById('modalChapterKey').value  = '';
	document.getElementById('modalChTitle').value     = '';
	document.getElementById('modalVimeoUrl').value    = '';
	document.getElementById('modalDuration').value    = '';
	document.getElementById('chapterModal').classList.add('open');
	document.getElementById('modalChTitle').focus();
}

function openEditModal(key) {
	const ch = chapters.find(c => c._key === key);
	if (!ch) return;
	document.getElementById('modalTitle').textContent = '챕터 수정';
	document.getElementById('modalChapterKey').value  = key;
	document.getElementById('modalChTitle').value     = ch.title;
	document.getElementById('modalVimeoUrl').value    = ch.vimeo_url || '';
	document.getElementById('modalDuration').value    = ch.duration || '';
	document.getElementById('chapterModal').classList.add('open');
	document.getElementById('modalChTitle').focus();
}

function closeModal() {
	document.getElementById('chapterModal').classList.remove('open');
}

document.getElementById('chapterModal').addEventListener('click', function(e) {
	if (e.target === this) closeModal();
});

// ── Vimeo oEmbed로 재생시간 자동 가져오기 ────────────────────
document.getElementById('modalVimeoUrl').addEventListener('blur', async function() {
	const url = this.value.trim();
	const statusEl  = document.getElementById('vimeoFetchStatus');
	const durationEl = document.getElementById('modalDuration');

	if (!url || !url.includes('vimeo.com')) {
		statusEl.textContent = '';
		return;
	}

	statusEl.style.color = '#8898aa';
	statusEl.textContent = '시간 불러오는 중...';

	try {
		const res = await fetch('https://vimeo.com/api/oembed.json?url=' + encodeURIComponent(url));
		if (!res.ok) throw new Error('not ok');
		const data = await res.json();
		if (data.duration) {
			const m = Math.floor(data.duration / 60);
			const s = data.duration % 60;
			durationEl.value = `${m}:${String(s).padStart(2, '0')}`;
			statusEl.style.color = '#38a169';
			statusEl.textContent = '✓ 재생 시간 자동 입력됨';
		} else {
			throw new Error('no duration');
		}
	} catch (_) {
		statusEl.style.color = '#e53e3e';
		statusEl.textContent = '불러오기 실패 — 직접 입력해주세요';
	}

	setTimeout(() => { statusEl.textContent = ''; }, 4000);
});

// ── 챕터 로컬 저장/삭제 ──────────────────────
function saveChapterLocal() {
	const keyStr   = document.getElementById('modalChapterKey').value;
	const title    = document.getElementById('modalChTitle').value.trim();
	const vimeoUrl = document.getElementById('modalVimeoUrl').value.trim();
	const duration = document.getElementById('modalDuration').value.trim() || '0:00';

	if (!title) { alert('챕터 제목을 입력해주세요.'); return; }

	if (keyStr === '') {
			// 신규 추가
			chapters.push({
					chapter_idx: null,
					_key: nextKey++,
					title,
					vimeo_url: vimeoUrl,
					duration,
			});
	} else {
			// 기존 수정
			const key = parseInt(keyStr);
			const ch  = chapters.find(c => c._key === key);
			if (ch) {
					ch.title     = title;
					ch.vimeo_url = vimeoUrl;
					ch.duration  = duration;
			}
	}

	closeModal();
	renderChapterList();
}

function deleteChapterLocal(key) {
	if (!confirm('이 챕터를 삭제하시겠습니까?')) return;
	chapters = chapters.filter(c => c._key !== key);
	renderChapterList();
}

// ── 드래그 앤 드롭 ────────────────────────────
(function () {
	const list = document.getElementById('chapterList');
	let dragEl = null;

	list.addEventListener('dragstart', e => {
			dragEl = e.target.closest('li');
			if (!dragEl) return;
			dragEl.style.opacity = '.4';
			e.dataTransfer.effectAllowed = 'move';
	});
	list.addEventListener('dragend', () => {
			if (dragEl) { dragEl.style.opacity = ''; dragEl = null; }
			// DOM 순서에 맞게 chapters 배열 재정렬
			const keys = [...list.querySelectorAll('li')].map(li => parseInt(li.dataset.key));
			chapters.sort((a, b) => keys.indexOf(a._key) - keys.indexOf(b._key));
			renderChapterList();
	});
	list.addEventListener('dragover', e => {
			e.preventDefault();
			const target = e.target.closest('li');
			if (!target || target === dragEl) return;
			const rect  = target.getBoundingClientRect();
			const after = e.clientY > rect.top + rect.height / 2;
			list.insertBefore(dragEl, after ? target.nextSibling : target);
	});
})();

// ── 폼 제출 시 chapters_json 직렬화 ──────────
document.getElementById('classForm').addEventListener('submit', function (e) {
	const typeEl = document.getElementById('typeSelect');
	const type   = typeEl ? typeEl.value : '<?= htmlspecialchars($class['type'] ?? '') ?>';

	if (type === 'premium' && chapters.length === 0) {
		e.preventDefault();
		alert('프리미엄 강의는 챕터를 1개 이상 등록해야 합니다.');
		document.getElementById('chapterList').scrollIntoView({behavior: 'smooth', block: 'center'});
		return;
	}

	const json = JSON.stringify(chapters.map((ch, i) => ({
			chapter_idx: ch.chapter_idx ?? null,
			title:       ch.title,
			vimeo_url:   ch.vimeo_url || '',
			duration:    ch.duration  || '0:00',
			sort_order:  i + 1,
	})));
	document.getElementById('chaptersJson').value = json;
	document.getElementById('deleteFileIds').value = deleteFileIds.join(',');
});

// ── 초기 렌더링 ───────────────────────────────
renderChapterList();

// ── 에러 위치로 스크롤 ─────────────────────────
(function() {
	const thumbErr = document.getElementById('thumbError');
	if (thumbErr) { thumbErr.scrollIntoView({behavior:'smooth', block:'center'}); }
	else {
		const firstErr = document.querySelector('.field-error, .has-error');
		if (firstErr) firstErr.scrollIntoView({behavior:'smooth', block:'center'});
	}
})();

// ============================================================
// 강의 자료 관리
// ============================================================
function deleteMaterialItem(btn, fileIdx) {
	if (!confirm('이 자료를 삭제하시겠습니까?')) return;
	deleteFileIds.push(fileIdx);
	btn.closest('.material-item').remove();
}

function addFileRow() {
	const div = document.createElement('div');
	div.className = 'material-new-row';
	div.innerHTML = `
			<input type="text" name="new_file_titles[]" placeholder="파일 제목 (선택)" class="form-control" style="max-width:200px">
			<input type="file" name="new_files[]" accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.zip,.jpg,.jpeg,.png">
			<button type="button" class="mat-remove" onclick="this.closest('.material-new-row').remove()">✕</button>
	`;
	document.getElementById('newMaterialsList').appendChild(div);
}

function addLinkRow() {
	const div = document.createElement('div');
	div.className = 'material-new-row';
	div.innerHTML = `
			<input type="text" name="new_link_titles[]" placeholder="링크 제목" class="form-control" style="max-width:200px">
			<input type="url" name="new_link_urls[]" placeholder="https://..." class="form-control">
			<button type="button" class="mat-remove" onclick="this.closest('.material-new-row').remove()">✕</button>
	`;
	document.getElementById('newMaterialsList').appendChild(div);
}

// ============================================================
// 썸네일 미리보기
// ============================================================
document.getElementById('thumbInput')?.addEventListener('change', function () {
	const file = this.files[0];
	if (!file) return;
	const img = document.getElementById('thumbImg');
	img.src = URL.createObjectURL(file);
	img.style.display = 'block';
});

// ============================================================
// 유형 → 가격 필드 토글
// ============================================================
document.getElementById('typeSelect')?.addEventListener('change', function () {
	const show = this.value === 'premium';
	document.getElementById('priceGroup').style.display    = show ? '' : 'none';
	document.getElementById('discountGroup').style.display = show ? '' : 'none';
	document.getElementById('vimeoGroup').style.display    = show ? 'none' : '';
});

// ============================================================
// 유틸
// ============================================================
function escHtml(str) {
	return (str || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}
(function(){
	var u = sessionStorage.getItem('back_classes');
	if (u) { var el = document.querySelector('.btn-cancel'); if (el) el.href = u; }
})();

// ── 폼 submit 시 써머노트 → textarea 동기화 ──
document.getElementById('classForm').addEventListener('submit', function () {
	$('#classDescription').val($('#classDescription').summernote('code'));
}, true);
</script>
<script>
$(document).ready(function () {
	$('#classDescription').summernote({
		height: 400,
		lang: 'ko-KR',
		toolbar: [
			['style',  ['bold', 'italic', 'underline', 'strikethrough', 'clear']],
			['color',  ['forecolor', 'color']],
			['para',   ['ul', 'ol', 'paragraph']],
			['table',  ['table']],
			['insert', ['link', 'video']],
			['view',   ['codeview']]
		]
	});
});
</script>
