<?php
if(!defined('TYPO3_MODE')){
    die('Access denied.');
}

# Add Context Info next to the TYPO3 logo.
$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects']['TYPO3\CMS\Backend\View\LogoView']['className'] = 'Fab\ContextHints\Xclass\Backend\View\ContextHintView';

