<?php

namespace AppBundle\PullRequests;

use Exception;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Extract human readable data from Pull request body.
 */
class BodyParser
{
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
        $regex = "/(\|[[:space:]]Branch\?[[:space:]]+\|[[:space:]])(.+)\r\n/";

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
        $regex = "/(\|[[:space:]]Description\?[[:space:]]+\|[[:space:]])(.+)\r\n/";

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
        $regex = "/(\|[[:space:]]Type\?[[:space:]]+\|[[:space:]]*)(\S+\s?\S*)[[:space:]]*\r\n/";

        return $this->extractWithRegex($regex);
    }

    /**
     * @Assert\Choice(choices = {"FO", "BO", "CO", "IN", "TE", "WS", "LO"},
     * message = "The `category` should be one of: `FO`, `BO`, `CO`, `IN`, `TE`, `WS`, `LO`",
     * strict=true)
     *
     * @return string
     */
    public function getCategory()
    {
        $regex = "/(\|[[:space:]]Category\?[[:space:]]+\|[[:space:]])(.+)\r\n/";

        return $this->extractWithRegex($regex);
    }

    /**
     * @return bool
     */
    public function isBackwardCompatible()
    {
        $regex = "/(\|[[:space:]]BC breaks\?[[:space:]]+\|[[:space:]])(.+)\r\n/";
        $backwardCompatible = $this->extractWithRegex($regex);

        return 'yes' === $backwardCompatible;
    }

    /**
     * @return bool
     */
    public function willDeprecateCode()
    {
        $regex = "/(\|[[:space:]]Deprecations\?[[:space:]]+\|[[:space:]])(.+)\r\n/";
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
     * @return string
     */
    public function getRelatedForgeIssue()
    {
        $regex = "/(\|[[:space:]]Fixed ticket\?[[:space:]]+\|[[:space:]])(.+)\r\n/";

        return $this->extractWithRegex($regex);
    }

    /**
     * @throws Exception
     */
    public function getTestingScenario()
    {
        throw new Exception('Need to be done');
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
     * @param $regex
     *
     * @return string
     */
    private function extractWithRegex($regex)
    {
        preg_match($regex, $this->getBody(), $matches);

        return isset($matches[2]) ? $matches[2] : '';
    }
}
