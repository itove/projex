<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\File;
use App\Entity\LifecycleStageInterface;
use App\Entity\User;
use App\Service\FilePreviewRenderService;
use App\Service\FilePreviewService;
use App\Service\FileViewerService;
use App\Service\Lifecycle\LifecycleStageAttachmentCatalog;
use App\Service\Lifecycle\ProjectLifecycleStageRegistry;
use App\Service\OrgAccessService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Attribute\Route;
use Vich\UploaderBundle\Form\Type\VichFileType;
use Vich\UploaderBundle\Storage\StorageInterface;

class FileCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly StorageInterface $storage,
        private readonly EntityManagerInterface $entityManager,
        private readonly ProjectLifecycleStageRegistry $stageRegistry,
        private readonly FilePreviewService $filePreviewService,
        private readonly FilePreviewRenderService $filePreviewRenderService,
        private readonly FileViewerService $fileViewerService,
        private readonly OrgAccessService $orgAccessService,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return File::class;
    }

    #[Route('/admin/file/download/{fileId}', name: 'admin_file_download')]
    public function download(int $fileId): BinaryFileResponse
    {
        $file = $this->findFileOrFail($fileId);

        $response = new BinaryFileResponse($this->resolveFilePath($file));
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $file->getOriginalName() ?? $file->getFileName() ?? 'download'
        );

        return $response;
    }

    #[Route('/admin/file/inline/{fileId}', name: 'admin_file_inline')]
    public function inline(int $fileId): BinaryFileResponse
    {
        $file = $this->findFileOrFail($fileId);
        $filePath = $this->resolveFilePath($file);

        $response = new BinaryFileResponse($filePath);
        $response->headers->set('Content-Type', $this->filePreviewRenderService->resolveMimeType($file, $filePath));
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_INLINE,
            $file->getFileName() ?? 'preview'
        );

        return $response;
    }

    #[Route('/admin/file/preview/{fileId}', name: 'admin_file_preview')]
    public function preview(int $fileId): Response
    {
        $file = $this->findFileOrFail($fileId);
        $filePath = $this->resolveFilePath($file);

        if (!$this->filePreviewRenderService->canRenderHtml($file)) {
            throw $this->createNotFoundException('Preview is not available for this file type.');
        }

        try {
            $html = $this->filePreviewRenderService->renderHtml($file, $filePath);
        } catch (\Throwable $exception) {
            throw $this->createNotFoundException('Unable to render file preview.', $exception);
        }

        return new Response($html);
    }

    #[Route('/admin/file/viewer/{fileId}', name: 'admin_file_viewer')]
    public function fileViewer(int $fileId): Response
    {
        $file = $this->findFileOrFail($fileId);
        $this->resolveFilePath($file);

        if (!$this->fileViewerService->supports($file)) {
            throw $this->createNotFoundException('File Viewer does not support this file type.');
        }

        if (!$this->fileViewerService->isBuilt()) {
            return $this->render('admin/file/file_viewer_missing.html.twig', [
                'file' => $file,
                'downloadUrl' => $this->generateUrl('admin_file_download', ['fileId' => $fileId]),
            ]);
        }

        return $this->render('admin/file/file_viewer.html.twig', [
            'file' => $file,
            'extension' => $this->filePreviewService->getExtension($file),
            'inlineUrl' => $this->generateUrl('admin_file_inline', ['fileId' => $fileId]),
            'downloadUrl' => $this->generateUrl('admin_file_download', ['fileId' => $fileId]),
            'entrypointScript' => $this->fileViewerService->getEntrypointScriptPath(),
            'entrypointCss' => $this->fileViewerService->getEntrypointCssPaths(),
        ]);
    }

    #[Route('/admin/file/modal-preview/{fileId}', name: 'admin_file_modal_preview')]
    public function modalPreview(int $fileId): Response
    {
        $file = $this->findFileOrFail($fileId);
        $flags = $this->buildPreviewFlags($file);

        $response = $this->render(
            'admin/file/_preview_modal_body.html.twig',
            array_merge(['file' => $file], $flags)
        );

        $openUrl = match (true) {
            $flags['file_viewer_available'] => $this->generateUrl('admin_file_viewer', ['fileId' => $fileId]),
            \in_array($flags['preview_type'], [
                FilePreviewService::TYPE_PDF,
                FilePreviewService::TYPE_IMAGE,
                FilePreviewService::TYPE_TEXT,
                FilePreviewService::TYPE_VIDEO,
                FilePreviewService::TYPE_AUDIO,
            ], true) => $this->generateUrl('admin_file_inline', ['fileId' => $fileId]),
            $flags['preview_type'] === FilePreviewService::TYPE_OFFICE && $flags['office_preview_available']
                => $this->generateUrl('admin_file_preview', ['fileId' => $fileId]),
            default => null,
        };

        if ($openUrl !== null) {
            $response->headers->set('X-File-Open-Url', $openUrl);
        }

        return $response;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('文件')
            ->setEntityLabelInPlural('文件管理')
            ->setPageTitle(Crud::PAGE_INDEX, '文件管理')
            ->setPageTitle(Crud::PAGE_NEW, '上传文件')
            ->setPageTitle(Crud::PAGE_EDIT, '编辑文件')
            ->setPageTitle(Crud::PAGE_DETAIL, '文件详情')
            ->setDefaultSort(['createdAt' => 'DESC'])
            ->setPaginatorPageSize(20)
            ->setSearchFields(['originalName', 'fileName', 'description', 'category'])
            ->overrideTemplate('crud/index', 'admin/file/index.html.twig')
            ->overrideTemplate('crud/detail', 'admin/file/detail.html.twig');
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('file', '文件')
            ->setFormType(VichFileType::class)
            ->setFormTypeOptions([
                'download_label' => '下载',
                'delete_label' => '删除',
                'translation_domain' => false,
            ])
            ->onlyOnForms()
            ->setRequired($pageName === Crud::PAGE_NEW)
            ->setHelp('最大文件大小: 50MB');

        yield TextField::new('originalName', '原始文件名')
            ->hideOnForm()
            ->setColumns(6);

        yield TextField::new('fileName', '存储文件名')
            ->hideOnForm()
            ->hideOnIndex()
            ->setColumns(6);

        yield TextField::new('mimeType', '文件类型')
            ->hideOnForm()
            ->setColumns(4);

        yield IntegerField::new('fileSize', '文件大小')
            ->hideOnForm()
            ->hideOnIndex()
            ->setColumns(4);

        yield TextField::new('fileSizeFormatted', '文件大小')
            ->onlyOnDetail()
            ->setColumns(4);

        yield ChoiceField::new('category', '文档类型')
            ->setChoices($this->categoryChoices())
            ->onlyOnForms()
            ->setRequired(false)
            ->formatValue(static fn (?string $value): string => $value !== null && $value !== ''
                ? (LifecycleStageAttachmentCatalog::labelForKey($value) ?? $value)
                : '—');

        yield TextareaField::new('description', '描述')
            ->setRequired(false)
            ->hideOnIndex();

        yield DateTimeField::new('createdAt', '上传时间')
            ->hideOnForm()
            ->setColumns(6);

        yield DateTimeField::new('updatedAt', '更新时间')
            ->hideOnForm()
            ->hideOnIndex()
            ->setColumns(6);
    }

    public function configureActions(Actions $actions): Actions
    {
        $downloadAction = Action::new('download', '下载', 'fa fa-download')
            ->linkToUrl(fn (File $file) => $this->generateUrl('admin_file_download', ['fileId' => $file->getId()]));

        $previewAction = Action::new('previewModal', '预览', 'fa fa-eye')
            ->linkToUrl('javascript:void(0)')
            ->setCssClass('js-file-preview-open')
            ->setTemplatePath('admin/file/action_preview.html.twig');

        return $actions
            ->add(Crud::PAGE_INDEX, $downloadAction)
            ->add(Crud::PAGE_INDEX, $previewAction)
            ->add(
                Crud::PAGE_DETAIL,
                Action::new('download', '下载', 'fa fa-download')
                    ->linkToUrl(fn (File $file) => $this->generateUrl('admin_file_download', ['fileId' => $file->getId()]))
                    ->setCssClass('btn btn-primary')
                    ->renderAsButton()
            )
            ->setPermission(Action::DELETE, 'ROLE_ADMIN');
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(TextFilter::new('category', '分类'))
            ->add(TextFilter::new('mimeType', '文件类型'))
            ->add(DateTimeFilter::new('createdAt', '上传时间'));
    }

    public function createIndexQueryBuilder(
        SearchDto $searchDto,
        EntityDto $entityDto,
        FieldCollection $fields,
        FilterCollection $filters,
    ): QueryBuilder {
        $qb = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);

        $user = $this->getUser();
        if ($user instanceof User) {
            $this->orgAccessService->applyLifecycleAttachmentOrgScope($qb, $user, 'entity', false);
        }

        return $qb;
    }

    public function detail(AdminContext $context): KeyValueStore|Response
    {
        $this->denyUnlessCanViewAttachment($context->getEntity()->getInstance());

        return parent::detail($context);
    }

    public function edit(AdminContext $context): KeyValueStore|Response
    {
        $this->denyUnlessCanViewAttachment($context->getEntity()->getInstance());

        return parent::edit($context);
    }

    public function delete(AdminContext $context): KeyValueStore|Response
    {
        $this->denyUnlessCanViewAttachment($context->getEntity()->getInstance());

        return parent::delete($context);
    }

    public function configureResponseParameters(KeyValueStore $responseParameters): KeyValueStore
    {
        if (Crud::PAGE_DETAIL === $responseParameters->get('pageName')) {
            $entity = $responseParameters->get('entity');
            $file = $entity->getInstance();

            if ($file instanceof File) {
                foreach ($this->buildPreviewFlags($file) as $key => $value) {
                    $responseParameters->set($key, $value);
                }
            }
        }

        return $responseParameters;
    }

    /**
     * @return array{
     *     preview_type: string,
     *     file_viewer_available: bool,
     *     file_viewer_built: bool,
     *     office_preview_available: bool
     * }
     */
    private function buildPreviewFlags(File $file): array
    {
        $built = $this->fileViewerService->isBuilt();

        return [
            'preview_type' => $this->filePreviewService->getPreviewType($file),
            'file_viewer_available' => $this->fileViewerService->supports($file) && $built,
            'file_viewer_built' => $built,
            'office_preview_available' => $this->filePreviewRenderService->canRenderHtml($file),
        ];
    }

    /**
     * When embedded in a lifecycle stage form, limit category choices to that
     * stage's attachment catalog. Standalone file admin keeps the merged list.
     *
     * @return array<string, string>
     */
    private function categoryChoices(): array
    {
        $context = $this->getContext();
        if ($context === null) {
            return LifecycleStageAttachmentCatalog::allChoiceMap();
        }

        $entity = $context->getEntity()->getInstance();
        if ($entity instanceof LifecycleStageInterface) {
            $definition = $this->stageRegistry->findByEntityClass($entity::class);
            if ($definition !== null) {
                return LifecycleStageAttachmentCatalog::choiceMapForStage($definition->key);
            }
        }

        return LifecycleStageAttachmentCatalog::allChoiceMap();
    }

    private function findFileOrFail(int $fileId): File
    {
        $file = $this->entityManager->getRepository(File::class)->find($fileId);

        if (!$file instanceof File) {
            throw $this->createNotFoundException('File not found');
        }

        $this->denyUnlessCanViewAttachment($file);

        return $file;
    }

    private function denyUnlessCanViewAttachment(?object $entity): void
    {
        if (!$entity instanceof File) {
            return;
        }

        $user = $this->getUser();
        if (!$user instanceof User || !$this->orgAccessService->canViewLifecycleAttachment($user, $entity)) {
            throw $this->createAccessDeniedException('无权访问该文件。');
        }
    }

    private function resolveFilePath(File $file): string
    {
        $filePath = $this->storage->resolvePath($file, 'file');

        if ($filePath === null || !is_file($filePath)) {
            throw $this->createNotFoundException('File not found on disk');
        }

        return $filePath;
    }
}
