<?php

namespace App\Console\Commands;

use App\Console\Behaviors\PrependTimestamp;
use App\Game\MissionManager;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Database\DatabaseManager;

class MissionGenerate extends Command
{
    use PrependTimestamp;

    /**
     * {@inheritdoc}
     */
    protected $signature = 'mission:generate';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Generate the missions';

    /**
     * The database manager instance.
     *
     * @var DatabaseManager
     */
    protected $database;

    /**
     * The mission manager instance.
     *
     * @var MissionManager
     */
    protected $manager;

    /**
     * Constructor.
     */
    public function __construct(DatabaseManager $database, MissionManager $manager)
    {
        parent::__construct();

        $this->database = $database;
        $this->manager = $manager;
    }

    /**
     * Execute the console command.
     *
     * @throws \Exception|\Throwable
     */
    public function handle()
    {
        $this->call('mission:clear');

        $this->info(
            $this->prependTimestamp('Generating missions...')
        );

        $users = User::whereNotNull('started_at')->get();

        foreach ($users as $user) {
            $this->database->transaction(function () use ($user) {
                $this->manager->createRand($user);
            });
        }

        $this->info(
            $this->prependTimestamp('Generation complete!')
        );
    }
}
