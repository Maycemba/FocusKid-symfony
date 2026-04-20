<?php
namespace App\Controller;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
final class FrontController extends AbstractController
{

#[Route('/index', name: 'app_index')]
public function index(): Response
{
    return $this->render('front/index.html.twig');
}
}