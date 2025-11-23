<?php

namespace {

use Mpdf\Mpdf;
use SilverStripe\Core\ClassInfo;
use SilverStripe\Core\TempFolder;
use SilverStripe\Control\Director;
use SilverStripe\Security\Security;
use SilverStripe\SiteConfig\SiteConfig;

    use SilverStripe\CMS\Controllers\ContentController;

    /**
     * @template T of Page
     * @extends ContentController<T>
     */
    class PageController extends ContentController
    {
        /**
         * An array of actions that can be accessed via a request. Each array element should be an action name, and the
         * permissions or conditions required to allow the user to access it.
         *
         * <code>
         * [
         *     'action', // anyone can access this action
         *     'action' => true, // same as above
         *     'action' => 'ADMIN', // you must have ADMIN permissions to access this action
         *     'action' => '->checkAction' // you can only access this action if $this->checkAction() returns true
         * ];
         * </code>
         *
         * @var array
         */
        private static $allowed_actions = [
            'getByClass',
            'getByClassJson'
        ];

        private static $url_handlers = [
            '-/$className!/$searchTerm!' => 'getByClassJson'
        ];

        protected function init()
        {
            parent::init();
            // You can include any CSS or JS required by your project here.
            // See: https://docs.silverstripe.org/en/developer_guides/templates/requirements/
        }

        //return current user
        public function currentUser() {
            return Security::getCurrentUser();
        }


        // check if is in edit mode
        public function checkEdit() {
            if ($this->getRequest()->getVar('e')) {
                return $this->getRequest()->getVar('e');
            } else if ($this->getRequest()->postVar('e')) {
                return $this->getRequest()->postVar('e');
            }
            return false;
        }

        // check if is in add mode
        public function checkAdd() {
            if ($this->getRequest()->getVar('a')) {
                return $this->getRequest()->getVar('a');
            } else if ($this->getRequest()->postVar('a')) {
                return $this->getRequest()->postVar('a');
            }
            return false;
        }

        // get objects by class name
        public function getByClassJson() {
            $className = $this->getRequest()->param('className');
            $searchTerm = $this->getRequest()->param('searchTerm');
            //echo urldecode($className) . ' - ' . $searchTerm;
            if ($className = urldecode($className)) {
                $class = ClassInfo::class_name($className);
                if ($class) {
                    if ($searchTerm) {
                        $searchTerm = urldecode($searchTerm);
                        return json_encode($class::get()->filterAny([
                            'Title:PartialMatch' => $searchTerm,
                        ])->limit(20)->map('ID', 'Title')->toArray());
                    } else {
                        return json_encode($class::get()->limit(20)->map('ID', 'Title')->toArray());
                    }
                    
                }
            }
            return [];
        }

        public function getMyClass($classname) {
            if ($classname) {
                $class = ClassInfo::class_name($classname);
                if ($class) {
                    return $class::get();
                }
            }
        }

        public function formatPrice($price, $withCurrency = true) {
            $value = number_format($price, 2, ',', '.') . ' €';
            return $withCurrency ? $value : rtrim($value, ' €');
        }

        public function pdfHeaderFirstPage() {
            $config = SiteConfig::current_site_config();
            
            return $html = $this->customise([
                'CompanyName' => $config->CompanyName,
                'CompanyAddressLine1' => $config->CompanyAddressLine1,
                'CompanyContactPhone' => $config->CompanyContactPhone
            ])->renderWith('app/Pages/Layout/PDF/FirstHeader');
        }

        public function pdfFooter() {
            $config = SiteConfig::current_site_config();
            
            return $html = $this->customise([
                'CompanyName' => $config->CompanyName,
                'CompanyAddressLine1' => $config->CompanyAddressLine1,
                'CompanyContactPhone' => $config->CompanyContactPhone
            ])->renderWith('app/Pages/Layout/PDF/Footer');
        }

        public function generatePDF($html, $pdfName = "document") {
            $mpdf = new Mpdf([
                'mode' => 'utf-8',
                'format' => [210, 297],
                'margin_top' => 42,
                'margin_bottom' => 26,
            ]);

            //$mpdf->setAutoTopMargin();

            //$mpdf->setAutoTopMargin = 'stretch';
            //$mpdf->setAutoBottomMargin = 'stretch';

            $mpdf->SetHTMLHeader($this->pdfHeaderFirstPage());
            //write a space 
            $mpdf->WriteHTML(' ');
            //set your header
            //$mpdf->SetHTMLHeader('Other Page Header'); 
            //set your footer
            $mpdf->SetHTMLFooter($this->pdfFooter());

            $mpdf->WriteHTML($html);
            $path = TempFolder::getTempFolder(Director::baseFolder())."/".$pdfName;
            $mpdf->Output($path, 'F');

            return $path;
        }

        /**
         * get notify session data
         * @return array
         */
        public function getNotifySessionData() {
            $session = $this->getRequest()->getSession();
            $notifyData = $session->get('NotifyData');
            // clear session data after getting it
            $session->clear('NotifyData');
            return $notifyData ? $this->customise($notifyData) : [];
        }

        public function setNotifySessionData($type, $icon, $message) {
            $this->getRequest()->getSession()->set('NotifyData', [
                'type' => $type,
                'icon' => $icon,
                'message' => $message
            ]);
        }
    }
}
