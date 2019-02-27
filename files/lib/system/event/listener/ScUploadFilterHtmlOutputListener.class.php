<?php
namespace wcf\system\event\listener;
use wcf\system\WCF;
use DateTime;

/**
 * Replaces images and embedded attachments with an
 * error container.
 * 
 * @copyright	SoftCreatR Media
 * @license	GNU General Public License
 * @package	WoltLabSuite\Core\System\Event\Listener
 */
class ScUploadFilterHtmlOutputListener implements IParameterizedEventListener {
	/**
	 * @inheritDoc
	 */
	public function execute($eventObj, $className, $eventName, array &$parameters) {
		$tz = WCF::getUser()->getTimeZone();
		$now = new DateTime('@' . TIME_NOW);
		$then = DateTime::createFromFormat('!Y-m-d', '2019-03-04');
		
		$now->setTimezone($tz);
		$then->setTimezone($tz);
		
		if ($now->getTimestamp() > $then->getTimestamp()) return;
		
		$nodeList = [];
		
		// search images
		foreach ($eventObj->getDocument()->getElementsByTagName('img') as $image) {
			// don't process smilies
			if (preg_match('~\bsmiley\b~', $image->getAttribute('class'))) continue;
			
			// don't process images without src
			if ($image->getAttribute('src')) continue;
			
			$nodeList[] = [
				'type' => 'image',
				'element' => $image
			];
		}
		
		// search embedded attachments
		foreach ($eventObj->getDocument()->getElementsByTagName('woltlab-metacode') as $element) {
			if ($element->getAttribute('data-name') === 'attach') {
				$nodeList[] = [
					'type' => 'attachment',
					'element' => $element
				];
			}
		}
		
		foreach ($nodeList as $node) {
			$scImage = $eventObj->renameTag($node['element'], 'p');
			$scImage->setAttribute('class', 'error scBlockedImage');
			
			$fragment = $node['element']->ownerDocument->createDocumentFragment();
			$fragment->appendXML(WCF::getLanguage()->getDynamicVariable('wcf.message.error.scUploadFilter', ['type' => $node['type']]));
			$scImage->appendChild($fragment);
		}
	}
}
