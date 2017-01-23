<?php

namespace Jorgenb\ElasticShield\Console;

use App\User;
use Illuminate\Console\Command;
use Laravel\Passport\Passport;

class ElasticShieldUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'elasticshield:user
            {--admin : Create a user with unrestricted access to the cluster.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a Personal Access Token for a user';

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
        if ($this->option('admin')) {
            return $this->createAdminUser();
        }

        return $this->createTokenForUser();
    }

    public function createTokenForUser () {
        $user_id = $this->ask(
            'Which user ID should the token be assigned to?'
        );

        $name = $this->ask(
            'Enter a name for the token'
        );

        $scope = $this->choice('Select the scopes for this token', ['get', 'post', 'put', 'delete']);

        $user = User::find($user_id);
        $token = $user->createToken($name, [$scope])->accessToken;
        $this->info($token);
    }

    public function createAdminUser () {

        $name = $this->ask(
            'Enter the full name of the admin user'
        );
        $email = $this->ask(
            'Enter the email address of the admin user'
        );
        $password = $this->secret(
            'Enter the password of the admin user'
        );

        Passport::tokensCan([
            'admin' => 'Full access to all Elasticsearch routes.',
        ]);

        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => bcrypt($password)
        ]);

        $token = $user->createToken('ELASTIC_API_ADMIN_TOKEN', ['admin'])->accessToken;

        $this->info('THIS IS THE ONLY TIME THAT THIS TOKEN WILL BE DISPLAYED SO PLEASE SAVE A COPY IN A SECURE PLACE');
        $this->info($token);

    }
}
