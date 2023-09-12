<?php

namespace ArchLinux\ImportFluxBB\Importer;

use Illuminate\Database\Capsule\Manager;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;

class ForumSubscriptions
{
    private Manager $database;

    public function __construct(Manager $database)
    {
        $this->database = $database;
    }

    public function execute(OutputInterface $output)
    {
        $output->writeln('Importing forum_subscriptions...');

        $array_id = $this->database->table('users')->pluck('id')->toArray();
        $array_forumid = $this->database->table('tags')->pluck('id')->toArray();

        $topicSubscriptions = $this->database->connection('fluxbb')
            ->table('forum_subscriptions')
            ->select(
                [
                    'user_id',
                    'forum_id'
                ]
            )
            ->whereIn('user_id', $array_id)
            ->whereIn('forum_id', $array_forumid)
            ->orderBy('forum_id')
            ->get()
            ->all();

        $progressBar = new ProgressBar($output, count($topicSubscriptions));

        foreach ($topicSubscriptions as $topicSubscription) {
            $this->database
                ->table('tag_user')
                ->insert(
                    [
                        'user_id' => $topicSubscription->user_id,
                        'tag_id' => $topicSubscription->forum_id,
                        'marked_as_read_at' => null,
                        'is_hidden' => 0
                    ]
                );
            $progressBar->advance();
        }
        $progressBar->finish();

        $output->writeln('');
    }
}
