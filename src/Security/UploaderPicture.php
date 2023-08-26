<?php

namespace App\Security;

use Symfony\Component\DependencyInjection\ContainerInterface;

class UploaderPicture
{

    public function __construct(
        private ContainerInterface $container
        )
    {
        
    }

    public function upload($picture, $folder)
    {
        $folder = $this->container->getParameter('profile.folder');
        $extension = $picture->guessExtension() ?? 'bin';
        $filename = uniqid() . '.' . $extension;
        $picture->move($folder, $filename);

        return $this->container->getParameter('profile.folder.public_path') . '/' . $filename;
    }
}
