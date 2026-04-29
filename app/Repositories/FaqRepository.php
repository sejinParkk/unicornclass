<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\DB;

class FaqRepository
{
    public function getList(string $category = ''): array
    {
        if ($category !== '') {
            return DB::select(
                'SELECT * FROM lc_faq WHERE category = ? AND deleted_at IS NULL ORDER BY sort_order ASC, faq_idx ASC',
                [$category]
            );
        }
        return DB::select('SELECT * FROM lc_faq WHERE deleted_at IS NULL ORDER BY sort_order ASC, faq_idx ASC');
    }

    public function findByIdx(int $faqIdx): ?array
    {
        return DB::selectOne('SELECT * FROM lc_faq WHERE faq_idx = ? AND deleted_at IS NULL', [$faqIdx]);
    }

    public function create(array $data): int
    {
        return (int) DB::insert(
            'INSERT INTO lc_faq (category, question, answer, sort_order, is_active) VALUES (?, ?, ?, ?, ?)',
            [$data['category'], $data['question'], $data['answer'],
             (int) ($data['sort_order'] ?? 0), (int) ($data['is_active'] ?? 1)]
        );
    }

    public function update(int $faqIdx, array $data): void
    {
        DB::execute(
            'UPDATE lc_faq SET category = ?, question = ?, answer = ?, sort_order = ?, is_active = ? WHERE faq_idx = ?',
            [$data['category'], $data['question'], $data['answer'],
             (int) ($data['sort_order'] ?? 0), (int) ($data['is_active'] ?? 1), $faqIdx]
        );
    }

    public function delete(int $faqIdx): void
    {
        DB::execute('UPDATE lc_faq SET deleted_at = NOW() WHERE faq_idx = ? AND deleted_at IS NULL', [$faqIdx]);
    }

    public function getCategories(): array
    {
        return ['all' => '전체', 'payment' => '결제/환불', 'lecture' => '강의', 'account' => '계정', 'tech' => '기술지원', 'etc' => '기타'];
    }
}
