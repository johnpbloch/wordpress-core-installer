<?php

namespace Tests\JohnPBloch\Composer\phpunit;

use Composer\Composer;
use Composer\Config;
use Composer\IO\NullIO;
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

}
