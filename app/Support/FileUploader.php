<?php

declare(strict_types=1);

namespace App\Support;

class FileUploader
{
    private const ALLOWED_IMAGE_MIMES = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
    ];

    private const ALLOWED_MATERIAL_MIMES = [
        'application/pdf'                                                                 => 'pdf',
        'application/msword'                                                              => 'doc',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document'        => 'docx',
        'application/vnd.ms-excel'                                                        => 'xls',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'              => 'xlsx',
        'application/vnd.ms-powerpoint'                                                   => 'ppt',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation'      => 'pptx',
        'application/zip'                                                                  => 'zip',
        'application/x-zip-compressed'                                                    => 'zip',
        'image/jpeg'                                                                       => 'jpg',
        'image/png'                                                                        => 'png',
    ];

    // -------------------------------------------------------------------------
    // 썸네일
    // -------------------------------------------------------------------------

    /**
     * @throws \RuntimeException
     */
    public static function uploadClassThumbnail(array $file): ?string
    {
        if (empty($file['tmp_name']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
            return null;
        }
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new \RuntimeException('파일 업로드 중 오류가 발생했습니다. (code: ' . $file['error'] . ')');
        }
        if ($file['size'] > 5 * 1024 * 1024) {
            throw new \RuntimeException('파일 크기는 5MB 이하여야 합니다.');
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime  = $finfo->file($file['tmp_name']);

        if (!array_key_exists($mime, self::ALLOWED_IMAGE_MIMES)) {
            throw new \RuntimeException('jpg, png, webp 형식만 업로드할 수 있습니다.');
        }

        $ext      = self::ALLOWED_IMAGE_MIMES[$mime];
        $filename = bin2hex(random_bytes(16)) . '.' . $ext;
        $savePath = ROOT_PATH . '/storage/uploads/class/' . $filename;

        if (!move_uploaded_file($file['tmp_name'], $savePath)) {
            throw new \RuntimeException('파일 저장에 실패했습니다.');
        }

        return $filename;
    }

    public static function deleteClassThumbnail(?string $filename): void
    {
        if (!$filename) return;
        $filename = basename($filename);
        $path = ROOT_PATH . '/storage/uploads/class/' . $filename;
        if (file_exists($path)) @unlink($path);
    }

    // -------------------------------------------------------------------------
    // 강의 자료 파일
    // -------------------------------------------------------------------------

    /**
     * 강의 자료 파일 업로드.
     * 성공 시 ['filename' => string, 'size' => int] 반환.
     *
     * @throws \RuntimeException
     */
    public static function uploadClassMaterial(array $file): array
    {
        if (empty($file['tmp_name']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
            return [];
        }
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new \RuntimeException('파일 업로드 오류 (code: ' . $file['error'] . ')');
        }
        if ($file['size'] > 50 * 1024 * 1024) {
            throw new \RuntimeException('파일 크기는 50MB 이하여야 합니다.');
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime  = $finfo->file($file['tmp_name']);

        if (!array_key_exists($mime, self::ALLOWED_MATERIAL_MIMES)) {
            throw new \RuntimeException('허용되지 않는 파일 형식입니다. (pdf, doc, xls, ppt, zip, jpg, png)');
        }

        $ext     = self::ALLOWED_MATERIAL_MIMES[$mime];
        $filename = bin2hex(random_bytes(16)) . '.' . $ext;
        $dir      = ROOT_PATH . '/storage/uploads/materials/';

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        if (!move_uploaded_file($file['tmp_name'], $dir . $filename)) {
            throw new \RuntimeException('파일 저장에 실패했습니다.');
        }

        return ['filename' => $filename, 'size' => (int) $file['size']];
    }

    public static function deleteClassMaterial(?string $filename): void
    {
        if (!$filename) return;
        $filename = basename($filename);
        $path = ROOT_PATH . '/storage/uploads/materials/' . $filename;
        if (file_exists($path)) @unlink($path);
    }

    // -------------------------------------------------------------------------
    // 강사 프로필 사진
    // -------------------------------------------------------------------------

    public static function uploadInstructorPhoto(array $file): ?string
    {
        if (empty($file['tmp_name']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
            return null;
        }
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new \RuntimeException('파일 업로드 중 오류가 발생했습니다. (code: ' . $file['error'] . ')');
        }
        if ($file['size'] > 5 * 1024 * 1024) {
            throw new \RuntimeException('파일 크기는 5MB 이하여야 합니다.');
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime  = $finfo->file($file['tmp_name']);

        if (!array_key_exists($mime, self::ALLOWED_IMAGE_MIMES)) {
            throw new \RuntimeException('jpg, png, webp 형식만 업로드할 수 있습니다.');
        }

        $ext      = self::ALLOWED_IMAGE_MIMES[$mime];
        $filename = bin2hex(random_bytes(16)) . '.' . $ext;
        $dir      = ROOT_PATH . '/storage/uploads/instructor/';

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        if (!move_uploaded_file($file['tmp_name'], $dir . $filename)) {
            throw new \RuntimeException('파일 저장에 실패했습니다.');
        }

        return $filename;
    }

    public static function deleteInstructorPhoto(?string $filename): void
    {
        if (!$filename) return;
        $filename = basename($filename);
        $path = ROOT_PATH . '/storage/uploads/instructor/' . $filename;
        if (file_exists($path)) @unlink($path);
    }

    // -------------------------------------------------------------------------
    // 사이트 이미지 (로고, 파비콘)
    // -------------------------------------------------------------------------

    /**
     * 로고/파비콘 업로드. jpg·png·webp·ico·svg 허용, 최대 2MB.
     * @throws \RuntimeException
     */
    public static function uploadSiteImage(array $file): ?string
    {
        if (empty($file['tmp_name']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
            return null;
        }
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new \RuntimeException('파일 업로드 중 오류가 발생했습니다. (code: ' . $file['error'] . ')');
        }
        if ($file['size'] > 2 * 1024 * 1024) {
            throw new \RuntimeException('파일 크기는 2MB 이하여야 합니다.');
        }

        $allowed = [
            'image/jpeg'     => 'jpg',
            'image/png'      => 'png',
            'image/webp'     => 'webp',
            'image/x-icon'   => 'ico',
            'image/vnd.microsoft.icon' => 'ico',
            'image/svg+xml'  => 'svg',
        ];

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime  = $finfo->file($file['tmp_name']);

        if (!array_key_exists($mime, $allowed)) {
            throw new \RuntimeException('jpg, png, webp, ico, svg 형식만 업로드할 수 있습니다.');
        }

        $ext      = $allowed[$mime];
        $filename = bin2hex(random_bytes(16)) . '.' . $ext;
        $dir      = ROOT_PATH . '/storage/uploads/site/';

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        if (!move_uploaded_file($file['tmp_name'], $dir . $filename)) {
            throw new \RuntimeException('파일 저장에 실패했습니다.');
        }

        return $filename;
    }

    public static function deleteSiteImage(?string $filename): void
    {
        if (!$filename) return;
        $filename = basename($filename);
        $path = ROOT_PATH . '/storage/uploads/site/' . $filename;
        if (file_exists($path)) @unlink($path);
    }

    // -------------------------------------------------------------------------
    // 히어로 배너 동영상 (mp4)
    // -------------------------------------------------------------------------

    /**
     * 히어로 배너용 mp4 업로드. 최대 200MB.
     * @throws \RuntimeException
     */
    public static function uploadSiteVideo(array $file): ?string
    {
        if (empty($file['tmp_name']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
            return null;
        }
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new \RuntimeException('파일 업로드 중 오류가 발생했습니다. (code: ' . $file['error'] . ')');
        }
        if ($file['size'] > 200 * 1024 * 1024) {
            throw new \RuntimeException('파일 크기는 200MB 이하여야 합니다.');
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime  = $finfo->file($file['tmp_name']);

        if ($mime !== 'video/mp4') {
            throw new \RuntimeException('mp4 형식만 업로드할 수 있습니다.');
        }

        $filename = bin2hex(random_bytes(16)) . '.mp4';
        $dir      = ROOT_PATH . '/storage/uploads/site/';

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        if (!move_uploaded_file($file['tmp_name'], $dir . $filename)) {
            throw new \RuntimeException('파일 저장에 실패했습니다.');
        }

        return $filename;
    }

    public static function deleteSiteVideo(?string $filename): void
    {
        if (!$filename) return;
        $filename = basename($filename);
        $path = ROOT_PATH . '/storage/uploads/site/' . $filename;
        if (file_exists($path)) @unlink($path);
    }

    // -------------------------------------------------------------------------
    // 이벤트 배너 이미지
    // -------------------------------------------------------------------------

    /**
     * 메인 이벤트 배너 이미지 업로드. jpg·png·webp 허용, 최대 5MB.
     * @throws \RuntimeException
     */
    public static function uploadBannerImage(array $file): ?string
    {
        if (empty($file['tmp_name']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
            return null;
        }
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new \RuntimeException('파일 업로드 중 오류가 발생했습니다. (code: ' . $file['error'] . ')');
        }
        if ($file['size'] > 5 * 1024 * 1024) {
            throw new \RuntimeException('파일 크기는 5MB 이하여야 합니다.');
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime  = $finfo->file($file['tmp_name']);

        if (!array_key_exists($mime, self::ALLOWED_IMAGE_MIMES)) {
            throw new \RuntimeException('jpg, png, webp 형식만 업로드할 수 있습니다.');
        }

        $ext      = self::ALLOWED_IMAGE_MIMES[$mime];
        $filename = bin2hex(random_bytes(16)) . '.' . $ext;
        $dir      = ROOT_PATH . '/storage/uploads/banner/';

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        if (!move_uploaded_file($file['tmp_name'], $dir . $filename)) {
            throw new \RuntimeException('파일 저장에 실패했습니다.');
        }

        return $filename;
    }

    public static function deleteBannerImage(?string $filename): void
    {
        if (!$filename) return;
        $filename = basename($filename);
        $path = ROOT_PATH . '/storage/uploads/banner/' . $filename;
        if (file_exists($path)) @unlink($path);
    }

    // -------------------------------------------------------------------------
    // 팝업 이미지
    // -------------------------------------------------------------------------

    /**
     * 메인 팝업 이미지 업로드. jpg·png·webp 허용, 최대 5MB.
     * @throws \RuntimeException
     */
    public static function uploadPopupImage(array $file): ?string
    {
        if (empty($file['tmp_name']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
            return null;
        }
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new \RuntimeException('파일 업로드 중 오류가 발생했습니다. (code: ' . $file['error'] . ')');
        }
        if ($file['size'] > 5 * 1024 * 1024) {
            throw new \RuntimeException('파일 크기는 5MB 이하여야 합니다.');
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime  = $finfo->file($file['tmp_name']);

        if (!array_key_exists($mime, self::ALLOWED_IMAGE_MIMES)) {
            throw new \RuntimeException('jpg, png, webp 형식만 업로드할 수 있습니다.');
        }

        $ext      = self::ALLOWED_IMAGE_MIMES[$mime];
        $filename = bin2hex(random_bytes(16)) . '.' . $ext;
        $dir      = ROOT_PATH . '/storage/uploads/popup/';

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        if (!move_uploaded_file($file['tmp_name'], $dir . $filename)) {
            throw new \RuntimeException('파일 저장에 실패했습니다.');
        }

        return $filename;
    }

    public static function deletePopupImage(?string $filename): void
    {
        if (!$filename) return;
        $filename = basename($filename);
        $path = ROOT_PATH . '/storage/uploads/popup/' . $filename;
        if (file_exists($path)) @unlink($path);
    }

    // -------------------------------------------------------------------------
    // 강사 지원 포트폴리오 파일
    // -------------------------------------------------------------------------

    private const ALLOWED_APPLY_MIMES = [
        'application/pdf'                                                                => 'pdf',
        'application/vnd.ms-powerpoint'                                                  => 'ppt',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation'     => 'pptx',
        'image/jpeg'                                                                     => 'jpg',
        'image/png'                                                                      => 'png',
    ];

    /**
     * 강사 지원 포트폴리오 파일 업로드. 최대 20MB, PDF/PPT/JPG/PNG 허용.
     * @return array{filename: string, original_name: string, size: int}
     * @throws \RuntimeException
     */
    public static function uploadApplyFile(array $file): array
    {
        if (empty($file['tmp_name']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
            return [];
        }
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new \RuntimeException('파일 업로드 오류 (code: ' . $file['error'] . ')');
        }
        if ($file['size'] > 20 * 1024 * 1024) {
            throw new \RuntimeException('파일 크기는 20MB 이하여야 합니다.');
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime  = $finfo->file($file['tmp_name']);

        if (!array_key_exists($mime, self::ALLOWED_APPLY_MIMES)) {
            throw new \RuntimeException('PDF, PPT, PPTX, JPG, PNG 파일만 업로드할 수 있습니다.');
        }

        $ext      = self::ALLOWED_APPLY_MIMES[$mime];
        $filename = bin2hex(random_bytes(16)) . '.' . $ext;
        $dir      = ROOT_PATH . '/storage/uploads/apply/';

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        if (!move_uploaded_file($file['tmp_name'], $dir . $filename)) {
            throw new \RuntimeException('파일 저장에 실패했습니다.');
        }

        return [
            'filename'      => $filename,
            'original_name' => $file['name'],
            'size'          => (int) $file['size'],
        ];
    }
}
