<?php

namespace App\Controller;

use eZ\Bundle\EzPublishCoreBundle\Controller;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{
    public function showSiteAccessNameAction(): Response
    {
        return new Response(
            'Current SiteAccess via GlobalHelper: ' . $this->getGlobalHelper()->getSiteaccess()->name
        );
    }
}
