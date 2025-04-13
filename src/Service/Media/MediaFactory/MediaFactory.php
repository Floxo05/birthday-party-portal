<?php

declare(strict_types=1);

namespace App\Service\Media\MediaFactory;

use App\Entity\Media;
use App\Entity\Party;
use App\Entity\User;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class MediaFactory implements MediaFactoryInterface
{

    public function create(Party $party, User $user, UploadedFile $file, string $storagePath): Media
    {
        $media = new Media();

        $media->setParty($party);
        $media->setUploader($user);
        $media->setOriginalFilename($file->getClientOriginalName());
        $media->setMimeType($file->getMimeType() ?? 'application/octet-stream');
        $media->setSize($file->getSize() ?: 0);
        $media->setStoragePath($storagePath);
        $media->setUploadedAt(new \DateTimeImmutable());

        return $media;
    }
}