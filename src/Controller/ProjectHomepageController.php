<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Project Homepage Controller (4.3 项目展示主页模块)
 * Redirects to admin dashboard which serves as the default homepage after login
 */
class ProjectHomepageController extends AbstractController
{
    #[Route('/', name: 'project_homepage', methods: ['GET'])]
    public function index(Request $request): Response
    {
        // Redirect to admin dashboard (4.3 核心定位：作为用户登录后的默认首页)
        return $this->redirectToRoute('admin', $request->query->all());
    }
}
