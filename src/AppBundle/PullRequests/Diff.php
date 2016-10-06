<?php

namespace AppBundle\PullRequests;

/**
 * Extract human readable data from Pull diff.
 */
class Diff
{
    /* string */
    public static $content;
    /* array */
    public static $lines;

    const TOKEN_PLUS = '+';

    /**
     * Parse diff content and look for regexp pattern.
     * 
     * @param string a regex expression
     * @param string the diff content
     * 
     * @return bool
     */
    public static function match($pattern, $diffContent)
    {
        self::$content = $diffContent;

        $token = strtok($diffContent, PHP_EOL);

        while ($token !== false) {
            if (
                0 === strpos($token, self::TOKEN_PLUS) &&
                1 === preg_match($pattern, $token)
            ) {
                return true;
            }
            $token = strtok(PHP_EOL);
        }

        return false;
    }
}
