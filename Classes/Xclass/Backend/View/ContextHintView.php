<?php
namespace Fab\ContextHints\Xclass\Backend\View;

/**
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use TYPO3\CMS\Backend\View\LogoView;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Application Context view.
 */
class ContextHintView extends LogoView {

	/**
	 * @var string
	 */
	protected $extensionKey = 'context_hints';

	/**
	 * @var array
	 */
	protected $sections = array('MAIL', 'DB', 'LOG', 'GFX', 'SYS', 'BE', 'FE', 'HTTP');

	/**
	 * Renders hints in the Backend regarding the application context next to the CMS logo.
	 *
	 * @return string Logo html code snippet to use in the backend
	 */
	public function render() {

		$content = parent::render();

		$configuration = $this->getExtensionConfiguration();

		if ($this->showInfo($configuration)) {
			$applicationContextTemplate = '
			<style>
				#typo3-logo {
					width: 400px;
				}

				.toolbar-item-x {
					padding-top: 4px;
					padding-right: 100px;
					float:right;
					color: white;
					font-size: 13px;
				}

				.tooltip span {
					z-index: 10;
					display: none;
					text-align: left;
					padding: 5px;
					width: 340px;
					font-size: 9pt;
					border-radius: 4px;
				}

				.tooltip span ul {
					padding-left: 0;
				}

				.tooltip span ul li {
					list-style: none;
				}

				.tooltip:hover span {
					display: inline;
					position: absolute;
					color: #111;
					border: 1px solid #DCA;
					background: #fffAF0;
					top: 35px;
					left:145px;
				}

			</style>
			<div class="toolbar-item toolbar-item-x">
				<a href="//%s/" target="_blank" style="color: white; text-decoration: none">%s</a> -
				<span class="tooltip">
					 %s<span>%s</span>
				</span>
			</div>
			';

			$toolTips = array();

			// Display CMS info
			$toolTips[] = sprintf('<strong>TYPO3 CMS</strong>');
			$toolTips[] = sprintf('<ul><li>version: %s</li></ul>', TYPO3_version);

			foreach ($this->sections as $section) {
				$key = strtolower($section) . 'ToolTip';
				$variables = GeneralUtility::trimExplode(',', $configuration[$key], TRUE);

				if (!empty($variables)) {

					$toolTips[] = sprintf('<strong>%s</strong>', $section);
					$toolTips[] = '<ul>';
					foreach ($variables as $variable) {
						// variable can be separated by "." to indicate a path in the array
						// e.g. development.recipients will corresponds to [development][recipients]
						$variablePaths = GeneralUtility::trimExplode('.', $variable, TRUE);
						$value = $this->search($GLOBALS['TYPO3_CONF_VARS'][$section], $variablePaths);
						$toolTips[] = sprintf('<li>%s: %s</li>', $variable, $value);
					}

					$toolTips[] = '</ul>';
				}
			}

			$applicationContextCode = sprintf($applicationContextTemplate,
				GeneralUtility::getIndpEnv('HTTP_HOST'),
				htmlspecialchars($GLOBALS['TYPO3_CONF_VARS']['SYS']['sitename']),
				strtolower((string)GeneralUtility::getApplicationContext()),
				implode("\n", $toolTips)
			);
			$content .= $applicationContextCode;
		}

		return $content;
	}

	/**
	 * Search recursively in the haystack.
	 *
	 * @param array $haystack
	 * @param array $needles
	 * @return string
	 */
	protected function search(array $haystack, array $needles) {
		if (count($needles) === 1) {
			$key = array_shift($needles);
			$value = $haystack[$key];
		} else {
			$key = array_shift($needles);

			$strippedHaystack = array();
			if (is_array($haystack[$key])) {
				$strippedHaystack = $haystack[$key];
			}
			return $this->search($strippedHaystack, $needles);
		}
		return $value;
	}

	/**
	 * Tell whether the context info should be displayed.
	 *
	 * @param $configuration
	 * @return array
	 */
	protected function showInfo($configuration) {
		return (GeneralUtility::getApplicationContext()->isProduction() && (bool)$configuration['displayProductionContext'])
		|| GeneralUtility::getApplicationContext()->isDevelopment()
		|| GeneralUtility::getApplicationContext()->isTesting();
	}

	/**
	 * @return array
	 */
	protected function getExtensionConfiguration() {

		/** @var \TYPO3\CMS\Extensionmanager\Utility\ConfigurationUtility $configurationUtility */
		$configurationUtility = $this->getObjectManager()->get('TYPO3\CMS\Extensionmanager\Utility\ConfigurationUtility');
		$rawConfiguration = $configurationUtility->getCurrentConfiguration($this->extensionKey);

		$configuration = array();
		// Fill up configuration array with relevant values.
		foreach ($rawConfiguration as $key => $data) {
			$configuration[$key] = $data['value'];
		}

		return $configuration;
	}

	/**
	 * @return \TYPO3\CMS\Extbase\Object\ObjectManager
	 */
	protected function getObjectManager() {
		return GeneralUtility::makeInstance('TYPO3\CMS\Extbase\Object\ObjectManager');
	}

}