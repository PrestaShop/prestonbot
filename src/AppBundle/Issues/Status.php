<?php

namespace AppBundle\Issues;

/**
 * The possible statuses of an issue.
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
final class Status
{
    const NEEDS_REVIEW = 'needs_review';

    const CODE_REVIEWED = 'code_reviewed';

    const QA_APPROVED = 'qa_approved';

    const PM_APPROVED = 'pm_approved';

    const WAITING_FOR_WORDING = 'waiting_for_wording';

    const CRITICAL_ISSUE = 'critical_issue';

    const REPORT_ON_STARTER_THEME = 'report_on_starter_theme';

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
}
