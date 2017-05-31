<?php

namespace Tests\JohnPBloch\Composer\phpunit;

use Composer\Composer;
use Composer\Config;
use Composer\IO\NullIO;
use Composer\Package\Package;
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
