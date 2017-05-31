<?php

namespace Tests\JohnPBloch\Composer\phpunit;

use Composer\Composer;
use Composer\Config;
use Composer\IO\NullIO;
use Composer\Package\Package;
use johnpbloch\Composer\WordPressCoreInstaller;
use PHPUnit\Framework\TestCase;

class WordPressCoreInstallerTest extends TestCase {

	public function testSupports() {
		$composer = new Composer();
		$composer->setConfig( new Config() );
		$installer = new WordPressCoreInstaller( new NullIO(), $composer );

		$this->assertTrue( $installer->supports( 'wordpress-core' ) );
		$this->assertFalse( $installer->supports( 'not-wordpress-core' ) );
	}

	public function testDefaultInstallDir() {
		$composer = new Composer();
		$composer->setConfig( new Config() );
		$installer = new WordPressCoreInstaller( new NullIO(), $composer );
		$package   = new Package( 'johnpbloch/test-package', '1.0.0.0', '1.0.0' );

		$this->assertEquals( 'wordpress', $installer->getInstallPath( $package ) );
	}

}
