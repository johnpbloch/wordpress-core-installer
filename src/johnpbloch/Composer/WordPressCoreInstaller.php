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

use Composer\Installer\LibraryInstaller;
use Composer\Package\PackageInterface;

class WordPressCoreInstaller extends LibraryInstaller {

	const TYPE = 'wordpress-core';
	const DEFAULT_INSTALL_DIR = 'wordpress';

	const MESSAGE_CONFLICT = 'Two packages (%s and %s) cannot share the same directory!';

	private static $_installedPaths = array();

	public static function extractInstallDir(PackageInterface $package, PackageInterface $root = null) {
		$installationDir = false;
		$prettyName      = $package->getPrettyName();
		if ( $root ) {
			$topExtra = $root->getExtra();
			if ( ! empty( $topExtra['wordpress-install-dir'] ) ) {
				$installationDir = $topExtra['wordpress-install-dir'];
				if ( is_array( $installationDir ) ) {
					$installationDir = empty( $installationDir[$prettyName] ) ? false : $installationDir[$prettyName];
				}
			}
		}
		$extra = $package->getExtra();
		if ( ! $installationDir && ! empty( $extra['wordpress-install-dir'] ) ) {
			$installationDir = $extra['wordpress-install-dir'];
		}
		if ( ! $installationDir ) {
			$installationDir = self::DEFAULT_INSTALL_DIR;
		}
		return $installationDir;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getInstallPath( PackageInterface $package ) {
		$installationDir = self::extractInstallDir($package, $this->composer->getPackage());
		if (
			! empty( self::$_installedPaths[$installationDir] ) &&
			$package->getPrettyName() !== self::$_installedPaths[$installationDir]
		) {
			$conflict_message = $this->getConflictMessage( $prettyName, self::$_installedPaths[ $installationDir ] );
			throw new \InvalidArgumentException( $conflict_message );
		}
		self::$_installedPaths[$installationDir] = $package->getPrettyName();
		return $installationDir;
	}

	/**
	 * {@inheritDoc}
	 */
	public function supports( $packageType ) {
		return self::TYPE === $packageType;
	}

	/**
	 * Get the exception message with conflicting packages
	 *
	 * @param string $attempted
	 * @param string $alreadyExists
	 *
	 * @return string
	 */
	private function getConflictMessage( $attempted, $alreadyExists ) {
		return sprintf( self::MESSAGE_CONFLICT, $attempted, $alreadyExists );
	}

}
