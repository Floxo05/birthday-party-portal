<?php

namespace App\Controller;

use App\Entity\Media;
use League\Flysystem\FilesystemException;
use League\Flysystem\FilesystemOperator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;

final class MediaController extends AbstractController
{
    /**
     * @throws FilesystemException
     */
    #[Route('/media/view/{id}', name: 'app_media_view')]
    public function view(Media $media, FilesystemOperator $mediaStorage): Response
    {
        $this->denyAccessUnlessGranted('view', $media);

        if (!$mediaStorage->fileExists($media->getStoragePath()))
        {
            throw $this->createNotFoundException();
        }

        $stream = $mediaStorage->readStream($media->getStoragePath());

        return new StreamedResponse(function () use ($stream)
        {
            fpassthru($stream);
            fclose($stream);
        }, 200, [
            'Content-Type' => $media->getMimeType(),
            'Content-Disposition' => 'inline; filename="' . $media->getOriginalFilename() . '"',
            'Content-Length' => $media->getSize(),
            'Accept-Ranges' => 'bytes',
        ]);
    }

    /**
     * @throws FilesystemException
     */
    #[Route('/media/download/{id}', name: 'app_media_download')]
    public function download(Media $media, FilesystemOperator $mediaStorage): Response
    {
        $this->denyAccessUnlessGranted('view', $media);

        if (!$mediaStorage->fileExists($media->getStoragePath()))
        {
            throw $this->createNotFoundException();
        }

        $stream = $mediaStorage->readStream($media->getStoragePath());

        return new StreamedResponse(function () use ($stream)
        {
            fpassthru($stream);
            fclose($stream);
        }, 200, [
            'Content-Type' => $media->getMimeType(),
            'Content-Disposition' => 'attachment; filename="' . $media->getOriginalFilename() . '"',
            'Content-Length' => $media->getSize(),
        ]);
    }
}
