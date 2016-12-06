<?php

namespace AppBundle\Security;

use Symfony\Component\HttpFoundation\Request;

/**
 * Class which implements this interface will check if a given Symfony request coming from GitHub in a web hook context
 * contains proper signature based on the provided secret.
 *
 * @author Mickaël Andrieu <andrieu.travail@gmail.com>
 */
interface SignatureValidatorInterface
{
    /**
     * Checks is the request contains valid signature.
     *
     * @param Request $request Incoming request
     * @param string  $secret  GitHub web hook secret
     *
     * @throws InvalidGitHubRequestSignatureException
     */
    public function validate(Request $request, $secret);
}
