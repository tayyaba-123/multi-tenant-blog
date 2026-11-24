<?php

namespace Database\Seeders;

use App\Actions\CreateTenant;
use App\Enums\PostStatus;
use App\Jobs\DeleteTenantStorage;
use App\Models\Post;
use App\Models\Tenant;
use App\Models\User;
use Database\Seeders\Support\DemoImage;
use Illuminate\Database\Seeder;

class DemoTenantsSeeder extends Seeder
{
    private const TENANTS = [
        [
            'subdomain' => 'acme',
            'name' => 'Acme Corp',
            'brand_color' => '#e11d48',
            'owner' => ['Wile E. Coyote', 'wile@acme.test'],
            'writer' => ['Road Runner', 'roadrunner@acme.test'],
            'titles' => [
                'Why our rockets always backfire',
                'A field guide to giant magnets',
                'Ten uses for an anvil',
                'Painting tunnels that actually work',
                'Lessons from 1,000 failed experiments',
                'Inside the Acme catalog',
            ],
        ],
        [
            'subdomain' => 'globex',
            'name' => 'Globex',
            'brand_color' => '#0d9488',
            'owner' => ['Hank Scorpio', 'hank@globex.test'],
            'writer' => ['Homer Simpson', 'homer@globex.test'],
            'titles' => [
                'Hammocks for every employee',
                'Our new campus in Cypress Creek',
                'How we run a flat organization',
                'Building teams that ship',
                'What makes a great manager',
                'The Globex onboarding playbook',
            ],
        ],
    ];

    public function run(CreateTenant $createTenant): void
    {
        foreach (self::TENANTS as $config) {
            $this->removeLeftovers($config['subdomain']);

            $tenant = $createTenant->handle([
                'name' => $config['name'],
                'subdomain' => $config['subdomain'],
                'brand_color' => $config['brand_color'],
                'owner_name' => $config['owner'][0],
                'owner_email' => $config['owner'][1],
                'owner_password' => 'password',
            ], DemoImage::logo($config['brand_color']));

            $tenant->run(fn () => $this->seedPosts($config));

            $this->command?->info("Seeded {$tenant->name} at http://{$tenant->primaryDomain()}");
        }
    }

    private function seedPosts(array $config): void
    {
        $owner = User::where('email', $config['owner'][1])->firstOrFail();
        $writer = User::factory()->create([
            'name' => $config['writer'][0],
            'email' => $config['writer'][1],
        ]);

        foreach ($config['titles'] as $index => $title) {
            $post = Post::factory()->published()->make([
                'title' => $title,
                'user_id' => $index % 2 === 0 ? $owner->id : $writer->id,
                'published_at' => now()->subDays(($index + 1) * 4),
            ]);

            if ($index % 3 !== 2) {
                $post->replaceCoverImage(DemoImage::cover($config['brand_color'], crc32($title)));
            }

            $post->save();
        }

        // Goes live a few minutes after seeding when the scheduler is running.
        Post::factory()->for($owner, 'author')->create([
            'title' => 'Coming up in a few minutes',
            'status' => PostStatus::Scheduled,
            'published_at' => now()->addMinutes(3),
        ]);

        Post::factory()->for($writer, 'author')->scheduled()->create([
            'title' => 'Next week: our big announcement',
            'published_at' => now()->addWeek(),
        ]);

        Post::factory()->for($writer, 'author')->create([
            'title' => 'Draft: notes for the next post',
        ]);
    }

    /**
     * migrate:fresh drops the central tables but not tenant databases or
     * storage, so clear those before re-creating a demo tenant.
     */
    private function removeLeftovers(string $id): void
    {
        if ($existing = Tenant::find($id)) {
            $existing->delete();

            return;
        }

        $probe = new Tenant(['id' => $id]);
        $manager = $probe->database()->manager();

        if ($manager->databaseExists($probe->database()->getName())) {
            $manager->deleteDatabase($probe);
        }

        (new DeleteTenantStorage($probe))->handle();
    }
}
