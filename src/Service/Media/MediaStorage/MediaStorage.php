<?php

declare(strict_types=1);

namespace App\Service\Media\MediaStorage;

use App\Entity\Party;
use League\Flysystem\FilesystemException;
use League\Flysystem\FilesystemOperator;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Uid\Uuid;

readonly class MediaStorage implements MediaStorageInterface
{
    public function __construct(
        private FilesystemOperator $mediaStorage
    ) {
    }

    public function getStoragePath(UploadedFile $file, Party $party): string
    {
        $extension = $file->guessExtension() ?? 'bin';
        return sprintf('%s/%s.%s', $party->getId(), Uuid::v4(), $extension);
    }

    /**
     * @throws FilesystemException
     */
    public function store(UploadedFile $file, string $path): void
    {
        $stream = fopen($file->getPathname(), 'r');
        $this->mediaStorage->writeStream($path, $stream);
        fclose($stream);
    }
}