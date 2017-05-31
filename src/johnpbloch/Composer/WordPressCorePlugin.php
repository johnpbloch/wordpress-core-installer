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

namespace johnpbloch\Composer;

use Composer\Composer;
use Composer\DependencyResolver\Operation\InstallOperation;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\Factory;
use Composer\Installer;
use Composer\Installer\PackageEvent;
use Composer\Installer\PackageEvents;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Composer\Repository\InstalledArrayRepository;

class WordPressCorePlugin implements EventSubscriberInterface, PluginInterface {

	protected $composer;
	protected $io;
	protected $runInstallAgain = false;

	/**
	 * Apply plugin modifications to composer
	 *
	 * @param Composer    $composer
	 * @param IOInterface $io
	 */
	public function activate( Composer $composer, IOInterface $io ) {
		$this->composer = $composer;
		$this->io = $io;
		$installer = new WordPressCoreInstaller( $io, $composer );
		$composer->getInstallationManager()->addInstaller( $installer );
	}

	public static function getSubscribedEvents() {
		return array(
			PackageEvents::POST_PACKAGE_INSTALL => 'onPostPackageInstallOrUpdate',
			PackageEvents::POST_PACKAGE_UPDATE => 'onPostPackageInstallOrUpdate',
		);
	}

	public function onPostPackageInstallOrUpdate(PackageEvent $e)
	{
		// if the package that's currently getting updated or installed is of type "wordpress-core", then we must re-install themes and plugins if they're supposed to get installed into a subdir of wordpress itself using composer/installer's "installer-paths" feature
		$thisOperation = $e->getOperation();
		if ($thisOperation->getJobType() == 'update') {
			$thisPackage = $thisOperation->getTargetPackage();
		} elseif ($thisOperation->getJobType() == 'install') {
			// regular installs also matter if a theme or plugin package name is alphabetically "smaller" than "johnpbloch/wordpress" (or whatever the name is), then it'd get installed first and overwritten
			$thisPackage = $thisOperation->getPackage();
		} else {
			return;
		}
		// we're only interested in installs or updates of WordPress itself
		if ($thisPackage->getType() != 'wordpress-core') {
			return;
		}
		
		$root = $this->composer->getPackage();
		$wpInstallationDir = rtrim(WordPressCoreInstaller::extractInstallDir($thisPackage, $root), '/\\');
		
		$extra = $root->getExtra();
		// we only have to do anything in one case:
		// if the installer path for anything is a subdirectory of wordpress itself
		// e.g. this:
		// "extra": {
		//   "wordpress-install-dir": "wordpress",
		//   "installer-paths": {
		//     "wordpress/wp-content/mu-plugins/{$name}/": ["type:wordpress-muplugin"],
		//     "wordpress/wp-content/plugins/{$name}/": ["type:wordpress-plugin"],
		//     "wordpress/wp-content/themes/{$name}/": ["type:wordpress-theme"]
		//   }
		// },
		$nestedPackages = array();
		$nestedTypes = array();
		$nestedVendors = array();
		if (isset($extra['installer-paths']) && is_array($extra['installer-paths'])) {
			foreach ($extra['installer-paths'] as $path => $instructions) {
				if (strpos($path, "$wpInstallationDir/") !== 0 && strpos($path, "$wpInstallationDir\\") !== 0) {
					continue;
				}
				foreach ((array)$instructions as $instruction) {
					// each array entry can be a package name, or a "type:...", or a "vendor:..."
					if (strpos($instruction, 'type:') === 0) {
						$nestedTypes[] = substr($instruction, 5);
					} elseif (strpos($instruction, 'vendor:') === 0) {
						$nestedVendors[] = substr($instruction, 7);
					} else {
						$nestedPackages[] = $instruction;
					}
				}
			}
		}
		
		if (!$nestedPackages && !$nestedTypes && !$nestedVendors) {
			return;
		}
		
		$im = $this->composer->getInstallationManager();
		$repo = $e->getInstalledRepo();
		
		// reverse over list of install steps to put the ones that Composer would install after wordpress-core anyway on an ignore list
		$ignore = array();
		foreach (array_reverse($e->getOperations()) as $otherOperation) {
			if ($otherOperation == $thisOperation) {
				// any operation after the install/update of this package (so any before in the reversed array) we do not want to duplicate
				// otherwise, the second install might cause an error because the package is already there, e.g. if it's from a path repo and got symlinked
				break;
			}
			if ($otherOperation->getJobType() == 'install') {
				$ignore[] = $otherOperation->getPackage()->getPrettyName();
			} elseif ($otherOperation->getJobType() == 'update') {
				$ignore[] = $otherOperation->getTargetPackage()->getPrettyName();
			}
		}
		
		$needsInstall = array();
		foreach ($e->getRequest()->getJobs() as $job) {
			if (!isset($job['packageName'])) {
				continue;
			}
			$package = $repo->findPackage($job['packageName'], $job['constraint']);
			if (!$package || in_array($package->getPrettyName(), $ignore)) {
				continue;
			}
			if (
				in_array($package->getPrettyName(), $nestedPackages) || 
				in_array($package->getType(), $nestedTypes) || 
				in_array(explode('/', $package->getPrettyName(), 2)[0], $nestedVendors)
			) {
				$needsInstall[] = $package;
			}
		}
		
		$repo = new InstalledArrayRepository(array());
		foreach ($needsInstall as $package) {
			$im->install($repo, new InstallOperation($package, $thisOperation->getReason())); // re-use reason from wordpress-core install/update operation
			if ($this->io->isVeryVerbose()) {
				$this->io->writeError('    REASON: nested inside installed or updated '.$thisPackage->getPrettyName());
				$this->io->writeError('');
			}
		}
	}
}
