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

            $result[$table] = collect($getAllColumns[0])->mapWithKeys(function ($value, $key) {

                $ColumnsToBeFaked = collect($this->getColumnsFile());

                $ColumnsToBeFaked->each(function ($fakeVale, $fakeKey) use (&$value, $key) {

                    $columns[] = $key;
                    $columnsToFaked = (in_array($fakeKey, $columns) ?? []);

                    if (preg_match("/$key/i", $key, $columnsToFaked)) {
                        $this->columnsToBeTransformed($key, $value);
                    }
                });

                return [$key => $value];
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

    public function columnsToBeTransformed($key, &$value): string
    {
        switch ($key) {
            case 'name':
                return $value = "fakeName";
            case 'email':
                return $value = "fakeEmail";
            case 'place_of_birth':
                return $value = "fakePlaceOfBirth";
            case 'data_health':
                return $value = "fakeDataHealth";
            case 'id_number':
                return $value = "fakeID";
            case 'phone_number':
                return $value = "fakePhoneNumber";
            case 'credit':
                return $value = "fakeCredit";
            case 'license_plate':
                return $value = "fakeLicensePlate";
            case 'image':
                return $value = "fakeImage";
            case 'ip_address':
                return $value = "fakeIPAddress";
            case 'data_location':
                return $value = "fakeDataLocation";
            case 'address':
                return $value = "fakeAddress";
            case 'date_of_birth':
                return $value = "fakeDateOfBirth";

            default:
                return $value = $this->standardValue;
        }
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
