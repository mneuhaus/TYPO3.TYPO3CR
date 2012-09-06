<?php
namespace TYPO3\TYPO3CR;

/*                                                                        *
 * This script belongs to the FLOW3 framework.                            *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 *  of the License, or (at your option) any later version.                *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use \TYPO3\FLOW3\Package\Package as BasePackage;

/**
 * The TYPO3CR Package
 */
class Package extends BasePackage {

	/**
	 * Invokes custom PHP code directly after the package manager has been initialized.
	 *
	 * @param \TYPO3\FLOW3\Core\Bootstrap $bootstrap The current bootstrap
	 * @return void
	 */
	public function boot(\TYPO3\FLOW3\Core\Bootstrap $bootstrap) {
		$dispatcher = $bootstrap->getSignalSlotDispatcher();
		$dispatcher->connect('TYPO3\FLOW3\Persistence\Doctrine\PersistenceManager', 'allObjectsPersisted', 'TYPO3\TYPO3CR\Domain\Repository\NodeRepository', 'flushNodeRegistry');
		$dispatcher->connect('TYPO3\TYPO3CR\Domain\Repository\NodeRepository', 'repositoryObjectsPersisted', 'TYPO3\TYPO3CR\Domain\Repository\NodeRepository', 'flushNodeRegistry');
		$dispatcher->connect('TYPO3\TYPO3CR\Domain\Model\Node', 'nodePathChanged', 'TYPO3\FLOW3\Mvc\Routing\Aspect\RouterCachingAspect', 'flushCaches');
	}

}

?>