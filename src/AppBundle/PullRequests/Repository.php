<?php

namespace AppBundle\PullRequests;

use AppBundle\Search\Repository as SearchRepository;
use Lpdigital\Github\Entity\PullRequest;

/**
 * Get the pull requests according to some filters
 * As GitHub consider pull requests as specific issues
 * don't be surprised too much by the produced repository.
 */
class Repository
{
    private $searchRepository;

    public function __construct(SearchRepository $searchRepository)
    {
        $this->searchRepository = $searchRepository;
    }

    public function findAll($base = 'develop')
    {
        $pullRequests = [];
        $search = $this->searchRepository->getPullRequests(['base' => $base]);

        foreach ($search['items'] as $pullRequest) {
            $pullRequests[] = PullRequest::createFromData($pullRequest);
        }

        return $pullRequests;
    }

    public function findAllWithLabel($label, $base = 'develop')
    {
        $pullRequests = [];
        $search = $this->searchRepository->getPullRequests(
            [
                'label' => $this->parseLabel($label),
                'base' => $base,

            ]
        );

        foreach ($search['items'] as $pullRequest) {
            $pullRequests[] = PullRequest::createFromData($pullRequest);
        }

        return $pullRequests;
    }

    public function findAllWaitingSince($nbDays)
    {
        throw new \Exception('Need to be done');

        return [];
    }

    private function parseLabel($label)
    {
        return '"'.$label.'"';
    }
}
