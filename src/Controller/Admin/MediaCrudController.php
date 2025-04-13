<?php

namespace App\Controller\Admin;

use App\Entity\Media;
use App\Entity\User;
use App\Service\Media\MediaStorage\MediaStorage;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Form\Type\FileUploadType;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class MediaCrudController extends AbstractCrudController
{


    public function __construct(
        private readonly MediaStorage $mediaStorage,
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

        yield TextField::new('originalFilename')
            ->setLabel('Datei')
            ->formatValue(function ($value, $entity)
            {
                if (!$entity instanceof Media)
                {
                    return $value;
                }

                $url = $this->generateUrl('app_media_download', [
                    'id' => $entity->getId(),
                ], UrlGeneratorInterface::ABSOLUTE_URL);

                return sprintf('<a href="%s" target="_blank">%s</a>', $url, htmlspecialchars($value));
            })
            ->hideOnForm()
            ->renderAsHtml();

        yield TextField::new('mimeType')
            ->hideOnForm();
        yield IntegerField::new('size')
            ->hideOnForm();
        yield DateField::new('uploadedAt')
            ->hideOnForm();

        yield AssociationField::new('party')
            ->setRequired(true);
        yield AssociationField::new('uploader')->hideOnForm();

        yield TextField::new('storagePath')
            ->setLabel('Datei hochladen')
            ->setFormType(FileUploadType::class)
            ->setFormTypeOption('upload_dir', 'media')
            ->setFormTypeOption('upload_new', function (UploadedFile $file) use ($context)
            {
                /** @var Media $media */
                $media = $context->getEntity()->getInstance();
                if ($media === null)
                {
                    throw new \RuntimeException('Media must be set');
                }

                $user = $this->getUser();
                if (!$user instanceof User)
                {
                    throw new \RuntimeException('User must be set');
                }

                $path = $this->mediaStorage->getStoragePath($file, $media->getParty());

                $this->mediaStorage->store($file, $path);

                $media->setStoragePath($path);
                $media->setOriginalFilename($file->getClientOriginalName());
                $media->setMimeType($file->getMimeType() ?? 'application/octet-stream');
                $media->setSize($file->getSize() ?? 0);
                $media->setUploadedAt(new \DateTimeImmutable());
                $media->setUploader($user);
            })
            ->onlyOnForms();
    }
}
