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
}
