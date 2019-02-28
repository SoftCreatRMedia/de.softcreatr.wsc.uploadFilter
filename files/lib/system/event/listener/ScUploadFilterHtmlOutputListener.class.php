<?php
namespace wcf\system\event\listener;
use wcf\system\application\ApplicationHandler;
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
		$now = new DateTime('now', $tz);
		$start = DateTime::createFromFormat('!Y-m-d', SC_UPLOAD_FREE_SUNDAY_START_DATE, $tz);
		$end = DateTime::createFromFormat('!Y-m-d', SC_UPLOAD_FREE_SUNDAY_END_DATE, $tz);
		
		if ($now->getTimestamp() < $start->getTimestamp() || $now->getTimestamp() > $end->getTimestamp()) {
			return;
		}
		
		$nodeList = [];
		
		// search images
		if (SC_UPLOAD_FREE_SUNDAY_HIDE_IMAGES) {
			foreach ($eventObj->getDocument()->getElementsByTagName('img') as $image) {
				// don't process smilies
				if (preg_match('~\bsmiley\b~', $image->getAttribute('class'))) continue;
				
				$nodeList[] = [
					'type' => 'image',
					'element' => $image
				];
			}
		}
		
		// search embedded attachments and media elements
		if (SC_UPLOAD_FREE_SUNDAY_HIDE_ATTACHMENTS || SC_UPLOAD_FREE_SUNDAY_HIDE_MEDIA) {
			foreach ($eventObj->getDocument()->getElementsByTagName('woltlab-metacode') as $element) {
				if (SC_UPLOAD_FREE_SUNDAY_HIDE_ATTACHMENTS && $element->getAttribute('data-name') === 'attach') {
					$nodeList[] = [
						'type' => 'attachment',
						'element' => $element
					];
					
					continue;
				}
				
				if (SC_UPLOAD_FREE_SUNDAY_HIDE_MEDIA && $element->getAttribute('data-name') === 'media') {
					while ($element->hasChildNodes()) {
						$element->removeChild($element->firstChild);
					}
					
					$nodeList[] = [
						'type' => 'media',
						'element' => $element
					];
					
					continue;
				}
			}
		}
		
		// search non-internal quotes
		if (SC_UPLOAD_FREE_SUNDAY_HIDE_QUOTES) {
			foreach ($eventObj->getDocument()->getElementsByTagName('woltlab-quote') as $quote) {
				$link = $quote->getAttribute('data-link');
				
				if (empty($link) || !ApplicationHandler::getInstance()->isInternalURL($link)) {
					while ($quote->hasChildNodes()) {
						$quote->removeChild($quote->firstChild);
					}
					
					$nodeList[] = [
						'type' => 'quote',
						'element' => $quote
					];
				}
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
