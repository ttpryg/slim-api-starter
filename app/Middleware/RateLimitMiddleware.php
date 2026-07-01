<?php

declare(strict_types=1);

namespace App\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

final class RateLimitMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly array $settings
    ) {}

    public function process(Request $request, RequestHandler $handler): Response
    {
        $ip = $request->getServerParams()['REMOTE_ADDR'] ?? '127.0.0.1';
        $maxRequests = $this->settings['max_requests'];
        $window = $this->settings['window'];
        $storagePath = $this->settings['storage_path'];

        if (! is_dir($storagePath)) {
            @mkdir($storagePath, 0775, true);
        }

        $file = $storagePath.'/'.md5($ip).'.json';
        $now = time();

        $data = ['window_start' => $now, 'count' => 0];

        $fp = fopen($file, 'c+');
        if ($fp) {
            flock($fp, LOCK_EX);

            // Baca isi file
            $content = stream_get_contents($fp);
            if (! empty($content)) {
                $saved = json_decode($content, true);
                if (is_array($saved) && isset($saved['window_start']) && $saved['window_start'] > $now - $window) {
                    $data = $saved;
                }
            }

            $data['count']++;

            if ($data['count'] > $maxRequests) {
                flock($fp, LOCK_UN);
                fclose($fp);

                $response = new \Slim\Psr7\Response;
                $response->getBody()->write(json_encode([
                    'success' => false,
                    'message' => 'Too many requests. Please try again later.',
                ]));

                return $response
                    ->withHeader('Content-Type', 'application/json')
                    ->withStatus(429)
                    ->withHeader('Retry-After', (string) $window);
            }

            // Tulis ulang isi file
            ftruncate($fp, 0);
            rewind($fp);
            fwrite($fp, json_encode($data));
            fflush($fp);
            flock($fp, LOCK_UN);
            fclose($fp);
        } else {
            // Fallback jika file gagal dibuka
            $data['count']++;
        }

        $response = $handler->handle($request);

        $remaining = $maxRequests - $data['count'];

        return $response
            ->withHeader('X-RateLimit-Limit', (string) $maxRequests)
            ->withHeader('X-RateLimit-Remaining', (string) max(0, $remaining));
    }
}
