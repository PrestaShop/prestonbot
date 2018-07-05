<?php

namespace AppBundle\PullRequests;

/**
 * Describe all PrestaShop labels.
 */
final class Labels
{
    const WAITING_FOR_CODE_REVIEW = 'waiting for code review';

    const WAITING_FOR_QA_FEEDBACK = 'waiting for QA feedback';

    const WAITING_FOR_PM_FEEDBACK = 'waiting for PM feedback';

    const FEATURE = 'Feature';

    const BUG = 'Bug';

    const IMPROVEMENT = 'Improvement';

    // Help to prevent changes in the future
    const ALIASES = [
        'new feature' => self::FEATURE,
        'bug fix' => self::BUG,
        'improvement' => self::IMPROVEMENT,
    ];
}
