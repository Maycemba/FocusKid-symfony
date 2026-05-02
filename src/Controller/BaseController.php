<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class BaseController extends AbstractController
{
    protected function getCarouselSlides(): array
    {
        return [
            [
                'image' => 'base-front/img/carousel-1.jpg',
                'title' => 'The Best Kindergarten School For Your Child',
                'description' => 'Vero elitr justo clita lorem. Ipsum dolor at sed stet sit diam no. Kasd rebum ipsum et diam justo clita et kasd rebum sea elitr.',
                'learn_more_link' => '#',
                'classes_link' => '#'
            ],
            [
                'image' => 'base-front/img/carousel-2.jpg',
                'title' => 'Make A Brighter Future For Your Child',
                'description' => 'Vero elitr justo clita lorem. Ipsum dolor at sed stet sit diam no. Kasd rebum ipsum et diam justo clita et kasd rebum sea elitr.',
                'learn_more_link' => '#',
                'classes_link' => '#'
            ]
        ];
    }
    
    protected function getBaseTemplateData(): array
    {
        return [
            'carousel_slides' => $this->getCarouselSlides(),
            'facilities' => [],
            'about' => [],
            'call_to_action' => [],
            'classes' => [],
            'teachers' => [],
            'testimonials' => [],
            'contact' => [],
            'social' => [],
            'gallery_images' => [],
            'site_name' => 'FocusKid'
        ];
    }
}