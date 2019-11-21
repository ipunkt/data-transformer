<?php

namespace Ipunkt\DataTransformer\Console\Commands;


use Exception;
use Faker\Generator as Faker;
use Ipunkt\DataTransformer\Services\GetJsonFile;
use Ipunkt\DataTransformer\Services\TargetDB;
use Ipunkt\DataTransformer\Services\TransformerJsonFile;

class TransformData extends RemoteDBCommand
{
    protected $targetDB;
    public $file;
    protected $signature = 'transform:data {host} {port} {db} {username} {password} {--unix_socket} 
   										   {--charset=utf8mb4} {--collation=utf8mb4_unicode_ci}  {--strict=false} 
   										   {--engine} {--dbTarget=mysql} {--driver=mysql} {--config=transformer.json} {--foreign-keys-checks=no}';
    protected $description = 'Data will be transformed.';

    public function handle()
    {
        try {
            $this->connect();
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

            $dsnSource = 'remote';
            $dsnTarget = $this->option('dbTarget');

            $foreignKeysChecks = $this->option('foreign-keys-checks');
            if ($foreignKeysChecks === 'no') {
                $this->targetDB->disableForeignKeys();
            }

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
