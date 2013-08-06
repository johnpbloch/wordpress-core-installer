<?php

namespace johnpbloch\Composer;

use Composer\Installer\LibraryInstaller;

class WordPressCoreInstaller extends LibraryInstaller {

	const TYPE = 'wordpress-core';

	/**
	 * {@inheritDoc}
	 */
	public function supports( $packageType ) {
		return self::TYPE === $packageType;
	}

}
