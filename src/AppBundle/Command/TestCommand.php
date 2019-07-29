<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace AppBundle\Command;

use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use eZ\Publish\API\Repository\Exceptions\UnauthorizedException;
use eZ\Publish\API\Repository\Repository;
use eZ\Publish\API\Repository\Values\Content\Content;
use EzSystems\EzPlatformRichText\eZ\FieldType\RichText\Value;
use EzSystems\EzPlatformRichText\eZ\RichText\Converter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class TestCommand extends Command
{
    /** @var \eZ\Publish\API\Repository\Repository */
    private $repository;

    /** @var \EzSystems\EzPlatformRichText\eZ\RichText\Converter */
    private $richTextOutputConverter;

    /** @var */
    private $inputXmlFilePath;

    public function __construct(
        Repository $repository,
        Converter $richTextOutputConverter,
        string $inputXmlFilePath
    ) {
        parent::__construct();
        $this->repository = $repository;
        $this->richTextOutputConverter = $richTextOutputConverter;
        $this->inputXmlFilePath = $inputXmlFilePath;
    }

    protected function configure()
    {
        $this->setName('app:test');
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
     * @return void
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\BadStateException
     * @throws \eZ\Publish\API\Repository\Exceptions\ContentFieldValidationException
     * @throws \eZ\Publish\API\Repository\Exceptions\ContentValidationException
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $contentService = $this->repository->getContentService();

        $content = $this->loadTestContent(md5(__FILE__), 'Testing EZP-30730');

        $draft = $contentService->createContentDraft($content->contentInfo);
        $contentUpdateStruct = $contentService->newContentUpdateStruct();
        $richTextValue = $this->getRichTextValue();
        $contentUpdateStruct->setField('description', $richTextValue);
        $contentService->updateContent($draft->versionInfo, $contentUpdateStruct);
        $contentService->publishVersion($draft->versionInfo);

        $output->writeln($this->richTextOutputConverter->convert($richTextValue->xml)->saveHTML());
    }

    private function loadTestContent(string $remoteId, string $name): Content
    {
        $contentService = $this->repository->getContentService();

        try {
            return $contentService->loadContentByRemoteId($remoteId);
        } catch (NotFoundException $e) {
            return $this->createTestContent($remoteId, $name);
        } catch (UnauthorizedException $e) {
            throw $e;
        }
    }

    /**
     * @param string $remoteId
     * @param string $name
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content
     * @throws \eZ\Publish\API\Repository\Exceptions\ForbiddenException
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    private function createTestContent(string $remoteId, string $name): Content
    {
        $contentService = $this->repository->getContentService();
        $contentTypeService = $this->repository->getContentTypeService();
        $locationService = $this->repository->getLocationService();

        $folderType = $contentTypeService->loadContentTypeByIdentifier('folder');

        $contentCreateStruct = $contentService->newContentCreateStruct($folderType, 'eng-GB');
        $contentCreateStruct->remoteId = $remoteId;
        $contentCreateStruct->setField('name', $name);
        $contentDraft = $contentService->createContent(
            $contentCreateStruct,
            [$locationService->newLocationCreateStruct(2)]
        );

        return $contentService->publishVersion($contentDraft->getVersionInfo());
    }

    private function getRichTextValue(): Value
    {
        return new Value(file_get_contents($this->inputXmlFilePath));
    }
}
