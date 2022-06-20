<?php

namespace AppBundle\Issues;

/**
 * The possible statuses of an issue.
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
final class Status
{
    const NEEDS_REVIEW = 'Status: Needs Review';

    const CODE_REVIEWED = 'Code reviewed';

    const QA_APPROVED = 'QA-approved';

    const PM_APPROVED = 'PM-approved';

    const WAITING_FOR_WORDING = 'waiting for wording';

    const REPORT_ON_STARTER_THEME = 'Needs port on StarterTheme';

    const BRANCH_177 = '1.7.7.x';

    const BRANCH_178 = '1.7.8.x';

    const BRANCH_80 = '8.0.x';

    const DEVELOP_BRANCH = 'develop';

    public static $branches = [
        self::BRANCH_177,
        self::BRANCH_178,
        self::BRANCH_80,
        self::DEVELOP_BRANCH,
    ];
}
