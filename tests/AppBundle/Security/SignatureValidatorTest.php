<?php

namespace Tests\AppBundle\Security;

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
     * @dataProvider getSignatures
     *
     * @param string $requestBody
     * @param string $signature
     * @param mixed  $expected
     */
    public function testCorrectSignature($requestBody, $signature, $expected)
    {
        $validated = $this->signatureValidator->validate($this->createRequest($requestBody, $signature), self::SECRET);
        $this->assertSame($expected, $validated);
    }

    public function getSignatures()
    {
        return [
            // correct signatures
            [
                '{"foo": "bar"}',
                self::createSignature('{"foo": "bar"}'),
                true,
            ],
            [
                '{"foo": "bar"}',
                self::createSignature('{"foo": "bar"}', self::SECRET, 'md5'),
                true,
            ],
            [
                '{"foo": "bar", "baz": true}',
                self::createSignature('{"foo": "bar", "baz": true}', self::SECRET, 'sha256'),
                true,
            ],
            // incorect signatures
            [
                '{"foo": "bar"}',
                'sha1=WrongHashOrInvalidSecret',
                false,
            ],
            [
                '{"foo": "bar"}',
                null, // No HTTP_X_Hub_Signature header
                false,
            ],
            [
                '{"foo": "bar"}',
                'Invalid Signature Header',
                false,
            ],
            [
                '{"foo": "bar"}',
                'sha1=', // No hash value
                false,
            ],
            [
                '{"foo": "bar"}',
                '=hash', // No algorithm
                false,
            ],
            [
                '{"foo": "bar"}',
                '=', // No algo nor hash
                false,
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
