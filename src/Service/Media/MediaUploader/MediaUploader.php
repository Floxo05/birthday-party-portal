<?php

declare(strict_types=1);

namespace App\Service\Media\MediaUploader;

use App\Entity\Party;
use App\Entity\User;
use App\Service\Media\MediaFactory\MediaFactoryInterface;
use App\Service\Media\MediaStorage\MediaStorageInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

readonly class MediaUploader implements MediaUploaderInterface
{
    public function __construct(
        private EntityManagerInterface $em,
        private MediaFactoryInterface $mediaFactory,
        private MediaStorageInterface $mediaStorage
    ) {
    }


    public function upload(UploadedFile $file, Party $party, User $user): void
    {
        // Pfad erzeugen
        $storagePath = $this->mediaStorage->getStoragePath($file, $party);

        // Datei physisch speichern (in minIO ablegen)
        $this->mediaStorage->store($file, $storagePath);

        // Media-Entity erzeugen
        $media = $this->mediaFactory->create($party, $user, $file, $storagePath);

        // Persistieren
        $this->em->persist($media);
        $this->em->flush();
    }
}