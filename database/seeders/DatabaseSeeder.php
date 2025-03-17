<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create admin user
        $admin = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'karma' => 100,
        ]);
        
        // Create regular users
        $users = User::factory(10)->create();
        
        // Create communities
        $communities = [];
        
        $communities[] = \App\Models\Community::create([
            'name' => 'technology',
            'description' => 'A community for discussing technology, programming, and gadgets.',
            'rules' => "1. Be respectful\n2. No spam\n3. Stay on topic",
            'created_by' => $admin->id,
        ]);
        
        $communities[] = \App\Models\Community::create([
            'name' => 'gaming',
            'description' => 'A community for gamers to discuss games, share tips, and connect.',
            'rules' => "1. No spoilers without tags\n2. Be respectful\n3. No piracy",
            'created_by' => $users[0]->id,
        ]);
        
        $communities[] = \App\Models\Community::create([
            'name' => 'movies',
            'description' => 'Discuss your favorite movies, directors, and actors.',
            'rules' => "1. Use spoiler tags\n2. Be respectful\n3. No piracy links",
            'created_by' => $users[1]->id,
        ]);
        
        // Add users to communities
        foreach ($communities as $community) {
            // Add creator as moderator
            $community->members()->attach($community->created_by, ['role' => 'moderator']);
            
            // Add some random users as members
            $randomUsers = $users->random(rand(3, 7));
            foreach ($randomUsers as $user) {
                if ($user->id !== $community->created_by) {
                    $community->members()->attach($user->id, ['role' => 'member']);
                }
            }
            
            // Add admin as moderator to all communities
            if ($community->created_by !== $admin->id) {
                $community->members()->attach($admin->id, ['role' => 'moderator']);
            }
        }
        
        // Create posts
        foreach ($communities as $community) {
            $communityMembers = $community->members;
            
            for ($i = 0; $i < rand(5, 10); $i++) {
                $randomUser = $communityMembers->random();
                
                $post = \App\Models\Post::create([
                    'title' => 'Sample Post ' . ($i + 1) . ' in ' . $community->name,
                    'content' => 'This is a sample post content. It can be quite long and detailed.',
                    'type' => 'text',
                    'community_id' => $community->id,
                    'user_id' => $randomUser->id,
                ]);
                
                // Add comments
                for ($j = 0; $j < rand(2, 5); $j++) {
                    $commentUser = $communityMembers->random();
                    
                    $comment = \App\Models\Comment::create([
                        'content' => 'This is a sample comment. It can be detailed and include opinions.',
                        'post_id' => $post->id,
                        'user_id' => $commentUser->id,
                    ]);
                    
                    // Add replies to some comments
                    if (rand(0, 1) === 1) {
                        for ($k = 0; $k < rand(1, 3); $k++) {
                            $replyUser = $communityMembers->random();
                            
                            \App\Models\Comment::create([
                                'content' => 'This is a reply to the comment above.',
                                'post_id' => $post->id,
                                'user_id' => $replyUser->id,
                                'parent_comment_id' => $comment->id,
                            ]);
                        }
                    }
                }
                
                // Add votes to posts
                foreach ($communityMembers->random(rand(3, $communityMembers->count())) as $voter) {
                    $post->votes()->create([
                        'user_id' => $voter->id,
                        'vote_type' => rand(0, 1) === 1 ? 'up' : 'down',
                    ]);
                }
            }
        }
    }
}
