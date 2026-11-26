<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Service\DashboardData;
use App\Service\OrgProjectOverviewService;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;

#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
class DashboardController extends AbstractDashboardController
{
    public function __construct(
        private readonly DashboardData $dashboardData,
        private readonly OrgProjectOverviewService $orgProjectOverviewService,
    ) {
    }

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

    #[AdminRoute(path: '/org-overview', name: 'org_project_overview')]
    public function orgProjectOverview(): Response
    {
        $user = $this->getUser();

        return $this->render('admin/org/overview.html.twig', [
            'tree' => $this->orgProjectOverviewService->getTree($user instanceof User ? $user : null),
        ]);
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('项目全周期智能管理系统');
    }

    public function configureAssets(): Assets
    {
        return Assets::new()
            ->addCssFile('styles/admin.css');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('项目展示主页', 'fa fa-home');
        yield MenuItem::section('项目管理');
        yield MenuItem::linkToRoute('公司项目总览', 'fa fa-sitemap', 'admin_org_project_overview');
        yield MenuItem::linkTo(ProjectCrudController::class, '项目基础信息', 'fa fa-project-diagram');
        yield MenuItem::linkTo(ProjectTaskCrudController::class, '项目任务', 'fa fa-tasks');
        yield MenuItem::linkTo(ProjectProgressReportCrudController::class, '进度报告', 'fa fa-chart-line');
        yield MenuItem::section('项目生命周期');
        yield MenuItem::linkTo(PreliminaryDecisionCrudController::class, '前期决策流程', 'fa fa-clipboard-check');
        yield MenuItem::linkTo(ProjectApprovalCrudController::class, '立项流程', 'fa fa-file-signature');
        yield MenuItem::linkTo(PlanningDesignCrudController::class, '规划设计流程', 'fa fa-drafting-compass');
        yield MenuItem::linkTo(ConstructionPreparationCrudController::class, '施工准备流程', 'fa fa-hard-hat');
        yield MenuItem::linkTo(ConstructionImplementationCrudController::class, '施工实施流程', 'fa fa-cogs');
        yield MenuItem::linkTo(CompletionAcceptanceCrudController::class, '竣工验收流程', 'fa fa-check-circle');
        yield MenuItem::linkTo(SettlementAccountsCrudController::class, '结算流程', 'fa fa-file-invoice-dollar');
        yield MenuItem::section('基础数据');
        yield MenuItem::linkTo(ProjectTypeCrudController::class, '项目类型', 'fa fa-tags');
        yield MenuItem::linkTo(ProjectSubtypeCrudController::class, '项目子类型', 'fa fa-tag');
        yield MenuItem::section('文件管理');
        yield MenuItem::linkTo(FileCrudController::class, '文件', 'fa fa-file');
        yield MenuItem::linkTo(ImageCrudController::class, '图片', 'fa fa-image');
        yield MenuItem::section('权限管理')->setPermission('ROLE_SYSTEM_ADMIN');
        yield MenuItem::linkTo(OrgCrudController::class, '组织机构', 'fa fa-building')
            ->setPermission('ROLE_SYSTEM_ADMIN');
        yield MenuItem::linkTo(UserCrudController::class, '用户', 'fa fa-users')
            ->setPermission('ROLE_SYSTEM_ADMIN');
        yield MenuItem::linkTo(RoleCrudController::class, '角色', 'fa fa-user-tag')
            ->setPermission('ROLE_SYSTEM_ADMIN');
        yield MenuItem::linkTo(PermissionCrudController::class, '权限', 'fa fa-key')
            ->setPermission('ROLE_SYSTEM_ADMIN');

        yield MenuItem::section('系统设置');
        yield MenuItem::linkTo(ChpwCrudController::class, '修改密码', 'fas fa-key')
            ->setAction('edit')
            ->setEntityId($this->getUser()->getId());
    }

    public function configureCrud(): Crud
    {
        return Crud::new()
            // ->showEntityActionsInlined()
            ->setTimezone("Asia/Shanghai")
            ->setDateTimeFormat("yyyy/MM/dd HH:mm")
            ->setDefaultSort(["id" => "DESC"])
            ->renderContentMaximized()
            ->setDefaultRowAction(Action::DETAIL)
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
