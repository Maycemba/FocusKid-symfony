<?php

return [
    // Core Symfony
    Symfony\Bundle\FrameworkBundle\FrameworkBundle::class => ['all' => true],
    Symfony\Bundle\SecurityBundle\SecurityBundle::class => ['all' => true],
    Symfony\Bundle\TwigBundle\TwigBundle::class => ['all' => true],
    Symfony\Bundle\MonologBundle\MonologBundle::class => ['all' => true],

    // Doctrine
    Doctrine\Bundle\DoctrineBundle\DoctrineBundle::class => ['all' => true],
    Doctrine\Bundle\MigrationsBundle\DoctrineMigrationsBundle::class => ['all' => true],

    // Twig extensions
    Twig\Extra\TwigExtraBundle\TwigExtraBundle::class => ['all' => true],

    // UX / Front
    Symfony\UX\StimulusBundle\StimulusBundle::class => ['all' => true],
    Symfony\UX\Turbo\TurboBundle::class => ['all' => true],
    Symfony\UX\Chartjs\ChartjsBundle::class => ['all' => true],

    // Extra bundles
    Knp\Bundle\PaginatorBundle\KnpPaginatorBundle::class => ['all' => true],
    Knp\Bundle\SnappyBundle\KnpSnappyBundle::class => ['all' => true],

    // Media / Upload
    Vich\UploaderBundle\VichUploaderBundle::class => ['all' => true],

    // Doctrine extensions
    Stof\DoctrineExtensionsBundle\StofDoctrineExtensionsBundle::class => ['all' => true],

    // Assets
    Symfony\WebpackEncoreBundle\WebpackEncoreBundle::class => ['all' => true],

    // API / CORS
    ApiPlatform\Symfony\Bundle\ApiPlatformBundle::class => ['all' => true],
    Nelmio\CorsBundle\NelmioCorsBundle::class => ['all' => true],

    // Search
    FOS\ElasticaBundle\FOSElasticaBundle::class => ['all' => true],

    // Calendar
    CalendarBundle\CalendarBundle::class => ['all' => true],
];