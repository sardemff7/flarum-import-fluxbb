<?php

namespace ArchLinux\ImportFluxBB\Importer;

use Illuminate\Database\Capsule\Manager;
use Illuminate\Support\Str;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;

class Categories
{
    private Manager $database;

    public function __construct(Manager $database)
    {
        $this->database = $database;
    }

    public function execute(OutputInterface $output)
    {
        $output->writeln('Importing categories...');

        $categories = $this->database->connection('fluxbb')
            ->table('categories')
            ->select(
                [
                    'id',
                    'cat_name',
                    'disp_position'
                ]
            )
            ->orderBy('id')
            ->get()
            ->all();

        $progressBar = new ProgressBar($output, count($categories));

        $this->database->connection()->statement('SET FOREIGN_KEY_CHECKS=0');
        foreach ($categories as $category) {
            $this->database
                ->table('tags')
                ->insert(
                    [
                        'id' => $category->id + 50, // Suggested fix from Ilaumgui https://discuss.flarum.org/d/3867-fluxbb-to-flarum-migration-tool/11
                        'name' => $category->cat_name,
                        'slug' => Str::slug(preg_replace('/\.+/', '-', $category->cat_name), '-', 'de'),
                        'position' => $category->disp_position,
                        'color' => '#08c',
                        'discussion_count' => $this->getNumberOfTopics($category->id),
                        'last_posted_at' => (new \DateTime())->setTimestamp($this->getLastPostedAt($category->id)),
                        'last_posted_discussion_id' => $this->getLastTopicId($category->id),
                        'last_posted_user_id' => $this->getLastPostUserId($category->id),
                    ]
                );
            $progressBar->advance();
        }
        $this->database->connection()->statement('SET FOREIGN_KEY_CHECKS=1');
        $progressBar->finish();

        $output->writeln('');
    }

    private function getNumberOfTopics(int $categoryId): int
    {
        return $this->database->connection('fluxbb')
            ->table('forums')
            ->selectRaw('SUM(num_topics) AS total_topics')
            ->where('cat_id', '=', $categoryId)
            ->get()
            ->first()
            ->total_topics;
    }

    private function getLastPostId(int $categoryId): int
    {
        return $this->database->connection('fluxbb')
            ->table('forums')
            ->select(['last_post_id'])
            ->where('cat_id', '=', $categoryId)
            ->orderBy('last_post', 'DESC')
            ->get()
            ->first()
            ->last_post_id;
    }

    private function getLastPostedAt(int $categoryId): int
    {
        return $this->database->connection('fluxbb')
            ->table('forums')
            ->select(['last_post'])
            ->where('cat_id', '=', $categoryId)
            ->orderBy('last_post', 'DESC')
            ->get()
            ->first()
            ->last_post;
    }

    private function getLastTopicId(int $categoryId): int
    {
        $lastPostId = $this->getLastPostId($categoryId);

        return $this->database->connection('fluxbb')
            ->table('posts')
            ->select(['topic_id'])
            ->where('id', '=', $lastPostId)
            ->get()
            ->first()
            ->topic_id;
    }

    private function getLastPostUserId(int $categoryId): ?int
    {
        $lastPostId = $this->getLastPostId($categoryId);

        $topic = $this->database->connection('fluxbb')
            ->table('posts')
            ->select(['poster_id'])
            ->where('id', '=', $lastPostId)
            ->where('poster_id', '!=', 1)
            ->get()
            ->first();

        return $topic->poster_id ?? null;
    }
}
