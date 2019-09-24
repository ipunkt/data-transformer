<?php


namespace Ipunkt\DataTransformer\Services;

use Exception;

class TransformerJsonFile
{
    protected $jsonFile;

    public function setJsonFile(GetJsonFile $jsonFile)
    {
        $this->jsonFile = $jsonFile;
    }

    public function jsonFileName(): string
    {
        return $this->jsonFile->jsonFile();
    }

    /**
     * @param $contents
     * @return string
     * @throws Exception
     */
    public function createTransformerFile($contents)
    {
        $file = $this->jsonFileName();

        if (!file_exists($file)) {
            file_put_contents($file, json_encode($contents, JSON_PRETTY_PRINT));
            echo " The (" . basename($this->jsonFileName()) . ") file  created successfully. \n";
        } else {
            throw new Exception(" There's already a file with the same Name (" . basename($this->jsonFileName()) . ")");
        }
    }
}
