<?php

declare(strict_types=1);

namespace App\Http\Controllers;

class AboutController
{
    public function index(): void
    {
        $pageTitle   = '회사소개 - 유니콘클래스';
        $extraStyles = '<link rel="stylesheet" href="/assets/css/about.css">';

        require VIEW_PATH . '/layout/header.php';
        require VIEW_PATH . '/pages/about.php';
        require VIEW_PATH . '/layout/footer.php';
    }
}
