<?php

declare(strict_types=1);

namespace App\Middleware;

use App\I18n\Translator;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

final readonly class LocaleMiddleware implements MiddlewareInterface
{
    public const ATTRIBUTE_NAME = 'locale';

    /**
     * @param  list<string>  $supportedLocales
     */
    public function __construct(
        private Translator $translator,
        private array $supportedLocales = ['en', 'id'],
        private string $defaultLocale = 'en'
    ) {}

    public function process(Request $request, RequestHandler $handler): Response
    {
        $header = $request->getHeaderLine('Accept-Language');
        $locale = Translator::parseAcceptLanguage($header, $this->supportedLocales, $this->defaultLocale);

        $this->translator->setLocale($locale);

        $request = $request->withAttribute(self::ATTRIBUTE_NAME, $locale);
        $response = $handler->handle($request);

        return $response->withHeader('Content-Language', $locale);
    }
}
