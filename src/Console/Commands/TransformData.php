<?php

namespace Ipunkt\DataTransformer\Console\Commands;


use Faker\Generator as Faker;
use Illuminate\Console\Command;
use Exception;
use Ipunkt\DataTransformer\Services\GetJsonFile;
use Ipunkt\DataTransformer\Services\TargetDB;
use Ipunkt\DataTransformer\Services\TransformerJsonFile;

class TransformData extends Command
{
    protected $targetDB;
    public $file;

    protected $signature = 'transform:data {dbSource} {dbTarget} {--config=transformer.json}';

    protected $description = 'Data will be transformed.';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        try {
            $jsonFileName = $this->option('config');

            if (empty($jsonFileName)) {
                throw new Exception("JSON File Name is required.");
            }

            $this->file = new TransformerJsonFile();
            $json = new GetJsonFile();
            $json->setName($jsonFileName);
            $faker = app(Faker::class);
            $this->file->setJsonFile($json);
            $this->targetDB = new TargetDB($this->file, $faker);

            $dsnSource = $this->argument('dbSource');
            $dsnTarget = $this->argument('dbTarget');

            $this->targetDB
                ->setSource($dsnSource)
                ->setTarget($dsnTarget)
                ->transform();

        } catch (Exception $e) {
            $this->error($e->getMessage());
            $this->warn($e->getTraceAsString());
        }
    }
}
