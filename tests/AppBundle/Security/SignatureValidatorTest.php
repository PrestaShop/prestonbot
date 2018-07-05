<?php

namespace tests\AppBundle\Security;

use AppBundle\Security\SignatureValidator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author Mickaël Andrieu <andrieu.travail@gmail.com>
 * @author  Sylvain Mauduit <sylvain@mauduit.fr>
 *
 * @see https://github.com/Swop/github-webhook
 */
class SignatureValidatorTest extends TestCase
{
    const SECRET = 'MyDirtySecret';
    private $signatureValidator;

    protected function setUp()
    {
        $this->signatureValidator = new SignatureValidator();
    }

    /**
     * @dataProvider correctSignatures
     *
     * @param string $requestBody
     * @param string $signature
     */
    public function testCorrectSignature($requestBody, $signature)
    {
        $this->signatureValidator->validate($this->createRequest($requestBody, $signature), self::SECRET);
    }

    public function correctSignatures()
    {
        return [
            [
                '{"foo": "bar"}',
                self::createSignature('{"foo": "bar"}'),
            ],
            [
                '{"foo": "bar"}',
                self::createSignature('{"foo": "bar"}', self::SECRET, 'md5'),
            ],
            [
                '{"foo": "bar", "baz": true}',
                self::createSignature('{"foo": "bar", "baz": true}', self::SECRET, 'sha256'),
            ],
        ];
    }

    public function incorrectSignatures()
    {
        return [
            [
                '{"foo": "bar"}',
                'sha1=WrongHashOrInvalidSecret',
            ],
            [
                '{"foo": "bar"}',
                null, // No HTTP_X_Hub_Signature header
            ],
            [
                '{"foo": "bar"}',
                'Invalid Signature Header',
            ],
            [
                '{"foo": "bar"}',
                'sha1=', // No hash value
            ],
            [
                '{"foo": "bar"}',
                '=hash', // No algorithm
            ],
            [
                '{"foo": "bar"}',
                '=', // No algo nor hash
            ],
        ];
    }

    /**
     * @param string $algo
     * @param string $signedContent
     * @param string $secret
     *
     * @return string
     */
    public static function createSignature($signedContent, $secret = self::SECRET, $algo = 'sha1')
    {
        return sprintf('%s=%s', $algo, hash_hmac($algo, $signedContent, $secret));
    }

    /**
     * @param string $requestContent
     * @param string $requestSignature
     *
     * @return Request
     */
    private function createRequest($requestContent, $requestSignature)
    {
        if (null === $requestSignature) {
            $requestSignatureHeader = [];
        } else {
            $requestSignatureHeader = [$requestSignature];
        }

        $request = Request::create('', 'POST', [], [], [], [], $requestContent);
        $request->headers->set('X-Hub-Signature', $requestSignatureHeader);

        return $request;
    }
}
