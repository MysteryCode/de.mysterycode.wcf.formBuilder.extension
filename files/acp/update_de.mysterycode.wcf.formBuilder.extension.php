<?php

/** @var ScriptPackageInstallationPlugin $this */

use wcf\system\package\plugin\ScriptPackageInstallationPlugin;
use wcf\system\WCF;
use wcf\util\FileUtil;

$packageID = $this->installation->getPackageID();

$statement = WCF::getDB()->prepareStatement('
	SELECT  *
	FROM	wcf' . WCF_N . '_package_installation_file_log
	WHERE   packageID = ?
');
$statement->execute([$packageID]);

while ($row = $statement->fetchArray()) {
	if (!defined(\mb_strtoupper($row['application']) . '_DIR') || empty($row['filename'])) {
		continue;
	}
	
	$filename = FileUtil::getRealPath(\constant(\mb_strtoupper($row['application']) . '_DIR')) . $row['filename'];
	
	if (\file_exists($filename)) {
		\unlink($filename);
	}
}

WCF::getDB()->prepareStatement('DELETE FROM wcf' . WCF_N . '_package_installation_file_log WHERE packageID = ?')->execute([$packageID]);

$statement = WCF::getDB()->prepareStatement('
	SELECT  *
	FROM	wcf' . WCF_N . '_acp_template
	WHERE   packageID = ?
');
$statement->execute([$packageID]);

while ($row = $statement->fetchArray()) {
	if (!defined(\mb_strtoupper($row['application']) . '_DIR') || empty($row['templateName'])) {
		continue;
	}
	
	$filename = FileUtil::getRealPath($row['application']) . 'acp/templates/' .  $row['templateName'];
	
	if (\file_exists($filename)) {
		\unlink($filename);
	}
}

WCF::getDB()->prepareStatement('DELETE FROM wcf' . WCF_N . '_acp_template WHERE packageID = ?')->execute([$packageID]);

$statement = WCF::getDB()->prepareStatement('
	SELECT  *
	FROM	wcf' . WCF_N . '_template
	WHERE   packageID = ?
');
$statement->execute([$packageID]);

while ($row = $statement->fetchArray()) {
	if (!defined(\mb_strtoupper($row['application']) . '_DIR') || empty($row['templateName'])) {
		continue;
	}
	
	$filename = FileUtil::getRealPath(\constant(\mb_strtoupper($row['application']) . '_DIR')) . 'templates/' .  $row['templateName'];
	
	if (\file_exists($filename)) {
		\unlink($filename);
	}
}

WCF::getDB()->prepareStatement('DELETE FROM wcf' . WCF_N . '_template WHERE packageID = ?')->execute([$packageID]);
