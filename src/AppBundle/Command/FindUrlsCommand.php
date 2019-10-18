<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace AppBundle\Command;

use eZ\Publish\API\Repository\PermissionResolver;
use eZ\Publish\API\Repository\URLService;
use eZ\Publish\API\Repository\UserService;
use eZ\Publish\API\Repository\Values\URL\Query\Criterion;
use eZ\Publish\API\Repository\Values\URL\Query\SortClause;
use eZ\Publish\API\Repository\Values\URL\URLQuery;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class FindUrlsCommand extends Command
{
    /** @var \eZ\Publish\API\Repository\PermissionResolver */
    private $permissionResolver;

    /** @var \eZ\Publish\API\Repository\UserService */
    private $userService;

    /** @var \eZ\Publish\API\Repository\URLService */
    private $urlService;

    public function __construct(
        PermissionResolver $permissionResolver,
        UserService $userService,
        URLService $urlService
    ) {
        parent::__construct();
        $this->permissionResolver = $permissionResolver;
        $this->userService = $userService;
        $this->urlService = $urlService;
    }

    /**
     * {@inheritdoc}
     */
    public function configure()
    {
        $this
            ->setName('test:find-urls')
            ->addArgument('pattern', InputArgument::IS_ARRAY | InputArgument::OPTIONAL)
            ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->permissionResolver->setCurrentUserReference(
            $this->userService->loadUserByLogin('admin')
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $patterns = $input->getArgument('pattern');

        $query = new URLQuery();
        $criteria = [
            new Criterion\LogicalOr(
                [
                    new Criterion\SectionId([1,2]),
                    //new Criterion\SectionIdentifier(['media'])
                ]
            ),
            new Criterion\VisibleOnly()
        ];
        if (!empty($patterns)) {
            $patternCriteria = array_map(
                function (string $pattern) {
                    return new Criterion\Pattern($pattern);
                },
                $patterns
            );

            $criteria[] = new Criterion\LogicalOr($patternCriteria);
        }
        $query->filter = new Criterion\LogicalAnd($criteria);
        $query->sortClauses = [
            new SortClause\URL(),
        ];
        $query->offset = 0;

        $urlSearchResult = $this->urlService->findUrls($query);
        $output->writeln("<info>Found <comment>{$urlSearchResult->totalCount}</comment> urls.</info>");
        foreach ($urlSearchResult as $foundUrl) {
            $output->writeln("\t[{$foundUrl->id}] <info>{$foundUrl->url}</info>");
        }
    }

}
