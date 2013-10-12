<?php
namespace TYPO3\TYPO3CR\Domain\Model;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3CR".               *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU General Public License, either version 3 of the   *
 * License, or (at your option) any later version.                        *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use Doctrine\ORM\Mapping as ORM;
use TYPO3\Flow\Annotations as Flow;

/**
 * A Workspace
 *
 * @Flow\Entity
 * @api
 */
class Workspace {

	/**
	 * @var string
	 * @Flow\Identity
	 * @Flow\Validate(type="StringLength", options={ "minimum"=1, "maximum"=200 })
	 */
	protected $name;

	/**
	 * Workspace (if any) this workspace is based on.
	 *
	 * Content from the base workspace will shine through in this workspace
	 * as long as they are not modified in this workspace.
	 *
	 * @var \TYPO3\TYPO3CR\Domain\Model\Workspace
	 * @ORM\ManyToOne
	 * @ORM\JoinColumn(onDelete="SET NULL")
	 */
	protected $baseWorkspace;

	/**
	 * Root node data of this workspace
	 *
	 * @var \TYPO3\TYPO3CR\Domain\Model\NodeData
	 * @ORM\ManyToOne
	 * @ORM\JoinColumn(referencedColumnName="id")
	 */
	protected $rootNodeData;

	/**
	 * @Flow\Inject
	 * @var \TYPO3\TYPO3CR\Domain\Repository\NodeDataRepository
	 */
	protected $nodeDataRepository;

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Object\ObjectManagerInterface
	 */
	protected $objectManager;

	/**
	 * Constructs a new workspace
	 *
	 * @param string $name Name of this workspace
	 * @param \TYPO3\TYPO3CR\Domain\Model\Workspace $baseWorkspace A workspace this workspace is based on (if any)
	 * @api
	 */
	public function __construct($name, \TYPO3\TYPO3CR\Domain\Model\Workspace $baseWorkspace = NULL) {
		$this->name = $name;
		$this->baseWorkspace = $baseWorkspace;
	}

	/**
	 * Initializes this workspace.
	 *
	 * If this workspace is brand new, a root node is created automatically.
	 *
	 * @param integer $initializationCause
	 * @return void
	 */
	public function initializeObject($initializationCause) {
		if ($initializationCause === \TYPO3\Flow\Object\ObjectManagerInterface::INITIALIZATIONCAUSE_CREATED) {
			$this->rootNodeData = new NodeData('/', $this);
			$this->nodeDataRepository->add($this->rootNodeData);
		}
	}

	/**
	 * Returns the name of this workspace
	 *
	 * @return string Name of this workspace
	 * @api
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * Returns the base workspace, if any
	 *
	 * @return \TYPO3\TYPO3CR\Domain\Model\Workspace
	 * @api
	 */
	public function getBaseWorkspace() {
		return $this->baseWorkspace;
	}

	/**
	 * Returns the root node data of this workspace
	 *
	 * @return \TYPO3\TYPO3CR\Domain\Model\NodeData
	 */
	public function getRootNodeData() {
		return $this->rootNodeData;
	}

	/**
	 * Publishes the content of this workspace to another workspace.
	 *
	 * The specified workspace must be a base workspace of this workspace.
	 *
	 * @param string $targetWorkspaceName Name of the workspace to publish to
	 * @return void
	 * @api
	 */
	public function publish($targetWorkspaceName) {
		$sourceNodes = $this->nodeDataRepository->findByWorkspace($this);
		$this->publishNodes($sourceNodes->toArray(), $targetWorkspaceName);
	}

	/**
	 * Publishes the given nodes to the target workspace.
	 *
	 * The specified workspace must be a base workspace of this workspace.
	 *
	 * @param array<\TYPO3\TYPO3CR\Domain\Model\NodeInterface> $nodes
	 * @param string $targetWorkspaceName Name of the workspace to publish to
	 * @return void
	 * @api
	 */
	public function publishNodes(array $nodes, $targetWorkspaceName) {
		$targetWorkspace = $this->getPublishingTargetWorkspace($targetWorkspaceName);
		foreach ($nodes as $node) {
			if ($node->getPath() !== '/') {
				$targetNode = $this->nodeDataRepository->findOneByIdentifier($node->getIdentifier(), $targetWorkspace);
				if ($targetNode !== NULL) {
					$this->nodeDataRepository->remove($targetNode);
				}
				if ($node->isRemoved() === FALSE) {
					$node->setWorkspace($targetWorkspace);
				} else {
					$this->nodeDataRepository->remove($node);
				}
			}
		}
	}

	/**
	 * Returns the number of nodes in this workspace.
	 *
	 * If $includeBaseWorkspaces is enabled, also nodes of base workspaces are
	 * taken into account. If it is disabled (default) then the number of nodes
	 * is the actual number (+1) of changes related to its base workspaces.
	 *
	 * A node count of 1 means that no changes are pending in this workspace
	 * because a workspace always contains at least its Root Node.
	 *
	 * @return integer
	 * @api
	 */
	public function getNodeCount() {
		return $this->nodeDataRepository->countByWorkspace($this);
	}

	/**
	 * Checks if the specified workspace is a base workspace of this workspace
	 * and if so, returns it.
	 *
	 * @param string $targetWorkspaceName Name of the target workspace
	 * @return \TYPO3\TYPO3CR\Domain\Model\Workspace The target workspace
	 * @throws \TYPO3\TYPO3CR\Exception\WorkspaceException if the specified workspace is not a base workspace of this workspace
	 */
	protected function getPublishingTargetWorkspace($targetWorkspaceName) {
		$targetWorkspace = $this->baseWorkspace;
		while ($targetWorkspaceName !== $targetWorkspace->getName()) {
			$targetWorkspace = $targetWorkspace->getBaseWorkspace();
			if ($targetWorkspace === NULL) {
				throw new \TYPO3\TYPO3CR\Exception\WorkspaceException('The specified workspace "' . $targetWorkspaceName . ' is not a base workspace of "' . $this->name . '".', 1289499117);
			}
		}
		return $targetWorkspace;
	}
}
