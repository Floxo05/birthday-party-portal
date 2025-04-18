<?php

declare(strict_types=1);

namespace App\Tests\Service\Media\MediaStorage;

use App\Entity\Party;
use App\Service\Media\MediaStorage\MediaStorage;
use League\Flysystem\FilesystemException;
use League\Flysystem\FilesystemOperator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Uid\Uuid;

class MediaStorageTest extends TestCase
{
    public function testGetStoragePathReturnsCorrectFormat(): void
    {
        $filesystem = $this->createMock(FilesystemOperator::class);
        $storage = new MediaStorage($filesystem);

        $party = $this->createMock(Party::class);
        $party->method('getId')->willReturn(Uuid::v4());

        $file = $this->createMock(UploadedFile::class);
        $file->method('guessExtension')->willReturn('mp4');

        $path = $storage->getStoragePath($file, $party);

        $this->assertMatchesRegularExpression('#^[a-f0-9\-]+/[a-f0-9\-]+\.mp4$#i', $path);
    }

    /**
     * @throws FilesystemException
     */
    public function testStoreCallsWriteStreamWithCorrectArguments(): void
    {
        $filesystem = $this->createMock(FilesystemOperator::class);
        $storage = new MediaStorage($filesystem);

        $file = $this->createMock(UploadedFile::class);

        // Create real temp file to allow fopen to succeed
        $tempPath = tempnam(sys_get_temp_dir(), 'test_media');
        file_put_contents($tempPath, 'hello world');

        $file->method('getPathname')->willReturn($tempPath);

        $filesystem
            ->expects($this->once())
            ->method('writeStream')
            ->with('some/path.txt', $this->isType('resource'));

        $storage->store($file, 'some/path.txt');

        unlink($tempPath); // Clean up
    }
}