<?php

declare(strict_types=1);

namespace App\Test\I18n;

use App\I18n\Translator;
use App\Middleware\LocaleMiddleware;
use App\Test\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Factory\ResponseFactory;
use Slim\Psr7\Factory\ServerRequestFactory;

class TranslatorTest extends TestCase
{
    public function test_translator_fetches_default_locale_line(): void
    {
        $translator = new Translator('en');
        $this->assertSame('Success', $translator->get('messages.success'));
        $this->assertSame('Validation failed', $translator->get('messages.validation_failed'));
    }

    public function test_translator_fetches_indonesian_locale_line(): void
    {
        $translator = new Translator('en');
        $this->assertSame('Berhasil', $translator->get('messages.success', locale: 'id'));
        $this->assertSame('Validasi gagal', $translator->get('messages.validation_failed', locale: 'id'));
    }

    public function test_translator_parses_accept_language_header(): void
    {
        $this->assertSame('id', Translator::parseAcceptLanguage('id-ID,id;q=0.9,en-US;q=0.8,en;q=0.7'));
        $this->assertSame('en', Translator::parseAcceptLanguage('en-US,en;q=0.9'));
        $this->assertSame('en', Translator::parseAcceptLanguage(''));
        $this->assertSame('en', Translator::parseAcceptLanguage('fr-FR,fr;q=0.9')); // fallback
    }

    public function test_locale_middleware_sets_translator_and_header(): void
    {
        $translator = new Translator('en');
        $middleware = new LocaleMiddleware($translator);

        $request = (new ServerRequestFactory)->createServerRequest('GET', '/')
            ->withHeader('Accept-Language', 'id-ID,id;q=0.9');

        $response = $middleware->process($request, new class implements RequestHandlerInterface
        {
            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                $locale = $request->getAttribute(LocaleMiddleware::ATTRIBUTE_NAME);
                $response = (new ResponseFactory)->createResponse();
                $response->getBody()->write((string) $locale);

                return $response;
            }
        });

        $this->assertSame('id', (string) $response->getBody());
        $this->assertSame('id', $response->getHeaderLine('Content-Language'));
        $this->assertSame('id', $translator->getLocale());
    }
}
