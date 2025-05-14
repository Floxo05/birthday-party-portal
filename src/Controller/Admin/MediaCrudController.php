<?php

namespace App\Controller\Admin;

use App\Entity\Media;
use App\Entity\User;
use App\Service\Media\MediaStorage\MediaStorage;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Override;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class MediaCrudController extends AbstractHostCrudController
{


    public function __construct(
        private readonly MediaStorage $mediaStorage,
        private readonly AdminUrlGenerator $adminUrlGenerator,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return Media::class;
    }


    public function configureFields(string $pageName): iterable
    {
        $context = $this->getContext();
        if ($context === null)
        {
            throw new \RuntimeException('Context must be set');
        }

        $user = $this->getUser();
        if (!$user instanceof User)
        {
            throw $this->createAccessDeniedException();
        }
        $userId = $user->getId();

        yield TextField::new('originalFilename')
            ->setLabel('Datei')
            ->formatValue(function ($value, $entity)
            {
                if (!$entity instanceof Media)
                {
                    return $value;
                }

                $url = $this->generateUrl('app_media_view', [
                    'id' => $entity->getId(),
                ], UrlGeneratorInterface::ABSOLUTE_URL);

                return sprintf('<a href="%s" target="_blank">%s</a>', $url, htmlspecialchars($value));
            })
            ->hideOnForm()
            ->renderAsHtml();

        yield TextField::new('mimeType', 'Mime Typ')
            ->hideOnForm();
        yield IntegerField::new('size', 'Größe in MiB')
            ->hideOnForm()
            ->formatValue(fn(int $size) => round($size / (1024 * 1024), 2));
        yield DateField::new('uploadedAt', 'Hochgeladen am')
            ->hideOnForm();

        yield AssociationField::new('party')
            ->setRequired(true)
            ->setQueryBuilder(function (QueryBuilder $queryBuilder) use ($userId)
            {
                $queryBuilder
                    ->innerJoin('entity.partyMembers', 'partyMembers')
                    ->innerJoin('partyMembers.user', 'user')
                    ->andWhere('user = :user')
                    ->setParameter('user', $userId, UuidType::NAME)
                    ->andWhere('partyMembers INSTANCE OF App\Entity\Host');
            }
            );


        yield AssociationField::new('uploader', 'Hochgeladen von')->hideOnForm();

        yield ImageField::new('storagePath')
            ->setLabel('Datei hochladen')
            ->setUploadDir('public') // just as placeholder; real dir is configured in upload_new
            ->setFormTypeOption('upload_new', function (UploadedFile $file) use ($context)
            {
                /** @var Media|null $media */
                $media = $context->getEntity()->getInstance();
                if ($media === null)
                {
                    throw new \UnexpectedValueException('Media must be set');
                }

                $party = $media->getParty();
                if ($party === null)
                {
                    throw new \UnexpectedValueException('Party must be set');
                }

                $user = $this->getUser();
                if (!$user instanceof User)
                {
                    throw new \UnexpectedValueException('User must be set');
                }

                $path = $this->mediaStorage->getStoragePath($file, $party);

                $this->mediaStorage->store($file, $path);

                $media->setStoragePath($path);
                $media->setOriginalFilename($file->getClientOriginalName());
                $media->setMimeType($file->getMimeType() ?? 'application/octet-stream');
                $media->setSize($file->getSize() ?: 0);
                $media->setUploadedAt(new \DateTimeImmutable());
                $media->setUploader($user);
            })
            ->setFileConstraints([])
            ->onlyOnForms();
    }

    #[Override]
    protected function getRedirectResponseAfterSave(AdminContext $context, string $action): RedirectResponse
    {
        return $this->redirect(
            $this->adminUrlGenerator->setAction(Action::INDEX)
                ->setEntityId($context->getEntity()->getPrimaryKeyValue())
                ->generateUrl()
        );
    }
}
