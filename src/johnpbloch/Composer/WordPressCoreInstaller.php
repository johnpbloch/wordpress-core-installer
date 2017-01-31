<?php

namespace johnpbloch\Composer;

use Composer\Installer\LibraryInstaller;
use Composer\Package\PackageInterface;

class WordPressCoreInstaller extends LibraryInstaller {

	const TYPE = 'wordpress-core';
	const DEFAULT_INSTALL_DIR = 'wordpress';

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
			throw new \InvalidArgumentException( 'Two packages cannot share the same directory!' );
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

}
