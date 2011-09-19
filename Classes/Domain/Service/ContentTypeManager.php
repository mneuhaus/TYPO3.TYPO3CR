<?php
namespace TYPO3\TYPO3CR\Domain\Service;

/*                                                                        *
 * This script belongs to the FLOW3 package "TYPO3CR".                    *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License as published by the *
 * Free Software Foundation, either version 3 of the License, or (at your *
 * option) any later version.                                             *
 *                                                                        *
 * This script is distributed in the hope that it will be useful, but     *
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN-    *
 * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser       *
 * General Public License for more details.                               *
 *                                                                        *
 * You should have received a copy of the GNU Lesser General Public       *
 * License along with the script.                                         *
 * If not, see http://www.gnu.org/licenses/lgpl.html                      *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

/**
 * Manager for content types
 *
 * @scope singleton
 */
class ContentTypeManager {

	/**
	 * Content types, indexed by name
	 *
	 * @var array
	 */
	protected $cachedContentTypes = array();

	/**
	 * @var array
	 */
	protected $settings;

	/**
	 * @param array $settings
	 */
	public function injectSettings(array $settings) {
		$this->settings = $settings;
	}

	/**
	 * Return all content types which have a certain $superType, without
	 * the $superType itself.
	 *
	 * @param string $superType
	 * @return array<\TYPO3\TYPO3CR\Domain\Model\ContentType> all content types registered in the system
	 */
	public function getSubContentTypes($superType) {
		if ($this->cachedContentTypes === array()) {
			$this->loadContentTypes();
		}

		$filteredContentTypes = array();
		foreach ($this->cachedContentTypes as $contentTypeName => $contentType) {
			if ($contentType->isOfType($superType) && $contentTypeName !== $superType) {
				$filteredContentTypes[$contentTypeName] = $contentType;
			}
		}
		return $filteredContentTypes;
	}

	/**
	 * Returns the speciifed content type
	 *
	 * @return \TYPO3\TYPO3CR\Domain\Model\ContentType or NULL
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function getContentType($name) {
		if ($this->cachedContentTypes === array()) {
			$this->loadContentTypes();
		}
		return isset($this->cachedContentTypes[$name]) ? $this->cachedContentTypes[$name] : NULL;
	}

	/**
	 * Checks if the specified content type exists
	 *
	 * @param string $name Name of the content type
	 * @return boolean TRUE if it exists, otherwise FALSE
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function hasContentType($name) {
		if ($this->cachedContentTypes === array()) {
			$this->loadContentTypes();
		}
		return isset($this->cachedContentTypes[$name]);
	}

	/**
	 * Creates a new content type
	 *
	 * @param string $contentTypeName Unique name of the new content type. Example: "TYPO3.TYPO3:Page"
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function createContentType($contentTypeName) {
		throw new \TYPO3\TYPO3CR\Exception('Creation of content types not supported so far; tried to create "' . $contentTypeName . '".', 1316449432);
	}

	/**
	 * Return the full configuration of all content types. This is just an internal
	 * method we need for exporting the schema to JavaScript for example.
	 *
	 * @return array
	 */
	public function getFullConfiguration() {
		if ($this->cachedContentTypes === array()) {
			$this->loadContentTypes();
		}
		$fullConfiguration = array();
		foreach ($this->cachedContentTypes as $contentTypeName => $contentType) {
			$fullConfiguration[$contentTypeName] = $contentType->getConfiguration();
		}
		return $fullConfiguration;
	}

	/**
	 * Loads all content types into memory.
	 *
	 * @return void
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	protected function loadContentTypes() {
		foreach (array_keys($this->settings['contentTypes']) as $contentTypeName) {
			$this->loadContentType($contentTypeName);
		}
	}

	/**
	 * Load one content type, if it is not loaded yet.
	 *
	 * @param string $contentTypeName
	 * @return \TYPO3\TYPO3CR\Domain\Model\ContentType
	 */
	protected function loadContentType($contentTypeName) {
		if (isset($this->cachedContentTypes[$contentTypeName])) {
			return $this->cachedContentTypes[$contentTypeName];
		}

		if (!isset($this->settings['contentTypes'][$contentTypeName])) {
			throw new \TYPO3\TYPO3CR\Exception('Content type "' . $contentTypeName . '" does not exist', 1316451800);
		}

		$contentTypeConfiguration = $this->settings['contentTypes'][$contentTypeName];

		$mergedConfiguration = array();
		$superTypes = array();
		if (isset($contentTypeConfiguration['superTypes'])) {
			foreach ($contentTypeConfiguration['superTypes'] as $superTypeName) {
				$superType = $this->loadContentType($superTypeName);
				$superTypes[] = $superType;
				$mergedConfiguration = \TYPO3\FLOW3\Utility\Arrays::arrayMergeRecursiveOverrule($mergedConfiguration, $superType->getConfiguration());
			}
			unset($mergedConfiguration['superTypes']);
		}
		$mergedConfiguration = \TYPO3\FLOW3\Utility\Arrays::arrayMergeRecursiveOverrule($mergedConfiguration, $contentTypeConfiguration);

		$contentType = new \TYPO3\TYPO3CR\Domain\Model\ContentType($contentTypeName, $superTypes, $mergedConfiguration);

		$this->cachedContentTypes[$contentTypeName] = $contentType;
		return $contentType;
	}
}
?>