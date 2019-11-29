<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace AppBundle\Command;

use eZ\Publish\API\Repository\ContentService;
use eZ\Publish\API\Repository\ContentTypeService;
use eZ\Publish\API\Repository\LocationService;
use eZ\Publish\API\Repository\PermissionResolver;
use eZ\Publish\API\Repository\UserService;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class TestCommand extends Command
{
    /**
     * @var \eZ\Publish\API\Repository\PermissionResolver
     */
    private $permissionResolver;

    /**
     * @var \eZ\Publish\API\Repository\UserService
     */
    private $userService;

    /**
     * @var \eZ\Publish\API\Repository\ContentTypeService
     */
    private $contentTypeService;

    /**
     * @var \eZ\Publish\API\Repository\ContentService
     */
    private $contentService;

    /**
     * @var \eZ\Publish\API\Repository\LocationService
     */
    private $locationService;

    public function __construct(
        PermissionResolver $permissionResolver,
        UserService $userService,
        ContentTypeService $contentTypeService,
        ContentService $contentService,
        LocationService $locationService
    ) {
        parent::__construct();
        $this->permissionResolver = $permissionResolver;
        $this->userService = $userService;
        $this->contentTypeService = $contentTypeService;
        $this->contentService = $contentService;
        $this->locationService = $locationService;
    }

    public function configure() {
        $this->setName('app:test');
    }

    /**
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->permissionResolver->setCurrentUserReference(
            $this->userService->loadUserByLogin('admin')
        );
    }

    /**
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\ForbiddenException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // create a Content Type which is not always available by default
        $contentType = $this->contentTypeService->loadContentTypeByIdentifier('article');

        $contentCreate = $this->contentService->newContentCreateStruct($contentType, 'eng-GB');
        $contentCreate->setField('title', 'My Article');
        $contentCreate->setField('intro', $this->getIntro());
        $content = $this->contentService->publishVersion(
            $this->contentService->createContent(
                $contentCreate,
                [$this->locationService->newLocationCreateStruct(2)]
            )->getVersionInfo()
        );
        $output->writeln('ContentInfo of created content:');
        $this->dump($content->contentInfo);

        $updateStruct = $this->contentService->newContentMetadataUpdateStruct();
        $updateStruct->mainLanguageCode = 'eng-GB';

        $this->contentService->updateContentMetadata($content->contentInfo, $updateStruct);

        $reloadedContent = $this->contentService->loadContent($content->id);

        $output->writeln('ContentInfo of updated content:');
        $this->dump($reloadedContent->contentInfo);
    }

    private function dump(ContentInfo $contentInfo)
    {
        $dumpFn = function_exists('dump') ? 'dump' : 'var_dump';
        $dumpFn($contentInfo);
    }

    private function getIntro()
    {
        return <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<section xmlns="http://docbook.org/ns/docbook"
         version="5.0-variant ezpublish-1.0">
  <title>This is a heading.</title>
</section>
XML;
    }
}
