<?php
namespace GeneralCrime\APF\extensions\arraypager\pres\controller;

use APF\core\pagecontroller\BaseDocumentController;
use APF\tools\link\LinkGenerator;
use APF\tools\link\Url;
use APF\tools\link\UrlFormatException;

/**
 * @class   iteXofYController
 * Generiert einen Pager "<< Seite X von Y >>"
 * @author  Christian Merz
 */
class SiteXofYController extends BaseDocumentController {

    /** @var string */
    private $content;


    /**
     * @public
     * Transformiert die Seite.
     * @throws UrlFormatException
     */
   public function transformContent() {

      $Config     = $this->getDocument()->getData('Config');
      $AnchorName = $this->getDocument()->getData('AnchorName');

      $localParameters = array($Config['ParameterEntries'] => $Config['Entries']);

      if ($Config['EntriesChangeable'] === TRUE) {
         $localParameters = self::getRequest()->getParameter($Config['ParameterEntries'], $Config['Entries']);
      }

      // Pager leer zurückgeben, falls keine Seiten vorhanden sind.
      if ($this->getDocument()->getData('DataCount') == 0) {
         // Content des aktuellen Designs leeren
         $this->content = '';

         return;
      }

      // Anzahl der Einträge
      $integerEntriesCount = $Config['Entries'];

      // Anzahl der Seiten generieren
      $integerPageCount = ceil($this->getDocument()->getData('DataCount') / $integerEntriesCount);
      $this->setPlaceHolder('CountPages', $integerPageCount);

      // Aktuelle Seite generieren
      $integerCurrentPage = intval(self::getRequest()->getParameter($Config['ParameterPage'], 1));
      $this->setPlaceHolder('CurrentPage', $integerCurrentPage);

      // VorherigeSeite
      if ($integerCurrentPage > 1) {
         // Template vorherige Seite ausgeben
         $objectTemplatePreviousPage = $this->getTemplate('PreviousPage_Active');

         // Link generieren
         $stringURL = LinkGenerator::generateUrl(Url::fromCurrent()->mergeQuery(array($Config['ParameterPage'] => ($integerCurrentPage - 1))));

         if (isset($AnchorName) === TRUE) {
            $objectTemplatePreviousPage->setPlaceHolder('URL', $stringURL . '#' . $this->getDocument()->getData('AnchorName'));
         } else {
            $objectTemplatePreviousPage->setPlaceHolder('URL', $stringURL);
         }

         unset($stringURL);
      } else {
         // Template vorherige Seite (inaktiv) ausgeben
         $objectTemplatePreviousPage = $this->getTemplate('PreviousPage_Inactive');
      }

      $this->setPlaceHolder('PreviousPage', $objectTemplatePreviousPage->transformTemplate());

      unset($objectTemplatePreviousPage);

      // NaechsteSeite
      if ($integerCurrentPage < $integerPageCount) {
         // Link generieren
         $stringURL = LinkGenerator::generateUrl(Url::fromCurrent()->mergeQuery(array($Config['ParameterPage'] => ($integerCurrentPage + 1))));

         $objectTemplateNextPage = $this->getTemplate('NextPage_Active');

         if (isset($AnchorName) === TRUE) {
            $objectTemplateNextPage->setPlaceHolder('URL', $stringURL . '#' . $this->getDocument()->getData('AnchorName'));
         } else {
            $objectTemplateNextPage->setPlaceHolder('URL', $stringURL);
         }
         unset($stringURL);
      } else {
         $objectTemplateNextPage = $this->getTemplate('NextPage_Inactive');
      }

      $this->setPlaceHolder('NextPage', $objectTemplateNextPage->transformTemplate());

      unset($objectTemplateNextPage);

      if ($Config['EntriesChangeable'] === TRUE) {
         // Einträge / Seite
         $arrayEntries = explode('|', $Config['EntriesPossible']);
         $stringBuffer = '';

         foreach ($arrayEntries AS &$integerEntries) {
            if ($localParameters[$Config['ParameterEntries']] == $integerEntries) {
               $objectTemplateEntries = $this->getTemplate('Entries_Active');
            } else {
               $objectTemplateEntries = $this->getTemplate('Entries_Inactive');
            }

            // Link generieren
            $stringURL = LinkGenerator::generateUrl(Url::fromCurrent()->mergeQuery(array(
                  $Config['ParameterPage']    => 1,
                  $Config['ParameterEntries'] => $integerEntries
               )));

            if (isset($AnchorName) === TRUE) {
               $objectTemplateEntries->setPlaceHolder('URL', $stringURL . '#' . $this->getDocument()->getData('AnchorName'));
            } else {
               $objectTemplateEntries->setPlaceHolder('URL', $stringURL);
            }

            unset($stringURL);

            // Anzahl einsetzen
            $objectTemplateEntries->setPlaceHolder('Entries', $integerEntries);

            // Template in Puffer einsetzen
            $stringBuffer .= $objectTemplateEntries->transformTemplate();

            unset($objectTemplateEntries);
         }

         $objectTemplateEntries = $this->getTemplate('Entries');

         $objectTemplateEntries->setPlaceHolder('Entries', $stringBuffer);

         unset($stringBuffer);

         $this->setPlaceHolder('Entries', $objectTemplateEntries->transformTemplate());

         unset($objectTemplateEntries);
      }
   }
}
