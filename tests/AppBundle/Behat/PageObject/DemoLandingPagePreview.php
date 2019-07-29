<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Tests\AppBundle\Behat\PageObject;

use EzSystems\EzPlatformAdminUi\Behat\Helper\EzEnvironmentConstants;
use EzSystems\EzPlatformAdminUi\Behat\Helper\UtilityContext;
use EzSystems\EzPlatformPageBuilder\Tests\Behat\PageObject\LandingPagePreview;

class DemoLandingPagePreview extends LandingPagePreview
{
    public function setTitle(string $title): void
    {
        $this->pageTitle = $title;
    }

    public function getDefaultPreviewData(): ?array
    {
        if ($this->pageTitle === EzEnvironmentConstants::get('MAIN_LANDING_PAGE_HEADER')) {
            return [['blockType', 'parameter1'], ['Hero', EzEnvironmentConstants::get('MAIN_LANDING_PAGE_HEADER')]];
        }

        return parent::getDefaultPreviewData();
    }

    public function verifyTitle(): void
    {
        // no title for Demo Pages
    }
}
