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
}
