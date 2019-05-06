<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace AppBundle\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Templating\EngineInterface;

class SiteExceptionEventSubscriber implements EventSubscriberInterface
{
    /**
     * @var \Symfony\Component\Templating\EngineInterface
     */
    private $templateEngine;

    /**
     * Symfony Kernel environment, e.g. 'prod', 'dev'.
     *
     * @var string
     */
    private $kernelEnvironment;

    public function __construct(EngineInterface $templateEngine, string $kernelEnvironment)
    {
        $this->templateEngine = $templateEngine;
        $this->kernelEnvironment = $kernelEnvironment;
    }

    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::EXCEPTION => 'onKernelException'];
    }

    /**
     * @param \Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent $event
     */
    public function onKernelException(GetResponseForExceptionEvent $event): void
    {
        if ($this->kernelEnvironment !== 'prod') {
            return;
        }

        $response = new Response();
        $exception = $event->getException();

        if ($exception instanceof HttpExceptionInterface) {
            $response->setStatusCode($exception->getStatusCode());
            $response->headers->replace($exception->getHeaders());
        } else {
            $response->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $code = $response->getStatusCode();

        $templateName = "@ezdesign/error_page/{$code}.html.twig";
        if ($this->templateEngine->exists($templateName)) {
            $content = $this->templateEngine->render(
                $templateName,
                ['status_code' => $code]
            );
        } else {
            $content = $this->templateEngine->render(
                '@ezdesign/error_page/error.html.twig',
                ['status_code' => $code, 'status_text' => $exception->getMessage()]
            );
        }

        $response->setContent($content);
        $event->setResponse($response);
    }
}
