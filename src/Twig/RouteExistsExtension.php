<?php

namespace App\Twig;

use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class RouteExistsExtension extends AbstractExtension
{
    private $router;

    public function __construct(UrlGeneratorInterface $router)
    {
        $this->router = $router;
    }

    public function getFunctions()
    {
        return array(
            new TwigFunction('route_exists', array($this, 'routeExists')),
        );
    }

    public function routeExists($name, $parameters = null)
    {
        try {
            $url = $this->router->generate($name, $parameters);
        } catch (RouteNotFoundException $e) {
            return false;
        }
        return $url;
    }
}
