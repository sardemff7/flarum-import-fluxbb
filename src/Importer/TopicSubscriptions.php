<?php

namespace ArchLinux\ImportFluxBB\Importer;

use Illuminate\Database\Capsule\Manager;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;

class TopicSubscriptions
{
    private Manager $database;

    public function __construct(Manager $database)
    {
        $this->database = $database;
    }

    public function execute(OutputInterface $output)
    {
        $output->writeln('Importing topic_subscriptions...');

        $topicSubscriptions = $this->database->connection('fluxbb')
            ->table('topic_subscriptions')
            ->select(
                [
                    'user_id',
                    'topic_id'
                ]
            )
            ->orderBy('topic_id')
            ->get()
            ->all();

        $progressBar = new ProgressBar($output, count($topicSubscriptions));

        foreach ($topicSubscriptions as $topicSubscription) {
            $this->database
                ->table('discussion_user')
                ->insert(
                    [
                        'user_id' => $topicSubscription->user_id,
                        'discussion_id' => $topicSubscription->topic_id,
                        'last_read_at' => null,
                        'last_read_post_number' => null,
                        'subscription' => 'follow'
                    ]
                );
            $progressBar->advance();
        }
        $progressBar->finish();

        $output->writeln('');
    }
}
