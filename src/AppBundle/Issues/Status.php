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

    const LEGACY_BRANCH = '1.6.1.x';

    const CURRENT_BRANCH = '1.7.x';

    const BRANCH_173 = '1.7.3.x';

    const BRANCH_174 = '1.7.4.x';

    const BRANCH_175 = '1.7.5.x';

    const BRANCH_176 = '1.7.6.x';

    const BRANCH_177 = '1.7.7.x';

    const BRANCH_178 = '1.7.8.x';

    const BRANCH_179 = '1.7.9.x';

    const FUTURE_BRANCH = '1.8.x';

    const DEVELOP_BRANCH = 'develop';

    public static $branches = [
        self::LEGACY_BRANCH,
        self::CURRENT_BRANCH,
        self::BRANCH_173,
        self::BRANCH_174,
        self::BRANCH_175,
        self::BRANCH_176,
        self::BRANCH_177,
        self::BRANCH_178,
        self::BRANCH_179,
        self::FUTURE_BRANCH,
        self::DEVELOP_BRANCH,
    ];
}
