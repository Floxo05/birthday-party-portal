<?php

declare(strict_types=1);

namespace App\Service\Media\MediaStorage;

use App\Entity\Party;
use Symfony\Component\HttpFoundation\File\UploadedFile;

interface MediaStorageInterface
{
    public function getStoragePath(UploadedFile $file, Party $party): string;

    public function store(UploadedFile $file, string $path): void;
}