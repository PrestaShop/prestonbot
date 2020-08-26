<?php

namespace AppBundle\PullRequests;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Extract human readable data from Pull request body.
 */
class BodyParser
{
    const DEFAULT_PATTERN = '~(?:\|\s+%s\?\s+\|\s+)(%s)\s+~';

    /**
     * @var string
     */
    private $bodyContent;

    public function __construct(string $bodyContent)
    {
        $this->bodyContent = $bodyContent;
    }

    /**
     * @return string
     */
    public function getBranch()
    {
        $regex = sprintf(self::DEFAULT_PATTERN, 'Branch', '.+');

        return $this->extractWithRegex($regex);
    }

    /**
     * @return string
     */
    public function getBody()
    {
        return $this->bodyContent;
    }

    /**
     * @Assert\NotBlank(message = "The `description` shouldn't be empty.")
     *
     * @return string
     */
    public function getDescription()
    {
        $regex = sprintf(self::DEFAULT_PATTERN, 'Description', '[^|]+');

        return $this->extractWithRegex($regex);
    }

    /**
     * @Assert\Choice(callback = "getValidTypes",
     * message = "The `type` should be one of: `new feature`, `improvement`, `bug fix`, `refacto` or `critical`.",
     * strict=true)
     *
     * @return string
     */
    public function getType()
    {
        $regex = sprintf(self::DEFAULT_PATTERN, 'Type', '\w+(?:\s\w+)?');

        return $this->extractWithRegex($regex);
    }

    /**
     * @Assert\Choice(choices = {"FO", "BO", "CO", "IN", "TE", "WS", "LO", "ME", "PM"},
     * message = "The `category` should be one of: `FO`, `BO`, `CO`, `IN`, `TE`, `WS`, `LO`, `ME`, `PM`",
     * strict=true)
     *
     * @return string
     */
    public function getCategory()
    {
        $regex = sprintf(self::DEFAULT_PATTERN, 'Category', '.+');

        return $this->extractWithRegex($regex);
    }

    /**
     * @return bool
     */
    public function isBackwardCompatible()
    {
        $regex = sprintf(self::DEFAULT_PATTERN, 'BC breaks', '.+');
        $backwardCompatible = $this->extractWithRegex($regex);

        return 'yes' === $backwardCompatible;
    }

    /**
     * @return bool
     */
    public function willDeprecateCode()
    {
        $regex = sprintf(self::DEFAULT_PATTERN, 'Deprecations', '.+');
        $willDeprecateCode = $this->extractWithRegex($regex);

        return 'no' === $willDeprecateCode;
    }

    /**
     * @return bool
     */
    public function isAFeature()
    {
        return 1 === preg_match('/feature/', $this->getType());
    }

    /**
     * @return bool
     */
    public function isAnImprovement()
    {
        return 1 === preg_match('/improvement/', $this->getType());
    }

    /**
     * @return bool
     */
    public function isABugFix()
    {
        return 1 === preg_match('/bug fix/', $this->getType());
    }

    /**
     * @return bool
     */
    public function isASmallFix()
    {
        return 1 === preg_match('/small fix/', $this->getType());
    }

    /**
     * @return bool
     */
    public function isARefacto()
    {
        return 1 === preg_match('/refacto/', $this->getType());
    }

    /**
     * @Assert\NotBlank(message = "Your pull request does not seem to fix any issue, you might consider [creating one](https://github.com/PrestaShop/PrestaShop/issues/new/choose) (see note below).")
     *
     * @return string
     */
    public function getRelatedTicket()
    {
        $regex = sprintf(self::DEFAULT_PATTERN, 'Fixed ticket', '?:.*(?:#|\/issues\/)([0-9]+)\.?');
        $ticket = $this->extractWithRegex($regex);

        return empty($ticket) ? '' : '#'.$ticket;
    }

    /**
     * @return string
     */
    public function getTestingScenario()
    {
        $regex = sprintf(self::DEFAULT_PATTERN, 'How to test', '.+');

        return $this->extractWithRegex($regex);
    }

    /**
     * @return array
     */
    public static function getValidTypes()
    {
        return [
            'feature',
            'new feature',
            'improvement',
            'fix',
            'refacto',
            'bug fix',
            'small fix',
            'critical',
        ];
    }

    /**
     * @param string $regex
     *
     * @return string
     */
    private function extractWithRegex(string $regex)
    {
        preg_match($regex, $this->getBody(), $matches);

        return trim(isset($matches[1]) ? $matches[1] : '');
    }
}
