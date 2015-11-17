<?php

namespace Qaamgo\Helper;

use Qaamgo\Configuration;
use Qaamgo\InformationApi;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Filesystem\Filesystem;
use Qaamgo\Models\Conversion;
use Symfony\Component\Finder\SplFileInfo;

/**
 * Class JsonPersister
 * @package Qaamgo\Helper
 * @author Andrés Cevallos <a.cevallos@qaamgo.com>
 */
class JsonPersister
{
    const SCHEMA_PATH = __DIR__ . '/../Resources/schema/';

    const SCHEMA_PATH_PATTERN = __DIR__ . '/../Resources/schema/%s.%s.json';

    const TIME_TO_UPDATE = 30;

    /**
     * @var Filesystem
     */
    private $filesystem;

    private $apiInfo;

    public function __construct()
    {
        $this->filesystem = new Filesystem();
        $this->apiInfo = new InformationApi();
    }

    /**
     * Persist the options schema required
     *
     * @param $category
     * @param $target
     * @return bool|string False when fail
     */
    public function getOptionsSchema($category, $target)
    {
        //todo log
        return $this->checkSchema($category, $target);
    }

    /**
     * Check if the schema is out date.
     *
     * @param $category
     * @param $target
     * @return bool
     */
    private function checkSchema($category, $target)
    {
        $name = $category . '.' . $target;
        /** @var Conversion $schemaInfo */
        $schemaInfo = json_encode($this->apiInfo->conversionsGet($category, $target));
        $data = json_encode(json_decode(substr($schemaInfo, 1, -1), true)['options']);
        $now = new \DateTime('now');

        $schema = $this->findSchema($name);
        $newSchemaPath = sprintf(Common::systemSlash(Configuration::$schema_path_pattern), $name, $now->getTimestamp());

        $nFiles = 0;
        /** @var SplFileInfo $file */
        foreach ($schema as $file) {
            $nFiles++;
            $fileName = $file->getBasename();
            var_dump($fileName);
            $fileNameSplited = preg_split('/\./', $file->getBasename());
            $timestamp = $fileNameSplited[count($fileNameSplited) - 2];
            $lastTime = new \DateTime();
            $lastTime->setTimestamp($timestamp);
            $timeInterval = $lastTime->diff($now)->format('%d');
            $toRemove = Common::systemSlash(Configuration::$schema_path) . $fileName;
            if ($timeInterval > Configuration::$time_to_update) {
                $this->filesystem->remove($toRemove);
                $this->filesystem->touch($newSchemaPath);
                $this->filesystem->dumpFile($newSchemaPath, $data);
            } else {
                $newSchemaPath = $toRemove;
            }
        }

        if ($nFiles === 0) {
            $this->filesystem->touch($newSchemaPath);
            $this->filesystem->dumpFile($newSchemaPath, $data);
        }

        return $newSchemaPath;
    }

    private function findSchema($name)
    {
        $finder = new Finder();
        $schema = $finder
            ->files()
            ->in(Common::systemSlash(Configuration::$schema_path))
            ->name($name . '*.json');

        return $schema;
    }
}