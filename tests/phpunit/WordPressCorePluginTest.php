<?php

/**
 * WordPress Core Installer - A Composer to install WordPress in a webroot subdirectory
 * Copyright (C) 2013    John P. Bloch
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

namespace Tests\JohnPBloch\Composer\phpunit;

use Composer\Composer;
use Composer\Config;
use Composer\Installer\InstallationManager;
use Composer\IO\IOInterface;
use Composer\IO\NullIO;
use Composer\Plugin\PluginInterface;
use Composer\Test\Mock\HttpDownloaderMock;
use Composer\Util\HttpDownloader;
use Composer\Util\Loop;
use johnpbloch\Composer\WordPressCorePlugin;
use PHPUnit\Framework\TestCase;

class WordPressCorePluginTest extends TestCase {

	public function testActivate() {
		$composer = new Composer();
		$composer->setConfig( new Config() );
		$nullIO              = new NullIO();
		$installationManager = $this->getInstallationManager( $composer, $nullIO );
		$composer->setInstallationManager( $installationManager );
		$composer->setConfig( new Config() );

		$plugin = new WordPressCorePlugin();
		$plugin->activate( $composer, $nullIO );

		$installer = $installationManager->getInstaller( 'wordpress-core' );

		$this->assertInstanceOf( '\johnpbloch\Composer\WordPressCoreInstaller', $installer );
	}

	/**
	 * @param Composer $composer
	 * @param IOInterface $io
	 *
	 * @return InstallationManager
	 */
	private function getInstallationManager( $composer, $io ) {
		$installationManager = null;
		switch ( explode( '.', PluginInterface::PLUGIN_API_VERSION )[0] ) {
			case '1':
				$installationManager = new InstallationManager();
				break;
			case '2':
			default:
				$http                = new HttpDownloader( $io, $composer->getConfig() );
				$loop                = new Loop( $http );
				$installationManager = new InstallationManager( $loop, $io );
				break;
		}

		return $installationManager;
	}

}
