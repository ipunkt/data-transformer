<?php

namespace Ipunkt\DataTransformer\Console\Commands;

use Faker\Generator as Faker;
use Exception;
use Illuminate\Console\Command;
use Ipunkt\DataTransformer\Services\GetJsonFile;
use Ipunkt\DataTransformer\Services\SourceDB;
use Ipunkt\DataTransformer\Services\TransformerJsonFile;

class TransformDump extends Command
{
    protected $sourceDB;
    public $file;

    protected $signature = 'transform:dump {dbSource} {--config=transformer.json}';

    protected $description = 'Dump all Tables.';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle(): void
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
            $this->sourceDB = new SourceDB($this->file, $faker);

            $this->sourceDB
                ->setSource($this->argument('dbSource'))
                ->storeInTransformerJson();
        } catch (Exception $e) {
            $this->warn($e->getMessage());
            $this->warn($e->getTraceAsString());
        }
    }
}
