<?php

namespace App\Service;

use Symfony\Component\String\UnicodeString;
use Symfony\Contracts\Translation\TranslatorInterface;

class LocaleService
{
    private array $supportedLocales;
    private TranslatorInterface $translator;

    public function __construct(string $supportedLocales, TranslatorInterface $translator)
    {
        // explode creates an array from string, array_filter removes empty elements, array_values creates new index
        $this->supportedLocales = array_values(array_filter(explode('|', $supportedLocales)));
        $this->translator = $translator;
    }

    public function getSupportedLocaleChoices(): array
    {
        $choices = [];

        foreach($this->supportedLocales as $locale) {
            $choices[$this->translator->trans($locale)] = $locale;
        }

        return $choices;
    }

    public function getLocaleRepresentations(): array
    {
        return [
            'en' => [
                'flag' => UnicodeString::fromCodePoints(0x1F1EC, 0x1F1E7),
                'name' => 'English'
            ],
            'de' => [
                'flag' => UnicodeString::fromCodePoints(0x1F1E9, 0x1F1EA),
                'name' => 'Deutsch'
            ]
        ];
    }
}