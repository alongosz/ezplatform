<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace AppBundle\Command;

use eZ\Publish\API\Repository\Repository;
use EzSystems\EzPlatformRichText\eZ\RichText\Converter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class ShowCommand extends Command
{
    /** @var \eZ\Publish\API\Repository\Repository */
    private $repository;

    /** @var \EzSystems\EzPlatformRichText\eZ\RichText\Converter */
    private $richTextOutputConverter;

    public function __construct(Repository $repository, Converter $richTextOutputConverter)
    {
        parent::__construct();
        $this->repository = $repository;
        $this->richTextOutputConverter = $richTextOutputConverter;
    }

    protected function configure()
    {
        $this
            ->setName('app:show')
            // ecb768115e138a782057ba2b2aa22de0
            ->addArgument('remote-id', InputArgument::REQUIRED, 'Content item Remote Id')
            ->addArgument('what', InputArgument::REQUIRED, 'What to show, one of: HTML5, DocBook')
        ;
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->repository->getPermissionResolver()->setCurrentUserReference(
            $this->repository->getUserService()->loadUserByLogin('admin')
        );
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $contentService = $this->repository->getContentService();

        $content = $contentService->loadContentByRemoteId($input->getArgument('remote-id'));

        $xmlDocBookDocument = $content->getField('description')->value->xml;

        $whatToShow = $input->getArgument('what');

        switch ($whatToShow) {
            case 'HTML5':
                $output->writeln($this->richTextOutputConverter->convert($xmlDocBookDocument)->saveHTML());
                break;
            case 'DocBook':
                $output->writeln($xmlDocBookDocument->saveXML());
                break;
            default:
                throw new InvalidArgumentException('what: expected one of HTML5, DocBook', 1);
        }

    }
}
