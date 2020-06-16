<?php

namespace Test\OnlineConvert\Functional;

use OnlineConvert\Api;
use OnlineConvert\Client\OnlineConvertClient;
use OnlineConvert\Configuration;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

/**
 * Bootstrapping of classes needed for functional testing.
 */
class FunctionalTestCase extends TestCase
{
    /**
     * @const string
     */
    const DOWNLOADS_PATH = __DIR__ . '/downloads/';

    /**
     * @const string
     */
    const FILES_PATH = __DIR__ . '/files/';

    /**
     * @var Configuration
     */
    protected $config;

    /**
     * @var OnlineConvertClient
     */
    protected $client;

    /**
     * @var Api
     */
    protected $api;

    protected function setUp(): void
    {
        parent::setUp();

        $this->config = new Configuration();
        $this->config->setApiKey('main', getenv('OC_API_KEY'));
        $this->config->downloadFolder = self::DOWNLOADS_PATH;
        $this->client = new OnlineConvertClient($this->config, 'main');
        $this->api    = new Api($this->client);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        unset(
            $this->config,
            $this->client,
            $this->api
        );
    }

    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();

        self::cleanDownloadsDirectory();
    }

    private static function cleanDownloadsDirectory()
    {
        $finder     = new Finder();
        $iterator   = $finder->in(self::DOWNLOADS_PATH)
            ->ignoreDotFiles(true)
            ->getIterator();
        $dirArray   = iterator_to_array($iterator, false);
        $filesystem = new Filesystem();

        /** @var SplFileInfo $dir */
        foreach ($dirArray as $dir) {
            $filesystem->remove($dir->getPathname());
        }
    }
}
