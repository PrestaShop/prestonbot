<?php

namespace AppBundle\PullRequests;

use Symfony\Component\Validator\Constraints as Assert;
use Exception;

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
     * message = "The `type` should be one of: `new feature`, `improvement`, `bug fix`, `refacto`.",
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

        return $backwardCompatible == 'yes' ? true : false;
    }

    /**
     * @return bool
     */
    public function willDeprecateCode()
    {
        $regex = "/(\|[[:space:]]Deprecations\?[[:space:]]+\|[[:space:]])(.+)\r\n/";
        $willDeprecateCode = $this->extractWithRegex($regex);

        return $willDeprecateCode == 'no' ? false : true;
    }

    /**
     * @return bool
     */
    public function isAFeature()
    {
        return true === preg_match('/feature/', $this->getType());
    }

    /**
     * @return bool
     */
    public function isAnImprovement()
    {
        return true === preg_match('/improvement/', $this->getType());
    }

    /**
     * @return bool
     */
    public function isABugFix()
    {
        return true === preg_match('/bug fix/', $this->getType());
    }

    /**
     * @return bool
     */
    public function isASmallFix()
    {
        return true === preg_match('/small fix/', $this->getType());
    }

    /**
     * @return bool
     */
    public function isARefacto()
    {
        return true === preg_match('/refacto/', $this->getType());
    }

    /**
     * @throws Exception
     */
    public function getRelatedForgeIssue()
    {
        throw new Exception('Need to be done');
    }

    /**
     * @throws Exception
     */
    public function getTestingScenario()
    {
        throw new Exception('Need to be done');
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
        ];
    }
}
