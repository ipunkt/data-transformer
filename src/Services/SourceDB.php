<?php

namespace Ipunkt\DataTransformer\Services;

use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Faker\Generator as Faker;
use Exception;

class SourceDB
{
    protected $faker;
    protected $json;
    protected $dsnSource;
    protected $standardValue = "value";

    public function __construct(TransformerJsonFile $json, Faker $faker)
    {
        $this->faker = $faker;
        $this->json = $json;
    }

    /**
     * @return ConnectionInterface
     */
    public function dsnProduction()
    {
        return DB::connection($this->getSource());
    }

    public function getAllTables(): array
    {
        return array_map('reset', $this->dsnProduction()->select('SHOW TABLES'));
    }

    public function getColumnsFile(): array
    {
        return Config::get('data-transformer');
    }

    public function getAllRows(): array
    {
        $tables = $this->getAllTables();

        $result = [];

        foreach ($tables as $table) {
            $getAllColumns = $this->firstRowForEachTable($table);

            if ($getAllColumns->isEmpty()) continue;

            $result[$table] = collect($getAllColumns[0])->mapWithKeys(function ($value, $columnNameInDB) {

                $ColumnsToBeFaked = collect($this->getColumnsFile());

                $transformation = null;

                $ColumnsToBeFaked->each(function ($transformationValueInConfig, $columnMatchInConfig) use (&$transformation, $columnNameInDB) {

                    $columns[] = $columnNameInDB;

                    $columnsToFaked = [];

                    if (preg_match("/$columnMatchInConfig/i", $columnNameInDB, $columnsToFaked, PREG_OFFSET_CAPTURE)) {
                        $transformation = $transformationValueInConfig;
                        return false;
                    }

                });

                if ($transformation === null)
                    $transformation = $this->standardValue;

                return [$columnNameInDB => $transformation];
            });
        }

        return $result;
    }

    public function firstRowForEachTable(string $table)
    {
        return $this->dsnProduction()->table($table)->limit(1)->get();
    }

    /**
     * @throws Exception
     */
    public function storeInTransformerJson()
    {
        return $this->json->createTransformerFile($this->getAllRows());
    }

    public function setSource($data)
    {
        $this->dsnSource = $data;
        return $this;
    }

    public function getSource()
    {
        return $this->dsnSource;
    }
}
