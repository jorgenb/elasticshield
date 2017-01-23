<?php

use App\User;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Class ShieldTestApi
 *
 * Run this script: 'vendor/bin/phpunit tests/Feature/ElasticShieldTestApi.php'
 */
class ElasticShieldTestApi extends TestCase
{
    use WithoutMiddleware;

    /**
     * Test the Elastic Shield API routes.
     */
    public function testShieldRoutes()
    {
        $faker = Faker\Factory::create();

        // No JSON API for creating a user, so create it manually.
        $user = User::create(
            [
                'userid' => $faker->uuid,
                'name' => $faker->name,
                'email' => $faker->email,
                'password' => bcrypt('secret')
            ]
        );

        /**
         * Indices operations
         */

        // Elasticsearch index name used in test case.
        $new_elastic_index_name = strtolower($faker->domainWord);

        // Create a new index using the API. Assert that name attribute matches input ...
        $response = $this->actingAs($user)->json('POST', '/api/elasticshield/indices', ['name' => $new_elastic_index_name])
            ->assertStatus(200)
            ->assertJson([
                'name' => $new_elastic_index_name,
            ]);

        $elasticsearchTestindex = json_decode($response->getContent());

        // List indices. Assert that response has at least ...
        $indices = $this->actingAs($user)->json('GET', '/api/elasticshield/indices');
        $indices
            ->assertStatus(200)
            ->assertSee($indices->getContent(), ['id', 'name', 'created_at', 'updated_at']);


        // Delete the index. Assert response.
        $this->actingAs($user)->json('DELETE', '/api/elasticshield/indices/' . $elasticsearchTestindex->id)
            ->assertStatus(200)
            ->assertJson([
                'acknowledged' => true,
            ]);

        /**
         * OAuth 2.0 operations
         */

        // List scopes. Assert that response has at least ...
        $scopes = $this->actingAs($user)->json('GET', '/api/oauth/scopes');
        $scopes
            ->assertStatus(200)
            ->assertSee($scopes->getContent(), ['id', 'description']);

        // Create an access token using the API.
        $fake_token_name = $fake_token_scope = 'get';
        $token = $this
            ->actingAs($user)
            ->json('POST', '/api/oauth/personal-access-tokens', ['name' => $fake_token_name, 'scopes' => [$fake_token_scope]]);
        $token
            ->assertStatus(200)
            ->assertSee($token->getContent(), ['accessToken']);

        $fake_token = json_decode($token->getContent());

        // List tokens.
        $tokens = $this
            ->actingAs($user)->json('GET', '/api/oauth/personal-access-tokens');
        $tokens
            ->assertStatus(200)
            ->assertSee($tokens->getContent(), ['id', 'user_id', 'client_id', 'name', 'scopes', 'revoked', 'created_at', 'updated_at', 'expires_at']);

        // Delete the index.
        // We don't worry about leaving revoked tokens. They will eventually be pruned by the system.
        $this
            ->actingAs($user)
            ->json('DELETE', '/api/oauth/personal-access-tokens/' . $fake_token->token->id)->assertStatus(200);

        //Delete the test user
        User::destroy($user->id);
    }
}
