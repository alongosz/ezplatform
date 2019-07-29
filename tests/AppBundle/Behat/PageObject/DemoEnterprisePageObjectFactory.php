<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Tests\AppBundle\Behat\PageObject;

use EzSystems\EzPlatformAdminUi\Behat\Helper\UtilityContext;
use EzSystems\EzPlatformAdminUi\Behat\PageObject\Page;
use EzSystems\EzPlatformPageBuilder\Tests\Behat\PageObject\EnterprisePageObjectFactory;
use EzSystems\EzPlatformPageBuilder\Tests\Behat\PageObject\LandingPagePreview;
use EzSystems\EzPlatformPageBuilder\Tests\Behat\PageObject\PageBuilderEditor;

class DemoEnterprisePageObjectFactory extends EnterprisePageObjectFactory
{
    /**
     * @param UtilityContext $context
     * @param string $pageName
     * @param null[]|string[] ...$parameters
     *
     * @return Page
     */
    public static function createPage(UtilityContext $context, string $pageName, ?string ...$parameters): Page
    {
        switch ($pageName) {
            case LandingPagePreview::PAGE_NAME:
                return new DemoLandingPagePreview($context);
            default:
                return parent::createPage($context, $pageName, ...$parameters);
        }
    }
}
