<?php

namespace App\Website\Enums;

enum BlogPostStatus: string
{
    case DRAFT = 'draft';

    case PUBLISHED = 'published';

    case IN_REVIEW = 'in_review';

    case ARCHIVED = 'archived';

}
