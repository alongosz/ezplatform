<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;

class ReportController extends Controller
{
    public function showRootLocationPathStringAction(): Response
    {
        return new Response(
            'Root Location path string: ' . $this->getRootLocation()->pathString
        );
    }
}
