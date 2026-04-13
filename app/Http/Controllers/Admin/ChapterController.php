<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Core\Auth;
use App\Core\Csrf;
use App\Repositories\ChapterRepository;
use App\Repositories\ClassRepository;

class ChapterController
{
    private ChapterRepository $chapterRepo;
    private ClassRepository   $classRepo;

    public function __construct()
    {
        Auth::requireAdmin();
        $this->chapterRepo = new ChapterRepository();
        $this->classRepo   = new ClassRepository();
    }

    // POST /admin/api/chapters
    public function store(): void
    {
        Csrf::verify();
        header('Content-Type: application/json; charset=utf-8');

        $classIdx = (int) ($_POST['class_idx'] ?? 0);
        $title    = trim($_POST['title'] ?? '');

        if (!$classIdx || !$title) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => '필수 항목을 입력해주세요.']);
            return;
        }

        if (!$this->classRepo->findById($classIdx)) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => '강의를 찾을 수 없습니다.']);
            return;
        }

        $chapterIdx = $this->chapterRepo->create($classIdx, [
            'title'     => $title,
            'vimeo_url' => trim($_POST['vimeo_url'] ?? ''),
            'duration'  => trim($_POST['duration'] ?? '0:00'),
        ]);
        $this->classRepo->syncTotalEpisodes($classIdx);

        $chapter = $this->chapterRepo->findById($chapterIdx);
        $chapter['duration_display'] = ChapterRepository::secondsToDisplay((int)$chapter['duration']);

        echo json_encode(['success' => true, 'data' => $chapter]);
    }

    // POST /admin/api/chapters/{idx}/update
    public function update(string $chapterIdx): void
    {
        Csrf::verify();
        header('Content-Type: application/json; charset=utf-8');

        $chapterIdx = (int) $chapterIdx;
        $chapter    = $this->chapterRepo->findById($chapterIdx);
        $title      = trim($_POST['title'] ?? '');

        if (!$chapter) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => '챕터를 찾을 수 없습니다.']);
            return;
        }
        if (!$title) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => '제목을 입력해주세요.']);
            return;
        }

        $this->chapterRepo->update($chapterIdx, [
            'title'     => $title,
            'vimeo_url' => trim($_POST['vimeo_url'] ?? ''),
            'duration'  => trim($_POST['duration'] ?? '0:00'),
        ]);

        $updated = $this->chapterRepo->findById($chapterIdx);
        $updated['duration_display'] = ChapterRepository::secondsToDisplay((int)$updated['duration']);

        echo json_encode(['success' => true, 'data' => $updated]);
    }

    // POST /admin/api/chapters/{idx}/delete
    public function destroy(string $chapterIdx): void
    {
        Csrf::verify();
        header('Content-Type: application/json; charset=utf-8');

        $chapterIdx = (int) $chapterIdx;
        $chapter    = $this->chapterRepo->findById($chapterIdx);

        if (!$chapter) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => '챕터를 찾을 수 없습니다.']);
            return;
        }

        $this->chapterRepo->delete($chapterIdx);
        $this->classRepo->syncTotalEpisodes((int) $chapter['class_idx']);

        echo json_encode(['success' => true]);
    }

    // POST /admin/api/chapters/reorder
    public function reorder(): void
    {
        Csrf::verify();
        header('Content-Type: application/json; charset=utf-8');

        // orders: JSON array of {chapter_idx, sort_order}
        $body   = json_decode(file_get_contents('php://input'), true);
        $orders = $body['orders'] ?? [];

        if (!is_array($orders)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => '잘못된 요청입니다.']);
            return;
        }

        $pairs = array_map(
            fn($o) => [(int)$o['chapter_idx'], (int)$o['sort_order']],
            $orders
        );
        $this->chapterRepo->updateSortOrders($pairs);

        echo json_encode(['success' => true]);
    }
}
