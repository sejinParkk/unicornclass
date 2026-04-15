<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Core\Csrf;
use App\Repositories\InstructorRepository;
use App\Support\FileUploader;

class InstructorController
{
    private InstructorRepository $repo;

    public function __construct()
    {
        $this->repo = new InstructorRepository();
    }

    // =========================================================================
    // GET /instructors
    // =========================================================================
    public function index(): void
    {
        $page   = max(1, (int) ($_GET['page'] ?? 1));
        $limit  = 12;
        $result = $this->repo->getPublicList($page, $limit);
        $list   = $result['list'];
        $total  = $result['total'];
        $pages  = (int) ceil($total / $limit);

        $pageTitle = '강사진 - 유니콘클래스';
        require VIEW_PATH . '/layout/header.php';
        require VIEW_PATH . '/pages/instructors/index.php';
        require VIEW_PATH . '/layout/footer.php';
    }

    // =========================================================================
    // GET /instructors/{instructor_idx}
    // =========================================================================
    public function show(string $instructor_idx): void
    {
        $instructor = $this->repo->findPublicById((int) $instructor_idx);

        if (!$instructor) {
            http_response_code(404);
            exit('강사를 찾을 수 없습니다.');
        }

        $pageTitle = htmlspecialchars($instructor['name']) . ' 강사 - 유니콘클래스';
        require VIEW_PATH . '/layout/header.php';
        require VIEW_PATH . '/pages/instructors/show.php';
        require VIEW_PATH . '/layout/footer.php';
    }

    // =========================================================================
    // GET /instructors/apply
    // 주의: 라우터에서 /instructors/apply를 /instructors/{instructor_idx}보다
    //       먼저 등록해야 'apply'가 idx로 파싱되지 않음
    // =========================================================================
    public function applyForm(): void
    {
        $csrfToken = Csrf::token();

        $pageTitle = '강사 지원하기 - 유니콘클래스';
        require VIEW_PATH . '/layout/header.php';
        require VIEW_PATH . '/pages/instructors/apply.php';
        require VIEW_PATH . '/layout/footer.php';
    }

    // =========================================================================
    // POST /instructors/apply
    // =========================================================================
    public function applyStore(): void
    {
        Csrf::verify();

        $name         = trim($_POST['name'] ?? '');
        $phone        = trim($_POST['phone'] ?? '');
        $email        = trim($_POST['email'] ?? '');
        $teachField   = trim($_POST['teach_field'] ?? '');
        $teachExp     = trim($_POST['teach_exp'] ?? '');
        $bio          = trim($_POST['bio'] ?? '');
        $curriculum   = trim($_POST['curriculum'] ?? '');
        $teachFormat  = trim($_POST['teach_format'] ?? '');
        $agree        = $_POST['agree'] ?? '';

        // 유효성 검사
        $errors = [];
        if (mb_strlen($name) < 2)        $errors[] = '이름을 2자 이상 입력해 주세요.';
        if (!preg_match('/^\d{10,11}$/', preg_replace('/[^0-9]/', '', $phone)))
                                          $errors[] = '연락처를 올바르게 입력해 주세요.';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = '이메일 형식이 올바르지 않습니다.';
        if ($teachField === '')           $errors[] = '강의 분야를 선택해 주세요.';
        if ($teachExp === '')             $errors[] = '강의 경력을 선택해 주세요.';
        if (mb_strlen($bio) < 10)        $errors[] = '자기소개를 입력해 주세요.';
        if ($curriculum === '')           $errors[] = '강의 계획을 입력해 주세요.';
        if (!in_array($teachFormat, ['free_webinar', 'paid_vod', 'mixed'], true))
                                          $errors[] = '희망 강의 형태를 선택해 주세요.';
        if ($agree !== '1')              $errors[] = '개인정보 수집 및 이용에 동의해 주세요.';

        if ($errors) {
            header('Location: /instructors/apply?error=' . urlencode(implode(' ', $errors)));
            exit;
        }

        // 지원서 저장
        $applyIdx = $this->repo->createApply([
            'name'           => $name,
            'phone'          => preg_replace('/[^0-9]/', '', $phone),
            'email'          => $email,
            'teach_field'    => $teachField,
            'teach_exp'      => $teachExp,
            'bio'            => $bio,
            'curriculum'     => $curriculum,
            'teach_format'   => $teachFormat,
            'sns_instagram'  => trim($_POST['sns_instagram'] ?? ''),
            'sns_youtube'    => trim($_POST['sns_youtube'] ?? ''),
            'sns_blog'       => trim($_POST['sns_blog'] ?? ''),
            'sns_other'      => trim($_POST['sns_other'] ?? ''),
            'portfolio_link' => trim($_POST['portfolio_link'] ?? ''),
        ]);

        // 파일 업로드 (최대 3개)
        if (!empty($_FILES['portfolio_files']['name'][0])) {
            $files     = $_FILES['portfolio_files'];
            $fileCount = 0;
            foreach ($files['name'] as $i => $name) {
                if ($fileCount >= 3) break;
                if ($files['error'][$i] === UPLOAD_ERR_NO_FILE) continue;

                $single = [
                    'name'     => $files['name'][$i],
                    'tmp_name' => $files['tmp_name'][$i],
                    'error'    => $files['error'][$i],
                    'size'     => $files['size'][$i],
                    'type'     => $files['type'][$i],
                ];

                try {
                    $uploaded = FileUploader::uploadApplyFile($single);
                    if ($uploaded) {
                        $this->repo->createApplyFile(
                            $applyIdx,
                            $uploaded['filename'],
                            $uploaded['original_name'],
                            $uploaded['size']
                        );
                        $fileCount++;
                    }
                } catch (\RuntimeException $e) {
                    // 파일 오류는 무시하고 지원서는 저장 완료
                }
            }
        }

        header('Location: /instructors/apply?success=1');
        exit;
    }
}
