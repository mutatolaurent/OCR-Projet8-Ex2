<?php

namespace App\EventListener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Psr\Log\LoggerInterface;

#[AsEventListener(event: 'kernel.request', method: 'onKernelRequest')]
final class RouteLogListener
{
    // On utilise l'injection de dépendance pour cibler un "channel" Monolog spécifique (ex: routing)
    public function __construct(
        private LoggerInterface $routingLogger
    ) {
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        // On évite de logguer les sous-requêtes internes de Symfony
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        $route  = $request->attributes->get('_route');

        // Optionnel : On évite de logguer le profiler ou la barre de debug en dev
        if (str_starts_with($route, '_wdt') || str_starts_with($route, '_profiler')) {
            return;
        }

        $ip     = $request->getClientIp();
        $method = $request->getMethod();
        $uri    = $request->getRequestUri();

        // On écrit l'information dans notre log
        $this->routingLogger->info(sprintf(
            'Route: %s | Method: %s | URI: %s | IP: %s',
            $route ?? 'n/a',
            $method,
            $uri,
            $ip
        ));
    }
}
