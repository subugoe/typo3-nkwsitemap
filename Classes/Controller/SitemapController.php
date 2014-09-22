<?php
namespace Subugoe\NkwSitemap\Controller;

/* * *************************************************************
 *  Copyright notice
 *
 *  (c) 2009 Nils K. Windisch <windisch@sub.uni-goettingen.de>
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 * ************************************************************* */
require_once(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('nkwlib') . 'class.tx_nkwlib.php');

/**
 * Plugin 'Custom Sitemap' for the 'nkwsitemap' extension.
 */
class SitemapController extends \TYPO3\CMS\Frontend\Plugin\AbstractPlugin {

	public $prefixId = 'SitemapController';
	public $scriptRelPath = 'Classes/Controller/SitemapController.php';
	public $extKey = 'nkwsitemap';
	public $pi_checkCHash = TRUE;

	/**
	 * The main method of the PlugIn
	 *
	 * @param string $content The PlugIn content
	 * @param array $conf The PlugIn configuration
	 * @return string The content that is displayed on the website
	 */
	public function main($content, $conf) {
		$this->conf = $conf;
		$this->pi_setPiVarDefaults();
		$content = $this->displayTree(\tx_nkwlib::getPageTreeIds($this->cObj->data['pages']), $GLOBALS['TSFE']->sys_page->sys_language_uid);
		return $this->pi_wrapInBaseClass($content);
	}

	/**
	 * Displays the tree for the sitemap
	 *
	 * @param string $tree
	 * @param int $lang
	 * @return string
	 */
	protected function displayTree($tree, $lang) {
		$displayTree = '<ul>';
		foreach ($tree as $key => $value) {
			$title = \tx_nkwlib::getPageTitle($key, $lang);
			$displayTree .= '<li>';
			// url title hacks
			$saveATagParams = $GLOBALS['TSFE']->ATagParams;
			$GLOBALS['TSFE']->ATagParams = 'title="' . $title . '"';
			$displayTree .= $this->pi_LinkToPage($title, $key, '', '');
			$GLOBALS['TSFE']->ATagParams = $saveATagParams;

			if ($value['children']) {
				$displayTree .= $this->displayTree($value['children'], $lang);
			}
			$displayTree .= '</li>';
		}
		$displayTree .= '</ul>';
		return $displayTree;
	}
}
