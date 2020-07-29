<?php

namespace Tests\AppBundle\PullRequests;

use AppBundle\PullRequests\BodyParser;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\ValidatorBuilder;

class ListenerTest extends TestCase
{
    private $validator;

    public function setUp()
    {
        $this->validator = (new ValidatorBuilder())
            ->enableAnnotationMapping()
            ->getValidator();
    }

    /**
     * @dataProvider getTests
     *
     * @param $descriptionFilename
     * @param $expected
     */
    public function testDescriptions($descriptionFilename, $expected)
    {
        $body = file_get_contents(__DIR__.'/../../Resources/PullRequestBody/'.$descriptionFilename);
        $bodyParser = new BodyParser($body);

        $validations = $this->validator->validate($bodyParser);
        $this->assertSame(\count($expected), \count($validations));
        foreach ($validations as $validation) {
            $this->assertContains($validation->getPropertyPath(), $expected);
        }
    }

    public function getTests()
    {
        return [
            'Valid description' => [
                'bug_fix.txt',
                [],
            ],
            'Missing description' => [
                'missing_description.txt',
                ['description'],
            ],
            'Invalid type' => [
                'invalid_type.txt',
                ['type'],
            ],
            'Invalid category' => [
                'invalid_category.txt',
                ['category'],
            ],
            'No related ticked' => [
                'no_related_ticket.txt',
                ['relatedTicket'],
            ],
        ];
    }
}
