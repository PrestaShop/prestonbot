<?php

namespace AppBundle\PullRequests;

use Symfony\Component\Validator\Constraints as Assert;
use \Exception;

/**
 * Extract human readable data from Pull request body
 */
class BodyParser
{
    private $bodyContent;
    
    public function __construct($bodyContent)
    {
        $this->bodyContent = $bodyContent;
    }
    
    public function getBranch()
    {
        $regex = "/(\|[[:space:]]Branch\?[[:space:]]+\|[[:space:]])(.+)\r\n/";
        
        return $this->extractWithRegex($regex);
    }
    
    public function getBody()
    {
        return $this->bodyContent;
    }
    
    /**
     * @Assert\NotBlank(message = "The `description` shouldn't be empty.")
     */
    public function getDescription()
    {
        $regex = "/(\|[[:space:]]Description\?[[:space:]]+\|[[:space:]])(.+)\r\n/";
        
        return $this->extractWithRegex($regex);
    }
    
    /**
     * @Assert\Choice(choices = {"feature", "improvement", "fix"},
     * message = "The `type` should be one of: `new feature`, `improvement`, `bug fix`.")
     */
    public function getType()
    {
        $regex = "/(\|[[:space:]]Type\?[[:space:]]+\|[[:space:]])(.+)\r\n/";
        
        return $this->extractWithRegex($regex);
    }
    
    public function getCategory()
    {
        $regex = "/(\|[[:space:]]Category\?[[:space:]]+\|[[:space:]])(.+)\r\n/";
        
        return $this->extractWithRegex($regex);
    }
    
    public function isBackwardCompatible()
    {
        $regex = "/(\|[[:space:]]BC breaks\?[[:space:]]+\|[[:space:]])(.+)\r\n/";
        $backwardCompatible = $this->extractWithRegex($regex);
        
        return $backwardCompatible == 'yes' ? true : false;
    }
    
    public function willDeprecateCode()
    {
        $regex = "/(\|[[:space:]]Deprecations\?[[:space:]]+\|[[:space:]])(.+)\r\n/";
        $willDeprecateCode = $this->extractWithRegex($regex);
        
        return $willDeprecateCode == 'no' ? false : true;
    }
    
    public function isAFeature()
    {
        return preg_match('/feature/', $this->getType()) == true;
    }
    
    public function isAnImprovement()
    {
        return preg_match('/improvement/', $this->getType()) == true;
    }
    
    public function isABugFix()
    {
        return preg_match('/bug fix/', $this->getType()) == true;
    }
    
    public function getRelatedForgeIssue()
    {
        throw new Exception('Need to be done');
    }
    
    public function getTestingScenario()
    {
        throw new Exception('Need to be done');
    }
    
    private function extractWithRegex($regex)
    {
        preg_match($regex, $this->getBody(), $matches);
        
        return isset($matches[2]) ? $matches[2] : '';
    }
}
