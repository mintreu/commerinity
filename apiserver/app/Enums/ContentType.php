<?php

declare(strict_types=1);

namespace App\Enums;

enum ContentType: string
{
    case Blog = 'blog';
    case News = 'news';
}
