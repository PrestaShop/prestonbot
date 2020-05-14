<?php

namespace Tests\AppBundle\Commits;

use AppBundle\Commits\RepositoryInterface;
use Lpdigital\Github\Entity\Commit;
use Lpdigital\Github\Entity\PullRequest;
use Lpdigital\Github\Entity\User;

class FakeRepository implements RepositoryInterface
{

    public function findAllByUser(User $user)
    {
        $filename = 'commits.user.'. $user->getLogin() .'.json';
        $responseApi = json_decode(file_get_contents(__DIR__ . '/../webhook_examples/' . $filename), true);
        $commits = [];
        foreach ($responseApi as $commitApi) {
            $commits[] = Commit::createFromData($commitApi['commit']);
        }

        return $commits;
    }

    public function findAllByPullRequest(PullRequest $pullRequest)
    {
        $filename = 'commits.all.'. $pullRequest->getNumber() .'.json';
        $responseApi = json_decode(file_get_contents(__DIR__ . '/../webhook_examples/' . $filename), true);
        $commits = [];
        foreach ($responseApi as $commitApi) {
            $commits[] = Commit::createFromData($commitApi['commit']);
        }

        return $commits;
    }
}
