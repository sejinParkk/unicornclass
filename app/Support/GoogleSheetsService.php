<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Google Sheets API v4 연동 서비스
 *
 * 사용 전 준비:
 * 1. Google Cloud Console에서 서비스 계정 생성
 * 2. Google Sheets API 활성화
 * 3. 서비스 계정 JSON 키 파일 다운로드 → 경로를 .env GOOGLE_SHEETS_CREDENTIALS_PATH 에 설정
 * 4. 대상 스프레드시트에 서비스 계정 이메일을 편집자로 공유
 * 5. .env GOOGLE_SHEETS_SPREADSHEET_ID 에 스프레드시트 ID 설정
 */
class GoogleSheetsService
{
    private string $credentialsPath;
    private string $spreadsheetId;
    private string $sheetName;

    private const TOKEN_URI   = 'https://oauth2.googleapis.com/token';
    private const SHEETS_URI  = 'https://sheets.googleapis.com/v4/spreadsheets';
    private const SCOPE       = 'https://www.googleapis.com/auth/spreadsheets';

    public function __construct()
    {
        $this->credentialsPath = $_ENV['GOOGLE_SHEETS_CREDENTIALS_PATH'] ?? '';
        $this->spreadsheetId   = $_ENV['GOOGLE_SHEETS_SPREADSHEET_ID']   ?? '';
        $this->sheetName       = $_ENV['GOOGLE_SHEETS_SHEET_NAME']        ?? '수강신청';
    }

    /**
     * 설정이 유효한지 확인 (키 파일 경로·스프레드시트 ID 모두 세팅된 경우)
     */
    public function isConfigured(): bool
    {
        return $this->credentialsPath !== ''
            && $this->spreadsheetId   !== ''
            && file_exists($this->credentialsPath);
    }

    /**
     * 무료강의 수강 신청 내역을 시트에 한 행 추가
     *
     * @param array{
     *   member_idx: int,
     *   name: string,
     *   email: string,
     *   phone: string,
     *   class_idx: int,
     *   class_title: string,
     *   enrolled_at: string
     * } $data
     */
    public function appendEnrollRow(array $data): void
    {
        if (!$this->isConfigured()) {
            error_log('[GoogleSheets] 설정이 완료되지 않았습니다. .env를 확인하세요.');
            return;
        }

        $row = [
            $data['enrolled_at'],
            $data['member_idx'],
            $data['name'],
            $data['email'],
            $data['phone'] ?? '',
            $data['class_idx'],
            $data['class_title'],
        ];

        $this->appendRow($row);
    }

    // -------------------------------------------------------------------------

    /**
     * Sheets API append 호출
     *
     * @param list<string|int> $row
     */
    private function appendRow(array $row): void
    {
        $accessToken = $this->getAccessToken();

        $range   = urlencode($this->sheetName);
        $url     = self::SHEETS_URI
                 . "/{$this->spreadsheetId}/values/{$range}:append"
                 . '?valueInputOption=USER_ENTERED&insertDataOption=INSERT_ROWS';

        $body = json_encode([
            'values' => [$row],
        ]);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $body,
            CURLOPT_HTTPHEADER     => [
                'Authorization: Bearer ' . $accessToken,
                'Content-Type: application/json',
            ],
            CURLOPT_TIMEOUT        => 10,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr  = curl_error($ch);
        curl_close($ch);

        if ($curlErr) {
            error_log('[GoogleSheets] cURL 오류: ' . $curlErr);
            return;
        }

        if ($httpCode !== 200) {
            error_log('[GoogleSheets] API 응답 오류 HTTP ' . $httpCode . ' — ' . $response);
        }
    }

    /**
     * 서비스 계정 JSON 키로 JWT를 만들어 Access Token 발급
     */
    private function getAccessToken(): string
    {
        $credentials = json_decode(file_get_contents($this->credentialsPath), true);

        $now = time();
        $header  = $this->base64url(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
        $payload = $this->base64url(json_encode([
            'iss'   => $credentials['client_email'],
            'scope' => self::SCOPE,
            'aud'   => self::TOKEN_URI,
            'exp'   => $now + 3600,
            'iat'   => $now,
        ]));

        $signingInput = $header . '.' . $payload;
        openssl_sign($signingInput, $signature, $credentials['private_key'], 'SHA256');
        $jwt = $signingInput . '.' . $this->base64url($signature);

        $ch = curl_init(self::TOKEN_URI);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => http_build_query([
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion'  => $jwt,
            ]),
            CURLOPT_TIMEOUT        => 10,
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($response, true);

        if (empty($data['access_token'])) {
            throw new \RuntimeException('[GoogleSheets] Access Token 발급 실패: ' . $response);
        }

        return $data['access_token'];
    }

    private function base64url(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
