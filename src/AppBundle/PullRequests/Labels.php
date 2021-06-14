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

    const BUG = 'Bug fix';

    const IMPROVEMENT = 'Improvement';

    const REFACTORING = 'Refactoring';

    const QA_APPROVED = 'QA ✔️';

    const WORDING_APPROVED = 'Wording ✔️';

    const BC_BREAK = 'BC break';

    public const PR_AVAILABLE = 'PR available';

    // Help to prevent changes in the future
    const ALIASES = [
        'bug fix' => self::BUG,
        'improvement' => self::IMPROVEMENT,
        'new feature' => self::FEATURE,
        'refacto' => self::REFACTORING,
    ];
}
