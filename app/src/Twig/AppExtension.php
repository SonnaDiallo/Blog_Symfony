<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('time_ago', [$this, 'timeAgo']),
            new TwigFilter('reading_time', [$this, 'readingTime']),
            new TwigFilter('truncate_words', [$this, 'truncateWords']),
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('pluralize', [$this, 'pluralize']),
        ];
    }

    public function timeAgo(\DateTimeInterface $date): string
    {
        $now = new \DateTime();
        $diff = $now->diff($date);

        if ($diff->y > 0) {
            return $diff->y === 1 ? 'il y a 1 an' : 'il y a ' . $diff->y . ' ans';
        }
        if ($diff->m > 0) {
            return $diff->m === 1 ? 'il y a 1 mois' : 'il y a ' . $diff->m . ' mois';
        }
        if ($diff->d > 0) {
            if ($diff->d === 1) return 'hier';
            if ($diff->d < 7) return 'il y a ' . $diff->d . ' jours';
            $weeks = floor($diff->d / 7);
            return $weeks === 1 ? 'il y a 1 semaine' : 'il y a ' . $weeks . ' semaines';
        }
        if ($diff->h > 0) {
            return $diff->h === 1 ? 'il y a 1 heure' : 'il y a ' . $diff->h . ' heures';
        }
        if ($diff->i > 0) {
            return $diff->i === 1 ? 'il y a 1 minute' : 'il y a ' . $diff->i . ' minutes';
        }

        return 'à l\'instant';
    }

    public function readingTime(string $content): string
    {
        $wordCount = str_word_count(strip_tags($content));
        $minutes = max(1, ceil($wordCount / 250));
        return $minutes . ' min de lecture';
    }

    public function truncateWords(string $text, int $words = 30, string $suffix = '...'): string
    {
        $text = strip_tags($text);
        $wordArray = explode(' ', $text);
        
        if (count($wordArray) <= $words) {
            return $text;
        }

        return implode(' ', array_slice($wordArray, 0, $words)) . $suffix;
    }

    public function pluralize(int $count, string $singular, string $plural): string
    {
        return $count === 1 ? $singular : $plural;
    }
}
