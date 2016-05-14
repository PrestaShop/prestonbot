<?php

namespace AppBundle\PullRequests;

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
        preg_match($regex, $this->getBody(), $matches);
        
        return $matches[2];
    }
    
    public function getBody()
    {
        return $this->bodyContent;
    }
    
    public function getDescription()
    {
        $regex = "/(\|[[:space:]]Description\?[[:space:]]+\|[[:space:]])(.+)\r\n/";
        preg_match($regex, $this->getBody(), $matches);
        
        return $matches[2];
    }
    
    public function getType()
    {
        $regex = "/(\|[[:space:]]Type\?[[:space:]]+\|[[:space:]])(.+)\r\n/";
        preg_match($regex, $this->getBody(), $matches);
        
        return $matches[2];
    }
    
    public function getCategory()
    {
        $regex = "/(\|[[:space:]]Category\?[[:space:]]+\|[[:space:]])(.+)\r\n/";
        preg_match($regex, $this->getBody(), $matches);
        
        return $matches[2];
    }
    
    public function isBackwardCompatible()
    {
        $regex = "/(\|[[:space:]]BC breaks\?[[:space:]]+\|[[:space:]])(.+)\r\n/";
        preg_match($regex, $this->getBody(), $matches);
        
        return $matches[2] == 'no' ? false : true;
    }
    
    public function willDeprecateCode()
    {
        $regex = "/(\|[[:space:]]Deprecations\?[[:space:]]+\|[[:space:]])(.+)\r\n/";
        preg_match($regex, $this->getBody(), $matches);
        
        return $matches[2] == 'no' ? false : true;
    }
    
    public function isAFeature()
    {
        return preg_match('/feature/',$this->getType());
    }
    
    public function isAnImprovement()
    {
        return preg_match('/improvement/',$this->getType());
    }
    
    public function isABugFix()
    {
        return preg_match('/bug fix/',$this->getType());
    }
    
    public function getRelatedForgeIssue()
    {
        
    }
    
    public function getTestingScenario()
    {
        
    }
}
