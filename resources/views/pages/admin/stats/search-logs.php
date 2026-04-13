<?php
/**
 * 관리자 검색 로그
 * @var array  $ranking     [keyword, search_count, no_result_count]
 * @var array  $byDay       [day, cnt]
 * @var int    $totalCount
 * @var string $dateFrom
 * @var string $dateTo
 */
?>

<form method="GET" action="/admin/search-logs" class="filter-bar">
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
	<div>
		<div class="kpi-val"><?= number_format($totalCount) ?></div>
		<div class="kpi-label">기간 내 총 검색 건수 (<?= htmlspecialchars($dateFrom) ?> ~ <?= htmlspecialchars($dateTo) ?>)</div>
	</div>
</div>

<div class="stats-grid">
	<!-- 검색어 순위 -->
	<div class="stat-card">
			<h3>인기 검색어 TOP 50</h3>
			<?php if (empty($ranking)): ?>
					<div class="empty-state">검색 데이터가 없습니다.</div>
			<?php else:
					$maxCount = max(array_column($ranking, 'search_count'));
			?>
					<table class="rank-table">
						<colgroup>
							<col width="15%">
							<col width="45%">
							<col width="40%">
							<!-- <col width="15%"> -->
						</colgroup>
						<thead>
							<tr>
								<th>순위</th>
								<th>검색어</th>
								<th>검색 수</th>
								<!-- <th>결과 없음</th> -->
							</tr>
						</thead>
						<tbody>
						<?php foreach ($ranking as $i => $row): ?>
						<tr>
							<td style="text-align:center;">
								<span class="rank-num <?= $i === 0 ? 'rank-1' : ($i === 1 ? 'rank-2' : ($i === 2 ? 'rank-3' : 'rank-n')) ?>">
									<?= $i + 1 ?>
								</span>
						</td>
							<td style="text-align:center;font-weight:<?= $i < 3 ? '600' : '400' ?>;"><?= htmlspecialchars($row['keyword']) ?></td>
							<td>
								<?= number_format($row['search_count']) ?>
								<div style="margin-top:3px;height:6px;background:#f0f2f5;border-radius:3px;">
									<div style="width:<?= min(100, round($row['search_count'] / $maxCount * 100)) ?>%;height:6px;background:#5E81F4;border-radius:3px;"></div>
								</div>
							</td>
							<!-- <td style="text-align:center;">
								<?php if ($row['no_result_count'] > 0): ?>
								<span class="no-result-badge"><?= $row['no_result_count'] ?></span>
								<?php else: ?>
								<span style="color:#e2e8f0;">-</span>
								<?php endif; ?>
							</td> -->
						</tr>
						<?php endforeach; ?>
						</tbody>
					</table>
			<?php endif; ?>
	</div>

	<!-- 날짜별 검색 건수 -->
	<div class="stat-card">
		<h3>날짜별 검색 건수 (최근 30일)</h3>
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
