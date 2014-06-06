<?php
namespace Vanilla\ApplicationContextHints\Xclass\Backend\View;

/***************************************************************
 *
 *  The MIT License (MIT)
 *
 *  Copyright (c) 2014 Fabien Udriot <fabien.udriot@typo3.org>
 *
 *  Permission is hereby granted, free of charge, to any person obtaining a copy
 *  of this software and associated documentation files (the "Software"), to deal
 *  in the Software without restriction, including without limitation the rights
 *  to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 *  copies of the Software, and to permit persons to whom the Software is
 *  furnished to do so, subject to the following conditions:
 *
 *  The above copyright notice and this permission notice shall be included in
 *  all copies or substantial portions of the Software.
 *
 *  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 *  IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 *  FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 *  AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 *  LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 *  OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 *  THE SOFTWARE.
 *
 ***************************************************************/
use TYPO3\CMS\Backend\View\LogoView;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Application Context view.
 */
class ApplicationContextView extends LogoView {

	/**
	 * @var string
	 */
	protected $extensionKey = 'application_context_hints';

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
					width: 300px;
				}

				.toolbar-item-x {
					padding-top: 4px;
					padding-right: 20px;
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
			<div class="toolbar-item toolbar-item-x tooltip">
				CONTEXT: %s<span>%s</span>
			</div>';

			$toolTips = array();

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
				(string)GeneralUtility::getApplicationContext(),
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
			return $this->search($haystack[$key], $needles);
		}
		return $value;
	}

	/**
	 * Tell whether the context info should be displayed.
	 *
	 * @param $configuration
	 * @return array
	 */
	protected function showInfo($configuration){
		return (GeneralUtility::getApplicationContext()->isProduction() && (bool)$configuration['displayProductionContext'])
		|| GeneralUtility::getApplicationContext()->isDevelopment()
		|| GeneralUtility::getApplicationContext()->isTesting();
	}

	/**
	 * @return array
	 */
	protected function getExtensionConfiguration(){

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