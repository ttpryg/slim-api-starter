<?php

declare(strict_types=1);

namespace App\I18n;

final class Translator
{
    /**
     * @var array<string, array<string, string>>
     */
    private array $translations = [];

    public function __construct(
        private string $defaultLocale = 'en',
        private ?string $translationsPath = null
    ) {
        $this->translationsPath ??= __DIR__.'/../../resources/lang';
        $this->loadLocale($this->defaultLocale);
    }

    public function getLocale(): string
    {
        return $this->defaultLocale;
    }

    public function setLocale(string $locale): self
    {
        $this->defaultLocale = $locale;
        $this->loadLocale($locale);

        return $this;
    }

    /**
     * Translate the given key.
     * Supports dot-notation, e.g. "messages.welcome" or "validation.required".
     *
     * @param  array<string, scalar>  $replace
     */
    public function get(string $key, array $replace = [], ?string $locale = null): string
    {
        $targetLocale = $locale ?? $this->defaultLocale;
        $this->loadLocale($targetLocale);

        $line = $this->translations[$targetLocale][$key] ?? null;

        if ($line === null) {
            // Fallback to default locale if different
            if ($targetLocale !== 'en') {
                $this->loadLocale('en');
                $line = $this->translations['en'][$key] ?? $key;
            } else {
                $line = $key;
            }
        }

        foreach ($replace as $placeholder => $value) {
            $line = str_replace([':'.$placeholder, '{'.$placeholder.'}'], (string) $value, $line);
        }

        return $line;
    }

    /**
     * Determine best matching locale from HTTP Accept-Language header.
     *
     * @param  list<string>  $supportedLocales
     */
    public static function parseAcceptLanguage(string $header, array $supportedLocales = ['en', 'id'], string $default = 'en'): string
    {
        if ($header === '') {
            return $default;
        }

        // Accept-Language format: id-ID,id;q=0.9,en-US;q=0.8,en;q=0.7
        $locales = [];
        foreach (explode(',', $header) as $part) {
            $subParts = explode(';', trim($part));
            $code = strtolower(trim($subParts[0]));
            // e.g. en-US -> en
            $primaryCode = explode('-', $code)[0];
            $quality = 1.0;
            if (isset($subParts[1])) {
                $qParts = explode('=', trim($subParts[1]));
                if (($qParts[0] ?? null) === 'q' && isset($qParts[1])) {
                    $quality = (float) $qParts[1];
                }
            }
            $locales[$primaryCode] = max($locales[$primaryCode] ?? 0.0, $quality);
        }

        arsort($locales);

        foreach (array_keys($locales) as $locale) {
            if (in_array($locale, $supportedLocales, true)) {
                return $locale;
            }
        }

        return $default;
    }

    private function loadLocale(string $locale): void
    {
        if (isset($this->translations[$locale])) {
            return;
        }

        $this->translations[$locale] = [];

        $localeDir = $this->translationsPath.'/'.$locale;
        if (! is_dir($localeDir)) {
            return;
        }

        $files = glob($localeDir.'/*.php');
        if ($files === false) {
            return;
        }

        foreach ($files as $file) {
            $group = basename($file, '.php');
            $data = require $file;
            if (is_array($data)) {
                foreach ($data as $key => $value) {
                    if (is_string($value)) {
                        $this->translations[$locale][$group.'.'.$key] = $value;
                    }
                }
            }
        }
    }
}
