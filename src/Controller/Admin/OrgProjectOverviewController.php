<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\User;
use App\Service\OrgProjectOverviewService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class OrgProjectOverviewController extends AbstractController
{
    public function __construct(
        private readonly OrgProjectOverviewService $overviewService,
    ) {
    }

    #[Route('/admin/org-project-overview', name: 'admin_org_project_overview', methods: ['GET'])]
    public function index(): Response
    {
        $user = $this->getUser();
        $tree = $this->overviewService->getTree($user instanceof User ? $user : null);

        return $this->render('admin/org/overview.html.twig', [
            'tree' => $tree,
        ]);
    }
}
