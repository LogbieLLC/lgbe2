<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Community;
use App\Models\Post;
use App\Models\Comment;
use App\Models\Vote;
use Illuminate\Database\Seeder;

class DuskTestSeeder extends Seeder
{
    /**
     * Seed the database with test data for Dusk tests.
     */
    public function run(): void
    {
        // Create test users
        $admin = User::factory()->create([
            'name' => 'Admin User',
            'username' => 'admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);

        $moderator = User::factory()->create([
            'name' => 'Moderator User',
            'username' => 'moderator',
            'email' => 'moderator@example.com',
            'password' => bcrypt('password'),
        ]);

        $user = User::factory()->create([
            'name' => 'Regular User',
            'username' => 'user',
            'email' => 'user@example.com',
            'password' => bcrypt('password'),
        ]);

        // Create test communities
        $community1 = Community::factory()->create([
            'name' => 'TestCommunity1',
            'description' => 'This is the first test community',
            'created_by' => $admin->id,
        ]);

        $community2 = Community::factory()->create([
            'name' => 'TestCommunity2',
            'description' => 'This is the second test community',
            'created_by' => $moderator->id,
        ]);

        // Set up community memberships
        $community1->members()->attach($admin->id, ['role' => 'moderator']);
        $community1->members()->attach($moderator->id, ['role' => 'moderator']);
        $community1->members()->attach($user->id, ['role' => 'member']);

        $community2->members()->attach($moderator->id, ['role' => 'moderator']);
        $community2->members()->attach($user->id, ['role' => 'member']);

        // Create test posts
        $post1 = Post::factory()->create([
            'title' => 'First Test Post',
            'content' => 'This is the content of the first test post.',
            'type' => 'text',
            'community_id' => $community1->id,
            'user_id' => $admin->id,
        ]);

        $post2 = Post::factory()->create([
            'title' => 'Second Test Post',
            'content' => 'This is the content of the second test post.',
            'type' => 'text',
            'community_id' => $community1->id,
            'user_id' => $moderator->id,
        ]);

        $post3 = Post::factory()->create([
            'title' => 'Third Test Post',
            'content' => 'This is the content of the third test post.',
            'type' => 'text',
            'community_id' => $community2->id,
            'user_id' => $user->id,
        ]);

        // Create test comments
        $comment1 = Comment::factory()->create([
            'content' => 'This is a comment on the first post.',
            'post_id' => $post1->id,
            'user_id' => $user->id,
        ]);

        $comment2 = Comment::factory()->create([
            'content' => 'This is another comment on the first post.',
            'post_id' => $post1->id,
            'user_id' => $moderator->id,
        ]);

        $reply1 = Comment::factory()->create([
            'content' => 'This is a reply to the first comment.',
            'post_id' => $post1->id,
            'user_id' => $admin->id,
            'parent_comment_id' => $comment1->id,
        ]);

        $comment3 = Comment::factory()->create([
            'content' => 'This is a comment on the second post.',
            'post_id' => $post2->id,
            'user_id' => $user->id,
        ]);

        // Create test votes
        // Upvotes for post1
        Vote::factory()->create([
            'user_id' => $user->id,
            'votable_id' => $post1->id,
            'votable_type' => Post::class,
            'vote_type' => 'up',
        ]);

        Vote::factory()->create([
            'user_id' => $moderator->id,
            'votable_id' => $post1->id,
            'votable_type' => Post::class,
            'vote_type' => 'up',
        ]);

        // Downvote for post2
        Vote::factory()->create([
            'user_id' => $user->id,
            'votable_id' => $post2->id,
            'votable_type' => Post::class,
            'vote_type' => 'down',
        ]);

        // Upvote for comment1
        Vote::factory()->create([
            'user_id' => $admin->id,
            'votable_id' => $comment1->id,
            'votable_type' => Comment::class,
            'vote_type' => 'up',
        ]);
    }
}
