<?php

namespace Ipunkt\DataTransformer\Console\Commands;

use Exception;
use Faker\Generator as Faker;
use Ipunkt\DataTransformer\Services\GetJsonFile;
use Ipunkt\DataTransformer\Services\SourceDB;
use Ipunkt\DataTransformer\Services\TransformerJsonFile;

class TransformDump extends RemoteDBCommand
{
    protected $sourceDB;
    public $file;
    protected $signature = 'transform:dump {host} {port} {db} {username} {password} {--unix_socket} 
 										   {--charset=utf8mb4} {--collation=utf8mb4_unicode_ci}  {--strict=false} 
 										   {--engine} {--driver=mysql} {--config=transformer.json}';

    protected $description = 'Dump all Tables.';

    public function handle(): void
    {
        try {
            $this->connect();
            $jsonFileName = $this->option('config');
            if (empty($jsonFileName)) {
                $this->error("JSON File Name is required.");
                return;
            }

            $this->file = new TransformerJsonFile();
            $json = new GetJsonFile();
            $json->setName($jsonFileName);
            $faker = app(Faker::class);
            $this->file->setJsonFile($json);
            $this->sourceDB = new SourceDB($this->file, $faker);

            $this->sourceDB
                ->setSource('remote')
                ->storeInTransformerJson();
        } catch (Exception $e) {
            $this->warn($e->getMessage());
            $this->warn($e->getTraceAsString());
        }
    }

}
