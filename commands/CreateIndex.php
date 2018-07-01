<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Aws\ElasticsearchService\ElasticsearchPhpHandler;
use Elasticsearch\ClientBuilder;

class CreateIndex extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scout:create-index';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create index required for Scout';

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
        $provider = env('ELASTICSEARCH_PROVIDER', 'elastic');

        switch ($provider) {
            case 'aws':
                // Create a handler (with the region of your Amazon Elasticsearch Service domain)
                $handler = new ElasticsearchPhpHandler(env('AWS_REGION'));

                // Use this handler to create an Elasticsearch-PHP client
                $client = ClientBuilder::create()
                            ->setHandler($handler)
                            ->setHosts(config('scout.elasticsearch.hosts'))
                            ->build();
            case 'elastic':
            default:
                $client = ClientBuilder::create()
                    ->setHosts(config('scout.elasticsearch.hosts'))
                    ->build();
        }
        
        if(!$client->indices()->exists(['index' => config('scout.elasticsearch.index')])) {
            $params = [
                'index' => config('scout.elasticsearch.index'),
            ];
            $client->indices()->create($params);
        }
    }
}