<?php

declare(strict_types=1);

namespace App\Tests\Service\Media\MediaUploader;

use App\Entity\Media;
use App\Entity\Party;
use App\Entity\User;
use App\Service\Media\MediaFactory\MediaFactoryInterface;
use App\Service\Media\MediaStorage\MediaStorageInterface;
use App\Service\Media\MediaUploader\MediaUploader;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class MediaUploaderTest extends TestCase
{
    public function testUploadCallsAllDependenciesCorrectly(): void
    {
        $file = $this->createMock(UploadedFile::class);
        $party = $this->createMock(Party::class);
        $user = $this->createMock(User::class);
        $media = $this->createMock(Media::class);

        $em = $this->createMock(EntityManagerInterface::class);
        $factory = $this->createMock(MediaFactoryInterface::class);
        $storage = $this->createMock(MediaStorageInterface::class);

        $storagePath = 'some/uuid-path.mp4';

        // Storage Pfad erzeugen
        $storage->expects($this->once())
            ->method('getStoragePath')
            ->with($file, $party)
            ->willReturn($storagePath);

        // Datei speichern
        $storage->expects($this->once())
            ->method('store')
            ->with($file, $storagePath);

        // Media erzeugen
        $factory->expects($this->once())
            ->method('create')
            ->with($party, $user, $file, $storagePath)
            ->willReturn($media);

        // Speichern in DB
        $em->expects($this->once())->method('persist')->with($media);
        $em->expects($this->once())->method('flush');

        $uploader = new MediaUploader($em, $factory, $storage);
        $uploader->upload($file, $party, $user);
    }
}
