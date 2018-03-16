<?php


namespace AppBundle\Command;

use eZ\Publish\API\Repository\Exceptions\InvalidArgumentException;
use eZ\Publish\API\Repository\SearchService;
use eZ\Publish\API\Repository\Values\Content\Query;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;

class SearchForEmptyImageCommand extends Command
{
    /**
     * @var \eZ\Publish\API\Repository\SearchService
     */
    private $searchService;

    public function __construct(SearchService $searchService)
    {
        parent::__construct();
        $this->searchService = $searchService;
    }

    protected function configure()
    {
        $this
            ->setName('app:find_empty_images')
            ->setDescription('Find empty images')
        ;
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return void
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $query = new Query(
            [
                'filter' => new Criterion\Field('image', Criterion\Operator::EQ, 'image'),
            ]
        );

        $results = $this->searchService->findContent($query);

        var_dump($results);

    }
}
