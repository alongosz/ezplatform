<?php declare(strict_types=1);

namespace AppBundle\Command;

use eZ\Publish\API\Repository\ContentService;
use eZ\Publish\API\Repository\LocationService;
use eZ\Publish\API\Repository\PermissionResolver;
use eZ\Publish\API\Repository\URLAliasService;
use eZ\Publish\API\Repository\UserService;
use function sprintf;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class CreateUrlAliasCommand extends Command
{
    /** @var \eZ\Publish\API\Repository\URLAliasService */
    private $urlAliasService;
    /** @var \eZ\Publish\API\Repository\LocationService */
    private $locationService;
    /** @var \eZ\Publish\API\Repository\ContentService */
    private $contentService;
    /** @var \eZ\Publish\API\Repository\PermissionResolver */
    private $permissionResolver;
    /** @var \eZ\Publish\API\Repository\UserService */
    private $userService;

    public function __construct(
        PermissionResolver $permissionResolver,
        UserService $userService,
        URLAliasService $urlAliasService,
        LocationService $locationService,
        ContentService $contentService
    ) {
        parent::__construct('app:create-url-alias');
        $this->permissionResolver = $permissionResolver;
        $this->userService = $userService;
        $this->urlAliasService = $urlAliasService;
        $this->locationService = $locationService;
        $this->contentService = $contentService;
    }

    protected function configure(): void
    {
        $this
            ->addArgument('content-remote-id', InputArgument::REQUIRED)
            ->addArgument('alias', InputArgument::REQUIRED)
            ->addArgument('language-code', InputArgument::REQUIRED);
    }

    /**
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->permissionResolver->setCurrentUserReference(
            $this->userService->loadUserByLogin('admin')
        );
    }

    /**
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $content = $this->contentService->loadContentByRemoteId(
            $input->getArgument('content-remote-id')
        );
        $aliasToBeAdded = $input->getArgument('alias');
        $languageCode = $input->getArgument('language-code');

        $io->writeln(
            sprintf(
                'Adding "%s" alias for translation "%s" for the main Location of Content "[%d] %s"',
                $aliasToBeAdded,
                $languageCode,
                $content->id,
                $content->getName($content->contentInfo->mainLanguageCode)
            )
        );

        $urlAlias = $this->urlAliasService->createUrlAlias(
            $this->locationService->loadLocation(
                $content->contentInfo->mainLocationId
            ),
            $aliasToBeAdded,
            $languageCode,
            true, // forwarding
            true // always available
        );

        $io->success('UrlAlias created');
        $io->table(
            ['property', 'value'],
            [
                ['id', $urlAlias->id],
                ['type', $urlAlias->type],
                ['destination', $urlAlias->destination],
                ['forward', $urlAlias->forward],
                ['isCustom', $urlAlias->isCustom],
                ['isHistory', $urlAlias->isHistory],
                ['languageCodes', $urlAlias->languageCodes],
                ['alwaysAvailable', $urlAlias->alwaysAvailable],
                ['path', $urlAlias->path],
            ]
        );

        return 0;
    }
}
