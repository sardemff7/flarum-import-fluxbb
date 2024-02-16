<?php

namespace ArchLinux\ImportFluxBB\Importer;

use Flarum\Foundation\Paths;
use Flarum\User\AvatarUploader;
use Illuminate\Database\Capsule\Manager;
use Illuminate\Contracts\Filesystem\Factory;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Intervention\Image\ImageManagerStatic as Image;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;

class Avatars
{
    private Manager $database;
    private ContainerInterface $container;
    private String $avatarsDir;

    public function __construct(Manager $database, ContainerInterface $container, Factory $filesystemFactory)
    {
        $this->database = $database;
        $this->container = $container;
        $this->uploadDir = $filesystemFactory->disk('flarum-avatars');
    }

    public function execute(OutputInterface $output, $avatarsDir)
    {
        $this->avatarsDir = $avatarsDir;
        $output->writeln('Importing avatars...');

        $users = $this->database->connection('fluxbb')
            ->table('users')
            ->select(['id'])
            ->where('username', '!=', 'Guest')
            ->where('group_id', '>', 0)
            ->orderBy('id')
            ->get()
            ->all();

        $progressBar = new ProgressBar($output, count($users));

        foreach ($users as $user) {
            $this->database
                ->table('users')
                ->where('id', '=', $user->id)
                ->update(['avatar_url' => $this->createAvatarUrl($user->id)]);
            $progressBar->advance();
        }
        $progressBar->finish();

        $output->writeln('');
    }

    /**
     * @param int $userId
     * @return string|null
     */
    private function createAvatarUrl(int $userId): ?string
    {
        $avatarFile = glob($this->avatarsDir . $userId . '.*');
        if (!$avatarFile) {
            return null;
        }
        $avatarFile = $avatarFile[0];

        $newFileName = Str::random() . '.png';

        Image::configure(['driver' => 'imagick']);
        $image = Image::make($avatarFile);
        if (extension_loaded('exif')) {
            $image->orientate();
        }
        $this->uploadDir->put($newFileName, $image->fit(100, 100)->encode('png'));

        return $newFileName;
    }
}
