<?php

namespace Pdp;

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use PHPUnit\Framework\TestCase;

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
class PublicSuffixListManagerTest extends TestCase
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
    $this->dataDir = realpath(\dirname(__DIR__) . '/../../data');

    $this->root = vfsStream::setup('pdp');
    vfsStream::create(['cache' => []], $this->root);
    $this->cacheDir = vfsStream::url('pdp/cache');

    $this->listManager = new PublicSuffixListManager($this->cacheDir);

    $this->httpAdapter = $this->getMock(HttpAdapter\HttpAdapterInterface::class);
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

  /*
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
  */

  public function testGetHttpAdapterReturnsDefaultCurlAdapterIfAdapterNotSet()
  {
    $listManager = new PublicSuffixListManager($this->cacheDir);
    self::assertInstanceOf(
        HttpAdapter\CurlHttpAdapter::class,
        $listManager->getHttpAdapter()
    );
  }

  /*
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
    self::assertGreaterThanOrEqual(300, \count($publicSuffixList));
    self::assertTrue(array_key_exists('stuff-4-sale', $publicSuffixList['org']) !== false);
    self::assertTrue(array_key_exists('net', $publicSuffixList['ac']) !== false);
  }
  */

  /**
   * @expectedException \Exception
   * @expectedExceptionMessage Cannot write to '/does/not/exist/public-suffix-list.php
   */
  public function testWriteThrowsExceptionIfCanNotWrite()
  {
    $manager = new PublicSuffixListManager('/does/not/exist');
    $manager->writePhpCache([]);
  }

  public function testParseListToArray()
  {
    $publicSuffixList = $this->listManager->parseListToArray(
        $this->dataDir . '/' . PublicSuffixListManager::PDP_PSL_TEXT_FILE
    );
    self::assertInternalType('array', $publicSuffixList);
  }

  /**
   * @expectedException \Exception
   * @expectedExceptionMessage Cannot read '/does/not/exist/public-suffix-list.txt'
   */
  public function testParseListToArrayThrowsExceptionIfCanNotRead()
  {
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
    self::assertGreaterThanOrEqual(300, \count($publicSuffixList));
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
        ['refreshPublicSuffixList'],
        [$this->cacheDir]
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
    self::assertGreaterThanOrEqual(300, \count($publicSuffixList));
    self::assertTrue(array_key_exists('stuff-4-sale', $publicSuffixList['org']) !== false);
    self::assertTrue(array_key_exists('net', $publicSuffixList['ac']) !== false);
  }

  /**
   * @expectedException \Exception
   * @expectedExceptionMessage Cannot read '/does/not/exist/public-suffix-list.php'
   */
  public function testgetListFromFileThrowsExceptionIfCanNotRead()
  {
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
    self::assertInstanceOf('\Pdp\PublicSuffixList', $icannSuffixList);
    self::assertInstanceOf('\Pdp\PublicSuffixList', $privateSuffixList);
    self::assertInstanceOf('\Pdp\PublicSuffixList', $invalidSuffixList);
    self::assertEquals($invalidSuffixList, $publicSuffixList);
    self::assertNotEquals($privateSuffixList, $icannSuffixList);
    self::assertNotEquals($publicSuffixList, $icannSuffixList);
    self::assertNotEquals($publicSuffixList, $privateSuffixList);
  }

  /**
   * Returns a mock object for the specified class.
   *
   * This method is a temporary solution to provide backward compatibility for tests that are still using the old
   * (4.8) getMock() method.
   * We should update the code and remove this method but for now this is good enough.
   *
   *
   * @param string     $originalClassName       Name of the class to mock.
   * @param array|null $methods                 When provided, only methods whose names are in the array
   *                                            are replaced with a configurable test double. The behavior
   *                                            of the other methods is not changed.
   *                                            Providing null means that no methods will be replaced.
   * @param array      $arguments               Parameters to pass to the original class' constructor.
   * @param string     $mockClassName           Class name for the generated test double class.
   * @param bool       $callOriginalConstructor Can be used to disable the call to the original class' constructor.
   * @param bool       $callOriginalClone       Can be used to disable the call to the original class' clone
   *                                            constructor.
   * @param bool       $callAutoload            Can be used to disable __autoload() during the generation of the test
   *                                            double class.
   * @param bool       $cloneArguments
   * @param bool       $callOriginalMethods
   * @param object     $proxyTarget
   *
   * @return \PHPUnit_Framework_MockObject_MockObject
   *
   * @throws \Exception
   */
  public function getMock($originalClassName, $methods = [], array $arguments = [], $mockClassName = '', $callOriginalConstructor = true, $callOriginalClone = true, $callAutoload = true, $cloneArguments = false, $callOriginalMethods = false, $proxyTarget = null)
  {
    $builder = $this->getMockBuilder($originalClassName);

    if (\is_array($methods)) {
      $builder->setMethods($methods);
    }

    if (\is_array($arguments)) {
      $builder->setConstructorArgs($arguments);
    }

    $callOriginalConstructor ? $builder->enableOriginalConstructor() : $builder->disableOriginalConstructor();
    $callOriginalClone ? $builder->enableOriginalClone() : $builder->disableOriginalClone();
    $callAutoload ? $builder->enableAutoload() : $builder->disableAutoload();
    $cloneArguments ? $builder->enableOriginalClone() : $builder->disableOriginalClone();
    $callOriginalMethods ? $builder->enableProxyingToOriginalMethods() : $builder->disableProxyingToOriginalMethods();

    if ($mockClassName) {
      $builder->setMockClassName($mockClassName);
    }

    if ($proxyTarget) {
      $builder->setProxyTarget($proxyTarget);
    }

    $mockObject = $builder->getMock();

    return $mockObject;
  }

}
