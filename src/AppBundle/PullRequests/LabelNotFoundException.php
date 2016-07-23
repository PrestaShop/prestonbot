<?php

namespace AppBundle\PullRequests;

use Exception;

/**
 * @author Mickaël Andrieu <andrieu.travail@gmail.com>
 */
class LabelNotFoundException extends Exception
{
    public function __construct($message = '')
    {
        $message = sprintf('The label `%s` is not found or configurated.', $message);
        parent::__construct($message, 0, null);
    }
}
