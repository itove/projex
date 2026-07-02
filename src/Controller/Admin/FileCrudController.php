<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\File;
use App\Entity\LifecycleStageInterface;
use App\Service\FilePreviewRenderService;
use App\Service\FilePreviewService;
use App\Service\Lifecycle\LifecycleStageAttachmentCatalog;
use App\Service\Lifecycle\ProjectLifecycleStageRegistry;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
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
            ->linkToUrl(fn (File $file) => $this->generateUrl('admin_file_download', ['fileId' => $file->getId()]))
            ->setCssClass('btn btn-primary')
            ->displayAsButton();

        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_DETAIL, $downloadAction)
            ->setPermission(Action::DELETE, 'ROLE_ADMIN');
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(TextFilter::new('category', '分类'))
            ->add(TextFilter::new('mimeType', '文件类型'))
            ->add(DateTimeFilter::new('createdAt', '上传时间'));
    }

    public function configureResponseParameters(KeyValueStore $responseParameters): KeyValueStore
    {
        if (Crud::PAGE_DETAIL === $responseParameters->get('pageName')) {
            $entity = $responseParameters->get('entity');
            $file = $entity->getInstance();

            if ($file instanceof File) {
                $previewType = $this->filePreviewService->getPreviewType($file);
                $responseParameters->set('preview_type', $previewType);
                $responseParameters->set('office_preview_available', $this->filePreviewRenderService->canRenderHtml($file));
            }
        }

        return $responseParameters;
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

        return $file;
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
