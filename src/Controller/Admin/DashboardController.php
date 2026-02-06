<?php

namespace App\Controller\Admin;

use App\Entity\File;
use App\Entity\Image;
use App\Entity\PreliminaryDecision;
use App\Entity\Project;
use App\Entity\ProjectType;
use App\Entity\ProjectSubtype;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use App\Service\DashboardData;

#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
class DashboardController extends AbstractDashboardController
{
    public function __construct(private DashboardData $dashboardData) {}

    public function index(): Response
    {
        return $this->render(
            "dashboard.html.twig",
            $this->dashboardData->get(),
        );

        // return parent::index();

        // Option 1. You can make your dashboard redirect to some common page of your backend
        //
        // 1.1) If you have enabled the "pretty URLs" feature:
        // return $this->redirectToRoute('admin_user_index');
        //
        // 1.2) Same example but using the "ugly URLs" that were used in previous EasyAdmin versions:
        // $adminUrlGenerator = $this->container->get(AdminUrlGenerator::class);
        // return $this->redirect($adminUrlGenerator->setController(OneOfYourCrudController::class)->generateUrl());

        // Option 2. You can make your dashboard redirect to different pages depending on the user
        //
        // if ('jane' === $this->getUser()->getUsername()) {
        //     return $this->redirectToRoute('...');
        // }

        // Option 3. You can render some custom template to display a proper dashboard with widgets, etc.
        // (tip: it's easier if your template extends from @EasyAdmin/page/content.html.twig)
        //
        // return $this->render('some/path/my-dashboard.html.twig');
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Projex');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');
        yield MenuItem::section('项目管理');
        yield MenuItem::linkToCrud('项目基础信息', 'fa fa-project-diagram', Project::class);
        yield MenuItem::section('项目生命周期');
        yield MenuItem::linkToCrud('前期决策流程', 'fa fa-clipboard-check', PreliminaryDecision::class);
        yield MenuItem::section('基础数据');
        yield MenuItem::linkToCrud('项目类型', 'fa fa-tags', ProjectType::class);
        yield MenuItem::linkToCrud('项目子类型', 'fa fa-tag', ProjectSubtype::class);
        yield MenuItem::section('文件管理');
        yield MenuItem::linkToCrud('文件', 'fa fa-file', File::class);
        yield MenuItem::linkToCrud('图片', 'fa fa-image', Image::class);
    }

    public function configureCrud(): Crud
    {
        return Crud::new()
            // ->showEntityActionsInlined()
            ->setTimezone("Asia/Shanghai")
            ->setDateTimeFormat("yyyy/MM/dd HH:mm")
            ->setDefaultSort(["id" => "DESC"])
        ;
    }

    // public function configureActions(): Actions
    // {
    //     return Actions::new()
    //         // ->disable('delete')
    //         // ->add(Crud::PAGE_NEW, Action::SAVE_AND_RETURN)
    //         // ->add(Crud::PAGE_NEW, Action::SAVE_AND_ADD_ANOTHER)
    //         // ->add(Crud::PAGE_EDIT, Action::SAVE_AND_RETURN)
    //         // ->add(Crud::PAGE_EDIT, Action::SAVE_AND_CONTINUE)
    //     ;
    // }
}
