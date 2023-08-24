<?php

namespace ArchLinux\ImportFluxBB\Importer;

use Illuminate\Database\Capsule\Manager;
use Illuminate\Support\Str;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;

class Forums
{
    private Manager $database;

    public function __construct(Manager $database)
    {
        $this->database = $database;
    }

    public function execute(OutputInterface $output)
    {
        $output->writeln('Importing forums...');

        $forums = $this->database->connection('fluxbb')
            ->table('forums')
            ->select(
                [
                    'id',
                    'forum_name',
                    'forum_desc',
                    'redirect_url',
                    'moderators',
                    'num_topics',
                    'num_posts',
                    'last_post',
                    'last_post_id',
                    'last_poster',
                    'sort_by',
                    'disp_position',
                    'cat_id'
                ]
            )
            ->orderBy('id')
            ->get()
            ->all();

        $progressBar = new ProgressBar($output, count($forums));

        $this->database->connection()->statement('SET FOREIGN_KEY_CHECKS=0');
        foreach ($forums as $forum) {
            $this->database
                ->table('tags')
                ->insert(
                    [
                        'id' => $forum->id,
                        'name' => $forum->forum_name,
                        'slug' => Str::slug(preg_replace('/\.+/', '-', $forum->forum_name), '-', 'de'),
                        'description' => $forum->forum_desc,
                        'position' => $forum->disp_position,
                        'parent_id' => $forum->cat_id + 50, // Suggested Fix https://discuss.flarum.org/d/3867-fluxbb-to-flarum-migration-tool/11
                        'discussion_count' => $forum->num_topics,
                        'last_posted_at' => (new \DateTime())->setTimestamp($forum->last_post ?? time()),
                        'last_posted_discussion_id' => $this->getLastTopicId($forum->last_post_id ?? 0),
                        'last_posted_user_id' => $this->getLastPostUserId($forum->last_post_id ?? 1),
                        'color' => '#333'
                    ]
                );
            $progressBar->advance();
        }
        $this->database->connection()->statement('SET FOREIGN_KEY_CHECKS=1');
        $progressBar->finish();

        $output->writeln('');
    }

    private function getLastTopicId(int $lastPostId): ?int
    {
        $topic = $this->database->connection('fluxbb')
            ->table('posts')
            ->select(['topic_id'])
            ->where('id', '=', $lastPostId)
            ->get()
            ->first();

        return $topic->topic_id ?? null;
    }

    private function getLastPostUserId(int $lastPostId): ?int
    {
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
