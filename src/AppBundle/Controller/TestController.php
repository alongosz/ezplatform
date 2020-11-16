<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace AppBundle\Controller;

use eZ\Bundle\EzPublishCoreBundle\Controller;
use eZ\Publish\API\Repository\ContentService;
use Symfony\Component\HttpFoundation\Response;

class TestController extends Controller
{
    private $contentService;

    public function __construct(ContentService $contentService)
    {
        $this->contentService = $contentService;
    }

    public function footerInfo(): Response
    {
        return $this->render('@ezdesign/footer_info.html.twig', [
            'content' => $this->contentService->loadContent(1)
        ]);
    }
}
