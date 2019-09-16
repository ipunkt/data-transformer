<?php


namespace Ipunkt\DataTransformer\Services;

class GetJsonFile
{
    protected $fileName;

    public function setName($setFileName)
    {
        $this->fileName = $setFileName;
        return $this;
    }

    public function getName()
    {
        return $this->fileName;
    }

    public function jsonFile(): string
    {
        return base_path($this->getName());
    }
}
