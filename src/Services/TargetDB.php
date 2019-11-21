<?php


namespace Ipunkt\DataTransformer\Services;

use Faker\Generator as Faker;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use stdClass;

class TargetDB
{
    /** @var Collection */
    protected $config;
    protected $faker;
    protected $json;
    protected $dsnSource;
    protected $dsnTarget;
    protected $success = false;
    /**
     * @var Collection
     */
    protected $copiedTables;

    /**
     * TargetDB constructor.
     * @param TransformerJsonFile $json
     * @param Faker $faker
     */
    public function __construct(TransformerJsonFile $json, Faker $faker)
    {
        $this->faker = $faker;
        $this->json = $json;
        $this->copiedTables = collect();
    }

    /**
     * @return ConnectionInterface
     */
    public function dsnSource()
    {
        return DB::connection($this->getSource());
    }

    public function loadConfiguration(): void
    {
        $file = $this->json->jsonFileName();
        $handle = fopen($file, 'r');
        $transformerJsonData = fread($handle, filesize($file));
        $transformerDecode = json_decode($transformerJsonData, true);

        $this->config = collect($transformerDecode);
    }

    public function disableForeignKeys()
    {
        DB::connection($this->getTarget())->statement("SET FOREIGN_KEY_CHECKS=0");
    }

    public function tables(): Collection
    {
        return $this->config->keys();
    }

    protected function dropTables(string $table): bool
    {
        print_r("Table $table dropped\n" . PHP_EOL);
        $dbName = $this->getDatabaseName($this->dsnSource);
        $result = DB::connection($this->dsnSource)->select("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE REFERENCED_TABLE_NAME = '{$table}' AND  REFERENCED_TABLE_SCHEMA = '{$dbName}';");
        $dependantTable = collect($result)->pluck("TABLE_NAME")->unique();
//		echo "$table depends on ".$dependantTable->implode(', ')."\n";
        $dependantTable->each(function ($dependantTableName) {
            $this->dropTables($dependantTableName);
        });
        return DB::connection($this->getTarget())->statement("DROP TABLE IF EXISTS `$table`");
    }

    public function saveDataInStaging(string $table, Collection $rowValues): bool
    {
        return DB::connection($this->getTarget())->table($table)->insert($rowValues->toArray());
    }

    public function createTableStatement(string $table): string
    {
        $createTableStatementResult = $this->dsnSource()->select("SHOW CREATE TABLE `$table`");
        return $createTableStatementResult[0]->{'Create Table'};
    }

    public function fakeOrValue(string $table, string $column, $valueFromSourceTableColumn)
    {
        $decision = Arr::get($this->config, $table . '.' . $column, 'value');

        switch ($decision) {
            case 'fakeName':
                return $this->faker->name;
            case 'fakeEmail':
                return $this->faker->safeEmail;
            case 'fakePlaceOfBirth':
                return $this->faker->country;
            case 'fakeDataHealth':
                return $this->faker->randomDigit;
            case 'fakeID':
                return $this->faker->uuid;
            case 'fakePhoneNumber':
                return $this->faker->phoneNumber;
            case 'fakeCredit':
                return $this->faker->bankAccountNumber;
            case 'fakeLicensePlate':
                return $this->faker->randomLetter;
            case 'fakeImage':
                return $this->faker->image();
            case 'fakeIPAddress':
                return $this->faker->localIpv4;
            case 'fakeDataLocation':
                return $this->faker->latitude;
            case 'fakeAddress':
                return $this->faker->address;
            case 'fakeDateOfBirth':
                return $this->faker->dateTime()->format('Y-m-d');
        }

        return $valueFromSourceTableColumn;
    }

    public function transform(): array
    {
        $this->loadConfiguration();

        foreach ($this->tables() as $table) {
            $this->dropTables($table);
        }

        $result = [];
        foreach ($this->tables() as $table) {
            $this->copyTable($table);
        }

        if ($this->success === true) {
            print_r(" Data has been successfully transformed.\n");
        }

        return $result;
    }

    protected function copyTable($table)
    {
        if ($this->alreadyCopied($table))
            return;

        $dependencies = $this->readTableDependencies($table);
        $dependencies->each(function ($table) {
            $this->copyTable($table);
        });

        if ($this->alreadyCopied($table))
            return;

        print_r("Transforming table $table .." . PHP_EOL);

        $createTableStatement = $this->createTableStatement($table);
        DB::connection($this->getTarget())->statement($createTableStatement);

        $this->dsnSource()->table($table)->orderBy($this->idOrFirstColumn($table))->chunk(1000, function (Collection $rows) use ($table) {
            collect($rows)->each(function (stdClass $row) use ($table) {
                $rowValues = collect($row)->mapWithKeys(function ($value, $key) use ($table) {
                    $value = $this->fakeOrValue($table, $key, $value);
                    return [$key => $value];
                });

                $this->saveDataInStaging($table, $rowValues);
                $this->success = true;
            });
        });

        $this->markCopied($table);
    }

    protected function idOrFirstColumn($table)
    {
        $dbName = $this->getDatabaseName($this->dsnSource);
        $columnNameRows = DB::connection($this->dsnSource)->select("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = '{$dbName}' AND TABLE_NAME = '$table';");
        $columnNames = collect($columnNameRows)->pluck('COLUMN_NAME');
        if ($columnNames->has('id'))
            return $columnNames->get('id');

        return $columnNames->first();
    }


    protected function alreadyCopied($tableName)
    {
        return $this->copiedTables->has($tableName);
    }

    protected function markCopied($tableName)
    {
        $this->copiedTables->put($tableName, $tableName);
    }

    protected function readTableDependencies($tableName)
    {
        $dbName = $this->getDatabaseName($this->dsnSource);
        $result = DB::connection($this->dsnSource)->select("SELECT REFERENCED_TABLE_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE REFERENCED_TABLE_SCHEMA = '{$dbName}' AND TABLE_NAME = '{$tableName}';");
        if (empty($result))
            return collect();

        $dependantTables = collect($result)->pluck("REFERENCED_TABLE_NAME")->unique();
        return $dependantTables;
    }

    public function setSource(string $data)
    {
        $this->dsnSource = $data;
        return $this;
    }

    public function getSource()
    {
        return $this->dsnSource;
    }

    public function setTarget($data)
    {
        $this->dsnTarget = $data;
        return $this;
    }

    public function getTarget()
    {
        return $this->dsnTarget;
    }

    protected function getDatabaseName($dsn)
    {
        return config("database.connections.{$dsn}.database");
    }
}
