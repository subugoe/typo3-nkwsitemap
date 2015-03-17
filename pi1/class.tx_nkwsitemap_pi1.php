<?php

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

/**
 * Plugin 'Custom Sitemap' for the 'nkwsitemap' extension.
 */
class tx_nkwsitemap_pi1 extends \TYPO3\CMS\Frontend\Plugin\AbstractPlugin {

	public $prefixId = 'tx_nkwsitemap_pi1';
	public $scriptRelPath = 'pi1/class.tx_nkwsitemap_pi1.php';
	public $extKey = 'nkwsitemap';
	public $pi_checkCHash = TRUE;

	/**
	 * @var \TYPO3\CMS\Frontend\Page\PageRepository
	 */
	protected $pageRepository;

	/**
	 * @var \TYPO3\CMS\Core\Database\DatabaseConnection
	 */
	protected $db;

	/**
	 * The main method of the PlugIn
	 *
	 * @param string $content The PlugIn content
	 * @param array $conf The PlugIn configuration
	 * @return string The content that is displayed on the website
	 */
	public function main($content, $conf) {
		$this->conf = $conf;

		$this->pageRepository = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Frontend\Page\PageRepository::class);
		$this->db = $GLOBALS['TYPO3_DB'];

		$this->pi_setPiVarDefaults();
		$content .= $this->displayTree($this->getPageTreeIds($this->cObj->data['pages']));
		return $this->pi_wrapInBaseClass($content);
	}

	/**
	 * Get the IDds of a pagetree as Array
	 *
	 * @param int $startId
	 * @return array
	 */
	protected function getPageTreeIds($startId) {

		$tree = array();

		$res1 = $this->db->exec_SELECTquery(
				'uid',
				'pages',
				'pid = ' . $startId . ' AND deleted = 0 AND hidden = 0 AND pid > 0 AND t3ver_wsid = 0',
				'',
				'sorting ASC',
				'');
		while ($row1 = $this->db->sql_fetch_assoc($res1)) {
			$children = $this->getPageTreeIds($row1['uid']);
			if ($children) {
				$tree[$row1['uid']]['children'] = $this->getPageTreeIds($row1['uid']);
			} else {
				$tree[$row1['uid']]['children'] = 0;
			}
		}
		return $tree;
	}

	/**
	 * Displays the tree for the sitemap
	 *
	 * @param array $tree
	 * @return string
	 */
	protected function displayTree($tree) {
		$displayTree = '<ul>';
		foreach ($tree as $uid => $value) {

			$page = $this->pageRepository->getPage($uid);

			if ($GLOBALS['TSFE']->sys_language_uid !== 0) {
				$page = $this->pageRepository->getPageOverlay($page, $GLOBALS['TSFE']->sys_language_uid);
			}

			$title = $page['title'];
			$displayTree .= '<li>';
			// url title hacks
			$saveATagParams = $GLOBALS['TSFE']->ATagParams;
			$GLOBALS['TSFE']->ATagParams = 'title="' . $title . '"';
			$displayTree .= $this->pi_LinkToPage($title, $uid, '', '');
			$GLOBALS['TSFE']->ATagParams = $saveATagParams;

			if ($value['children']) {
				$displayTree .= $this->displayTree($value['children']);
			}
			$displayTree .= '</li>';
		}
		$displayTree .= '</ul>';
		return $displayTree;
	}

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/' . $extKey . '/pi1/class.tx_nkwsitemap_pi1.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/' . $extKey . '/pi1/class.tx_nkwsitemap_pi1.php']);
}
