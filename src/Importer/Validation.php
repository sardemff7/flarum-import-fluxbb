<?php

namespace ArchLinux\ImportFluxBB\Importer;

use Illuminate\Database\Capsule\Manager;
use Symfony\Component\Console\Output\OutputInterface;

class Validation
{
    private Manager $database;

    public function __construct(Manager $database)
    {
        $this->database = $database;
    }

    public function execute(OutputInterface $output)
    {
        $output->writeln('Validate data integrity...');

        $this->validateDiscussions($output);
        $this->validateDiscussionTag($output);
        $this->validateDiscussionUser($output);
        $this->validateGroupPermission($output);
        $this->validateGroupUser($output);
        $this->validatePosts($output);
        $this->validatePostMentionsUser($output);
        $this->validateTags($output);
        $this->validateTagUser($output);
    }

    private function assertZero(int $value): void
    {
        if ($value !== 0) {
            throw new \RuntimeException(sprintf('%s is not 0', $value));
        }
    }

    private function validateDiscussions(OutputInterface $output): void
    {
        $output->writeln("\tdiscussions");
        $this->assertZero(
            $this->database
                ->table('discussions')
                ->select('id')
                ->whereNotIn('user_id', $this->database->table('users')->select('id'))
                ->get()
                ->count()
        );
        $this->assertZero(
            $this->database
                ->table('discussions')
                ->select('id')
                ->whereNotIn('last_posted_user_id', $this->database->table('users')->select('id'))
                ->get()
                ->count()
        );
        $this->assertZero(
            $this->database
                ->table('discussions')
                ->select('id')
                ->whereNotIn('first_post_id', $this->database->table('posts')->select('id'))
                ->get()
                ->count()
        );
        $this->assertZero(
            $this->database
                ->table('discussions')
                ->select('id')
                ->whereNotIn('last_post_id', $this->database->table('posts')->select('id'))
                ->get()
                ->count()
        );
    }

    private function validateDiscussionTag(OutputInterface $output): void
    {
        $output->writeln("\tdiscussion_tag");
        $this->assertZero(
            $this->database
                ->table('discussion_tag')
                ->select('discussion_id')
                ->whereNotIn('discussion_id', $this->database->table('discussions')->select('id'))
                ->get()
                ->count()
        );
        $this->assertZero(
            $this->database
                ->table('discussion_tag')
                ->select('tag_id')
                ->whereNotIn('tag_id', $this->database->table('tags')->select('id'))
                ->get()
                ->count()
        );
    }

    private function validateDiscussionUser(OutputInterface $output): void
    {
        $output->writeln("\tdiscussion_user");
        $this->assertZero(
            $this->database
                ->table('discussion_user')
                ->select('discussion_id')
                ->whereNotIn('discussion_id', $this->database->table('discussions')->select('id'))
                ->get()
                ->count()
        );
        $this->assertZero(
            $this->database
                ->table('discussion_user')
                ->select('user_id')
                ->whereNotIn('user_id', $this->database->table('users')->select('id'))
                ->get()
                ->count()
        );
    }

    private function validateGroupPermission(OutputInterface $output): void
    {
        $output->writeln("\tgroup_permission");
        $this->assertZero(
            $this->database
                ->table('group_permission')
                ->select('group_id')
                ->whereNotIn('group_id', $this->database->table('groups')->select('id'))
                ->get()
                ->count()
        );
    }

    private function validateGroupUser(OutputInterface $output): void
    {
        $output->writeln("\tgroup_user");
        $this->assertZero(
            $this->database
                ->table('group_user')
                ->select('group_id')
                ->whereNotIn('group_id', $this->database->table('groups')->select('id'))
                ->get()
                ->count()
        );
        $this->assertZero(
            $this->database
                ->table('group_user')
                ->select('user_id')
                ->whereNotIn('user_id', $this->database->table('users')->select('id'))
                ->get()
                ->count()
        );
    }

    private function validatePosts(OutputInterface $output): void
    {
        $output->writeln("\tposts");
        $this->assertZero(
            $this->database
                ->table('posts')
                ->select('discussion_id')
                ->whereNotIn('discussion_id', $this->database->table('discussions')->select('id'))
                ->get()
                ->count()
        );
        $this->assertZero(
            $this->database
                ->table('posts')
                ->select('user_id')
                ->whereNotIn('user_id', $this->database->table('users')->select('id'))
                ->get()
                ->count()
        );
        $this->assertZero(
            $this->database
                ->table('posts')
                ->select('edited_user_id')
                ->whereNotIn('edited_user_id', $this->database->table('users')->select('id'))
                ->get()
                ->count()
        );
    }

    private function validatePostMentionsUser(OutputInterface $output): void
    {
        $output->writeln("\tpost_mentions_user");
        $this->assertZero(
            $this->database
                ->table('post_mentions_user')
                ->select('post_id')
                ->whereNotIn('post_id', $this->database->table('posts')->select('id'))
                ->get()
                ->count()
        );
        $this->assertZero(
            $this->database
                ->table('post_mentions_user')
                ->select('mentions_user_id')
                ->whereNotIn('mentions_user_id', $this->database->table('users')->select('id'))
                ->get()
                ->count()
        );
    }

    private function validateTags(OutputInterface $output): void
    {
        $output->writeln("\ttags");
        $this->assertZero(
            $this->database
                ->table('tags')
                ->select('parent_id')
                ->whereNotIn('parent_id', $this->database->table('tags')->select('id'))
                ->get()
                ->count()
        );
        $this->assertZero(
            $this->database
                ->table('tags')
                ->select('last_posted_discussion_id')
                ->whereNotIn('last_posted_discussion_id', $this->database->table('discussions')->select('id'))
                ->get()
                ->count()
        );
        $this->assertZero(
            $this->database
                ->table('tags')
                ->select('last_posted_user_id')
                ->whereNotIn('last_posted_user_id', $this->database->table('users')->select('id'))
                ->get()
                ->count()
        );
    }

    private function validateTagUser(OutputInterface $output): void
    {
        $output->writeln("\ttag_user");
        $this->assertZero(
            $this->database
                ->table('tag_user')
                ->select('user_id')
                ->whereNotIn('user_id', $this->database->table('users')->select('id'))
                ->get()
                ->count()
        );
        $this->assertZero(
            $this->database
                ->table('tag_user')
                ->select('tag_id')
                ->whereNotIn('tag_id', $this->database->table('tags')->select('id'))
                ->get()
                ->count()
        );
    }
}
