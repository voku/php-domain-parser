<?php

namespace Pdp;

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;

// work around PHP 5.3 quirky behavior with ftruncate() and streams
// @see https://bugs.php.net/bug.php?id=53888
if (version_compare(PHP_VERSION, '5.4.0') < 0) {
  /**
   * @param $fp
   * @param $size
   *
   * @return bool
   */
  function ftruncate($fp, $size)
  {
    /** @noinspection PhpUsageOfSilenceOperatorInspection */
    return @\ftruncate($fp, $size) || true;
  }
}

/**
 * Class PublicSuffixListManagerTest
 *
 * @package Pdp
 */
class PublicSuffixListManagerTest extends \PHPUnit_Framework_TestCase
{
  /**
   * @var PublicSuffixListManager List manager
   */
  protected $listManager;

  /**
   * @var vfsStreamDirectory
   */
  protected $root;

  /**
   * @var string Cache directory
   */
  protected $cacheDir;

  /**
   * @var string Source file name
   */
  protected $sourceFile;

  /**
   * @var string Cache file name
   */
  protected $cacheFile;

  /**
   * @var string data dir
   */
  protected $dataDir;

  /**
   * @var string url
   */
  protected $publicSuffixListUrl = 'https://publicsuffix.org/list/effective_tld_names.dat';

  /**
   * @var \Pdp\HttpAdapter\HttpAdapterInterface|\PHPUnit_Framework_MockObject_MockObject Http adapter
   */
  protected $httpAdapter;

  protected function setUp()
  {
    parent::setUp();

    /** @noinspection RealpathOnRelativePathsInspection */
    $this->dataDir = realpath(dirname(__DIR__) . '/../../data');

    $this->root = vfsStream::setup('pdp');
    vfsStream::create(array('cache' => array()), $this->root);
    $this->cacheDir = vfsStream::url('pdp/cache');

    $this->listManager = new PublicSuffixListManager($this->cacheDir);

    $this->httpAdapter = $this->getMock('\Pdp\HttpAdapter\HttpAdapterInterface');
    $this->listManager->setHttpAdapter($this->httpAdapter);
  }

  protected function tearDown()
  {
    $this->cacheDir = null;
    $this->root = null;
    $this->httpAdapter = null;
    $this->listManager = null;

    parent::tearDown();
  }

  public function testRefreshPublicSuffixList()
  {
    $content = file_get_contents(
        $this->dataDir . '/' . PublicSuffixListManager::PDP_PSL_TEXT_FILE
    );

    $this->httpAdapter->expects($this->once())
                      ->method('getContent')
                      ->with($this->publicSuffixListUrl)
                      ->will($this->returnValue($content));

    self::assertFileNotExists(
        $this->cacheDir . '/' . PublicSuffixListManager::PDP_PSL_TEXT_FILE
    );
    self::assertFileNotExists(
        $this->cacheDir . '/' . PublicSuffixListManager::PDP_PSL_PHP_FILE
    );

    $this->listManager->refreshPublicSuffixList();

    self::assertFileExists(
        $this->cacheDir . '/' . PublicSuffixListManager::PDP_PSL_TEXT_FILE
    );
    self::assertFileExists(
        $this->cacheDir . '/' . PublicSuffixListManager::PDP_PSL_PHP_FILE
    );
  }

  public function testFetchListFromSource()
  {
    $content = file_get_contents(
        $this->dataDir . '/' . PublicSuffixListManager::PDP_PSL_TEXT_FILE
    );

    $this->httpAdapter->expects($this->once())
                      ->method('getContent')
                      ->with($this->publicSuffixListUrl)
                      ->will($this->returnValue($content));

    $publicSuffixList = $this->listManager->fetchListFromSource();
    self::assertGreaterThanOrEqual(100000, $publicSuffixList);
  }

  public function testGetHttpAdapterReturnsDefaultCurlAdapterIfAdapterNotSet()
  {
    $listManager = new PublicSuffixListManager($this->cacheDir);
    self::assertInstanceOf(
        '\Pdp\HttpAdapter\CurlHttpAdapter',
        $listManager->getHttpAdapter()
    );
  }

  public function testWritePhpCache()
  {
    self::assertFileNotExists(
        $this->cacheDir . '/' . PublicSuffixListManager::PDP_PSL_PHP_FILE
    );
    $array = $this->listManager->parseListToArray(
        $this->dataDir . '/' . PublicSuffixListManager::PDP_PSL_TEXT_FILE
    );
    self::assertGreaterThanOrEqual(230000, $this->listManager->writePhpCache($array));
    self::assertFileExists(
        $this->cacheDir . '/' . PublicSuffixListManager::PDP_PSL_PHP_FILE
    );
    $publicSuffixList = include $this->cacheDir . '/' . PublicSuffixListManager::PDP_PSL_PHP_FILE;
    self::assertInternalType('array', $publicSuffixList);
    self::assertGreaterThanOrEqual(300, count($publicSuffixList));
    self::assertTrue(array_key_exists('stuff-4-sale', $publicSuffixList['org']) !== false);
    self::assertTrue(array_key_exists('net', $publicSuffixList['ac']) !== false);
  }

  public function testWriteThrowsExceptionIfCanNotWrite()
  {
    $this->setExpectedException('\Exception', "Cannot write to '/does/not/exist/public-suffix-list.php'");
    $manager = new PublicSuffixListManager('/does/not/exist');
    $manager->writePhpCache(array());
  }

  public function testParseListToArray()
  {
    $publicSuffixList = $this->listManager->parseListToArray(
        $this->dataDir . '/' . PublicSuffixListManager::PDP_PSL_TEXT_FILE
    );
    self::assertInternalType('array', $publicSuffixList);
  }

  public function testParseListToArrayThrowsExceptionIfCanNotRead()
  {
    $this->setExpectedException('\Exception', "Cannot read '/does/not/exist/public-suffix-list.txt'");
    /** @noinspection OnlyWritesOnParameterInspection */
    /** @noinspection PhpUnusedLocalVariableInspection */
    $publicSuffixList = $this->listManager->parseListToArray(
        '/does/not/exist/' . PublicSuffixListManager::PDP_PSL_TEXT_FILE
    );
  }

  public function testGetList()
  {
    copy(
        $this->dataDir . '/' . PublicSuffixListManager::PDP_PSL_PHP_FILE,
        $this->cacheDir . '/' . PublicSuffixListManager::PDP_PSL_PHP_FILE
    );
    self::assertFileExists(
        $this->cacheDir . '/' . PublicSuffixListManager::PDP_PSL_PHP_FILE
    );
    $publicSuffixList = $this->listManager->getList();
    self::assertInstanceOf('\Pdp\PublicSuffixList', $publicSuffixList);
    self::assertGreaterThanOrEqual(300, count($publicSuffixList));
    self::assertTrue(array_key_exists('stuff-4-sale', $publicSuffixList['org']) !== false);
    self::assertTrue(array_key_exists('net', $publicSuffixList['ac']) !== false);
  }

  public function testGetListWithoutCache()
  {
    self::assertFileNotExists(
        $this->cacheDir . '/' . PublicSuffixListManager::PDP_PSL_PHP_FILE
    );

    /** @var PublicSuffixListManager|\PHPUnit_Framework_MockObject_MockObject $listManager */
    $listManager = $this->getMock(
        '\Pdp\PublicSuffixListManager',
        array('refreshPublicSuffixList'),
        array($this->cacheDir)
    );

    $dataDir = $this->dataDir;
    $cacheDir = $this->cacheDir;

    $listManager->expects($this->once())
                ->method('refreshPublicSuffixList')
                ->will(
                    $this->returnCallback(
                        function () use ($dataDir, $cacheDir) {
                          copy(
                              $dataDir . '/' . PublicSuffixListManager::PDP_PSL_PHP_FILE,
                              $cacheDir . '/' . PublicSuffixListManager::PDP_PSL_PHP_FILE
                          );
                        }
                    )
                );

    $publicSuffixList = $listManager->getList(PublicSuffixListManager::ALL_DOMAINS, false);
    self::assertInstanceOf('\Pdp\PublicSuffixList', $publicSuffixList);
  }

  public function testGetProvidedListFromDefaultCacheDir()
  {
    // By not providing cache I'm forcing use of default cache dir
    $listManager = new PublicSuffixListManager();
    $publicSuffixList = $listManager->getList();
    self::assertInstanceOf('\Pdp\PublicSuffixList', $publicSuffixList);
    self::assertGreaterThanOrEqual(300, count($publicSuffixList));
    self::assertTrue(array_key_exists('stuff-4-sale', $publicSuffixList['org']) !== false);
    self::assertTrue(array_key_exists('net', $publicSuffixList['ac']) !== false);
  }

  public function testgetListFromFileThrowsExceptionIfCanNotRead()
  {
    $this->setExpectedException('\Exception', "Cannot read '/does/not/exist/public-suffix-list.php'");
    /** @noinspection OnlyWritesOnParameterInspection */
    /** @noinspection PhpUnusedLocalVariableInspection */
    $publicSuffixList = $this->listManager->getListFromFile(
        '/does/not/exist/' . PublicSuffixListManager::PDP_PSL_PHP_FILE
    );
  }

  public function testGetDifferentPublicList()
  {
    $listManager = new PublicSuffixListManager();
    $publicSuffixList = $listManager->getList();
    $icannSuffixList = $listManager->getList(PublicSuffixListManager::ICANN_DOMAINS, false);
    $privateSuffixList = $listManager->getList(PublicSuffixListManager::PRIVATE_DOMAINS, false);
    $invalidSuffixList = $listManager->getList('invalid type');
    $this->assertInstanceOf('\Pdp\PublicSuffixList', $icannSuffixList);
    $this->assertInstanceOf('\Pdp\PublicSuffixList', $privateSuffixList);
    $this->assertInstanceOf('\Pdp\PublicSuffixList', $invalidSuffixList);
    $this->assertEquals($invalidSuffixList, $publicSuffixList);
    $this->assertNotEquals($privateSuffixList, $icannSuffixList);
    $this->assertNotEquals($publicSuffixList, $icannSuffixList);
    $this->assertNotEquals($publicSuffixList, $privateSuffixList);
  }
}
