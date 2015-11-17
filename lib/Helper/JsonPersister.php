<?php

namespace Qaamgo\Helper;

use Qaamgo\InformationApi;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Filesystem\Filesystem;
use Qaamgo\Models\Conversion;

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
    /**
     * @var Finder
     */
    private $finder;

    private $apiInfo;

    public function __construct()
    {
        $this->finder = new Finder();
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
        $name = $category . '.' . $target;
        $this->checkSchema($name);
        /** @var Conversion $schemaInfo */
        $schemaInfo = json_encode($this->apiInfo->conversionsGet($category, $target));
        $data = substr($schemaInfo, 1, -1);

        $now = new \DateTime('now');
        $pathSchema = sprintf(Common::systemSlash(self::SCHEMA_PATH_PATTERN), $name, $now->getTimestamp());
        $this->filesystem->touch($pathSchema);
        $this->filesystem->dumpFile($pathSchema, $data);

        return $pathSchema;
    }

    /**
     * Check if the schema is out date. Refresh every 30 days.
     *
     * @param $name
     * @return bool
     */
    private function checkSchema($name)
    {
        $schema = $this->finder->files()->in(Common::systemSlash(self::SCHEMA_PATH))->name($name . '*.json');
        $now = new \DateTime('now');

        /** @var SplFileInfo $file */
        foreach ($schema as $file) {
            $fileName = $file->getBasename();
            $fileNameSplited = preg_split('/\./', $file->getBasename());
            $timestamp = $fileNameSplited[count($fileNameSplited) - 1];
            $lastTime = new \DateTime();
            $lastTime->setTimestamp($timestamp);
            $timeInterval = $lastTime->diff($now)->format('%m');
            if ($timeInterval > 1) {
                $this->filesystem->remove(Common::systemSlash(self::SCHEMA_PATH) . $fileName);
            }
        }
        return true;
    }
}