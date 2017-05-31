<?php

namespace Tests\JohnPBloch\Composer\phpunit;

use Composer\Composer;
use Composer\Config;
use Composer\IO\NullIO;
use Composer\Package\Package;
use Composer\Package\RootPackage;
use johnpbloch\Composer\WordPressCoreInstaller;
use PHPUnit\Framework\TestCase;

class WordPressCoreInstallerTest extends TestCase {

	protected function setUp() {
		$this->resetInstallPaths();
	}

	protected function tearDown() {
		$this->resetInstallPaths();
	}

	public function testSupports() {
		$installer = new WordPressCoreInstaller( new NullIO(), $this->createComposer() );

		$this->assertTrue( $installer->supports( 'wordpress-core' ) );
		$this->assertFalse( $installer->supports( 'not-wordpress-core' ) );
	}

	public function testDefaultInstallDir() {
		$installer = new WordPressCoreInstaller( new NullIO(), $this->createComposer() );
		$package   = new Package( 'johnpbloch/test-package', '1.0.0.0', '1.0.0' );

		$this->assertEquals( 'wordpress', $installer->getInstallPath( $package ) );
	}

	public function testSingleRootInstallDir() {
		$composer    = $this->createComposer();
		$rootPackage = new RootPackage( 'test/root-package', '1.0.1.0', '1.0.1' );
		$composer->setPackage( $rootPackage );
		$installDir = 'tmp-wp-' . rand( 0, 9 );
		$rootPackage->setExtra( array(
			'wordpress-install-dir' => $installDir,
		) );
		$installer = new WordPressCoreInstaller( new NullIO(), $composer );

		$this->assertEquals(
			$installDir,
			$installer->getInstallPath(
				new Package( 'not/important', '1.0.0.0', '1.0.0' )
			)
		);
	}

	public function testArrayOfInstallDirs() {
		$composer    = $this->createComposer();
		$rootPackage = new RootPackage( 'test/root-package', '1.0.1.0', '1.0.1' );
		$composer->setPackage( $rootPackage );
		$rootPackage->setExtra( array(
			'wordpress-install-dir' => array(
				'test/package-one' => 'install-dir/one',
				'test/package-two' => 'install-dir/two',
			),
		) );
		$installer = new WordPressCoreInstaller( new NullIO(), $composer );

		$this->assertEquals(
			'install-dir/one',
			$installer->getInstallPath(
				new Package( 'test/package-one', '1.0.0.0', '1.0.0' )
			)
		);

		$this->assertEquals(
			'install-dir/two',
			$installer->getInstallPath(
				new Package( 'test/package-two', '1.0.0.0', '1.0.0' )
			)
		);
	}

	private function resetInstallPaths() {
		$prop = new \ReflectionProperty( '\johnpbloch\Composer\WordPressCoreInstaller', '_installedPaths' );
		$prop->setAccessible( true );
		$prop->setValue( array() );
	}

	/**
	 * @return Composer
	 */
	private function createComposer() {
		$composer = new Composer();
		$composer->setConfig( new Config() );

		return $composer;
	}

}
