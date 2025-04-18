<?php

declare(strict_types=1);

namespace App\Service\Media\MediaUploader;

use App\Entity\Party;
use App\Entity\User;
use Symfony\Component\HttpFoundation\File\UploadedFile;

interface MediaUploaderInterface
{
    public function upload(UploadedFile $file, Party $party, User $user): void;
}