<?php

namespace App\Website\Enums;

enum BlogStatusEnum: string
{
    case DRAFT = 'Draft';

    case PUBLISHED = 'Published';

    case IN_REVIEW = 'In Review';

    case ARCHIVED = 'Archived';

}
