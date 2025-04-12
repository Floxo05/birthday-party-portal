<?php

declare(strict_types=1);

namespace App\Service\Media\MediaFactory;

use App\Entity\Media;
use App\Entity\Party;
use App\Entity\User;
use Symfony\Component\HttpFoundation\File\UploadedFile;

interface MediaFactoryInterface
{
    public function create(
        Party $party,
        User $user,
        UploadedFile $file,
        string $storagePath
    ): Media;
}
