<?php
if(!defined('TYPO3_MODE')){
    die('Access denied.');
}


/***************
 * Backend Styling
 */
$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects']['TYPO3\\CMS\\Backend\\View\\LogoView']['className'] = 'Vanilla\\ApplicationContextHints\\Xclass\\Backend\\View\\ApplicationContextView';

