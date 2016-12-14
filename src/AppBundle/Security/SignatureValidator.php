<?php

namespace AppBundle\Security;

use Symfony\Component\HttpFoundation\Request;

/**
 * A 'Symfony' adaptation of github-webhook SignatureValidator.
 *
 * @author Mickaël Andrieu <andrieu.travail@gmail.com>
 * @author  Sylvain Mauduit <sylvain@mauduit.fr>
 *
 * @see https://github.com/Swop/github-webhook
 */
class SignatureValidator implements SignatureValidatorInterface
{
    public function validate(Request $request, $secret)
    {
        $signature = $request->headers->get('X-Hub-Signature');
        $payload = $request->getContent();

        return $this->validateSignature($signature, $payload, $secret);
    }

    /**
     * @param string $signature
     * @param string $payload
     * @param string $secret
     *
     * @return bool
     */
    private function validateSignature($signature, $payload, $secret)
    {
        if (empty($signature)) {
            return false;
        }

        $explodeResult = explode('=', $signature, 2);
        if (2 !== count($explodeResult)) {
            return false;
        }

        list($algorithm, $hash) = $explodeResult;

        if (empty($algorithm) || empty($hash)) {
            return false;
        }

        $payloadHash = @hash_hmac($algorithm, $payload, $secret);

        return $hash === $payloadHash;
    }
}
