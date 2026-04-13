<?php
/**
 * 관리자 오픈채팅 통계
 * @var array  $byClass     [class_idx, class_title, class_type, click_count]
 * @var array  $byDay       [day, cnt]
 * @var int    $totalCount
 * @var string $dateFrom
 * @var string $dateTo
 */
?>

<form method="GET" action="/admin/openchat-logs" class="filter-bar">
	<div>
		<div style="font-size:11.5px;color:#a0aec0;margin-bottom:4px;">시작일</div>
		<input type="date" name="date_from" value="<?= htmlspecialchars($dateFrom) ?>">
	</div>
	<div>
		<div style="font-size:11.5px;color:#a0aec0;margin-bottom:4px;">종료일</div>
		<input type="date" name="date_to" value="<?= htmlspecialchars($dateTo) ?>">
	</div>
	<button type="submit" class="btn-search">조회</button>
</form>

<div class="kpi-card">
	<div class="kpi-val"><?= number_format($totalCount) ?></div>
	<div class="kpi-label">기간 내 총 오픈채팅 클릭 수 (<?= htmlspecialchars($dateFrom) ?> ~ <?= htmlspecialchars($dateTo) ?>)</div>
</div>

<div class="stats-grid">
	<!-- 강의별 클릭 수 -->
	<div class="stat-card">
			<h3>강의별 오픈채팅 클릭 수</h3>
			<?php if (empty($byClass)): ?>
				<div class="empty-state">데이터가 없습니다.</div>
			<?php else:
				$maxCount = max(array_column($byClass, 'click_count'));
			?>
				<table class="rank-table">
					<colgroup>
						<col width="34%">
						<col width="33%">
						<col width="33%">
					</colgroup>
					<thead>
						<tr>
							<th>강의</th>
							<th>유형</th>
							<th>클릭 수</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($byClass as $i => $row): ?>
						<tr>
							<td style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-weight:<?= $i < 3 ? '600' : '400' ?>;">
								<?= htmlspecialchars($row['class_title']) ?>
							</td>
							<td>
								<span class="badge badge-<?= $row['class_type'] ?>">
									<?= $row['class_type'] === 'free' ? '무료' : '프리미엄' ?>
								</span>
							</td>
							<td>
								<?= number_format($row['click_count']) ?>
								<div style="margin-top:3px;height:6px;background:#f0f2f5;border-radius:3px;">
									<div style="width:<?= $maxCount > 0 ? min(100, round($row['click_count'] / $maxCount * 100)) : 0 ?>%;height:6px;background:#5E81F4;border-radius:3px;"></div>
								</div>
							</td>
						</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			<?php endif; ?>
	</div>

	<!-- 날짜별 클릭 수 -->
	<div class="stat-card">
		<h3>날짜별 클릭 수 (최근 30일)</h3>
		<?php if (empty($byDay)): ?>
			<div class="empty-state">데이터가 없습니다.</div>
		<?php else:
			$maxDay = max(array_column($byDay, 'cnt'));
		?>
			<div class="bar-wrap">
			<?php foreach ($byDay as $row): ?>
				<div class="bar-row">
					<div class="bar-label"><?= htmlspecialchars($row['day']) ?></div>
					<div class="bar-bg">
						<div class="bar-fill" style="width:<?= $maxDay > 0 ? min(100, round($row['cnt'] / $maxDay * 100)) : 0 ?>%;"></div>
					</div>
					<div class="bar-count"><?= number_format($row['cnt']) ?></div>
				</div>
			<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>
</div>
