<?php
namespace wcf\system\event\listener;
use wcf\system\WCF;
use DateTime;

/**
 * Does something.
 * 
 * @copyright	SoftCreatR Media
 * @license	GNU General Public License
 * @package	WoltLabSuite\Core\System\Event\Listener
 */
class ScUploadFilterAvatarRemovalListener implements IParameterizedEventListener {
	/**
	 * @inheritDoc
	 */
	public function execute($eventObj, $className, $eventName, array &$parameters) {
		$tz = WCF::getUser()->getTimeZone();
		$now = new DateTime('@' . TIME_NOW);
		$then = DateTime::createFromFormat('!Y-m-d', '2019-03-04');
		
		$now->setTimezone($tz);
		$then->setTimezone($tz);
		
		WCF::getTPL()->assign('uploadFilterRemoveAvatar', false);
		
		if ($now->getTimestamp() > $then->getTimestamp()) return;
		
		WCF::getTPL()->assign('uploadFilterRemoveAvatar', true);
	}
}
