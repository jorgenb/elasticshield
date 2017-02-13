<?php

namespace Jorgenb\OAuthShield\Console;

use App\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Jorgenb\OAuthShield\Facades\ElasticSearchCluster;

class OAuthShieldIndex extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'oauthshield:index';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create an Elasticsearch index for a user.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $user_id = $this->ask(
            'Which user ID should the index be assigned to?'
        );

        $name = $this->ask(
            'Enter desired index name?'
        );

        $validator = Validator::make(['name' => $name], [
            'name' => 'required|unique:elastic_indices|min:2|max:255|alpha_num',
        ]);

        //TODO: Returns something sensible to the user.
        if ($validator->fails()) {
            $this->error('Validation failed.');
        }

        ElasticSearchCluster::create($name);

        User::findOrFail($user_id)->indices()->create(['name' => $name]);

        $this->info('Elasticsearch index successfully created.');
    }
}
