<?php

declare(strict_types=1);

namespace App\Tests\Service\Media\MediaFactory;

use App\Entity\Media;
use App\Entity\Party;
use App\Entity\User;
use App\Service\Media\MediaFactory\MediaFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class MediaFactoryTest extends TestCase
{
    public function testCreatesCorrectMediaObject(): void
    {
        $factory = new MediaFactory();

        $party = $this->createMock(Party::class);
        $uploader = $this->createMock(User::class);
        $file = $this->createMock(UploadedFile::class);

        $file->method('getClientOriginalName')->willReturn('video.mp4');
        $file->method('getMimeType')->willReturn('video/mp4');
        $file->method('getSize')->willReturn(1024);

        $storagePath = 'party-uuid/uuid.mp4';

        $media = $factory->create($party, $uploader, $file, $storagePath);

        $this->assertInstanceOf(Media::class, $media);
        $this->assertSame('video.mp4', $media->getOriginalFilename());
        $this->assertSame('video/mp4', $media->getMimeType());
        $this->assertSame(1024, $media->getSize());
        $this->assertSame($storagePath, $media->getStoragePath());
        $this->assertSame($party, $media->getParty());
        $this->assertSame($uploader, $media->getUploader());
        $this->assertInstanceOf(\DateTimeImmutable::class, $media->getUploadedAt());
    }
}