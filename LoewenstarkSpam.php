<?php

namespace LoewenstarkSpam;

use Shopware\Components\Plugin;
use Shopware\Components\Plugin\Context\UpdateContext;
use Shopware\Components\Plugin\Context\InstallContext;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Doctrine\DBAL\Connection;
use Shopware\Components\Routing\Context;
use Shopware\Components\Routing\Router;
use Shopware\Components\Translation as TranslationComponent;
use Doctrine\ORM\Tools\SchemaTool;

/**
 * Shopware-Plugin LoewenstarkSpam.
 */
class LoewenstarkSpam extends Plugin
{
    private $translationComponent;

    /**
     * subscribe on events
     *
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            'Enlight_Controller_Action_PreDispatch' => 'addTemplateDir',
            'Enlight_Controller_Action_PostDispatch_Frontend' => 'onPostDispatch',
            'Enlight_Controller_Action_PreDispatch_Frontend_Register' => 'onSaveRegister', // Account Regestration
        ];
    }

    /*
        TODO
        - Penality Tabelle benutzen; siehe getPenalityByIp als Beispiel SQL aufruf
    */

    /*
     * Account Regestration
     */
    public function onSaveRegister(\Enlight_Event_EventArgs $arguments)
    {
        $request = $arguments->getSubject()->Request();

        if($request->getActionName()!='saveRegister') {
            return;
        }

        $check_keys = array(
            'register->personal->firstname',
            'register->personal->lastname'
        );

        $checks = array(
            $this->_valueCheck($request, $check_keys),
            $this->_keyCheck($request),
            $this->_generalCheck($request),
        );

        if(in_array(true, $checks))
        {
            $this->throw403($arguments->getSubject());
        }

        return;
    }

    public function throw403($subject)
    {
        $subject->forward('genericError', null, null, ['code' => 403]);
    }

    public function _valueCheck($request, $check_keys)
    {
        $items = $request->getParams();

        foreach($check_keys as $check_key)
        {
            $explode_path = explode('->', $check_key);
    
            $step = $items;
            foreach($explode_path as $path)
            {
                $step = $step[$path];
            }

            $value = $step;

            if (!empty($value) && (stristr($value, 'http://') || stristr($value, 'https://')))
            {
                return true;
            }
        }

        return false;
    }

    public function _keyCheck($request)
    {
        $user_key = $request->getParam('loes_id');
        $org_key = $this->getKey();

        if($user_key===$org_key)
        {
            return false;
        }else{
            return true;
        }
    }

    public function _generalCheck($request)
    {
        // some perl scripts use 1.0 as default
        if (isset($_SERVER['SERVER_PROTOCOL']) && $_SERVER['SERVER_PROTOCOL'] == 'HTTP/1.0')
        {
            return true;
        }
        // is the UA is empty, its not an customer
        $ua = isset($_SERVER['HTTP_USER_AGENT']) ? trim($_SERVER['HTTP_USER_AGENT']) : '';
        if ((!isset($_SERVER['HTTP_USER_AGENT']) || empty($ua)))
        {
            return true;
        }
        // most spammers used "//" in some cases :)
        $url = str_replace(array('https://', 'http://'), '', $request->getRequestUri());
        if (strstr($url, '//'))
        {
            return true;
        }
        // check general ua list
        if (isset($_SERVER['HTTP_USER_AGENT']) && !$this->checkUserAgent($_SERVER['HTTP_USER_AGENT']))
        {
            return true;
        }
        return false;
    }

    /**
     * 
     * @param string $ua
     * @return boolean
     */
    public function checkUserAgent($ua)
    {
        $data = array(
            'Firefox/7.0.1',
            'Firefox/8.0.1',
            'Firefox/9.0.1',
            'Firefox/10.0.1',
        );
        foreach ($data as $_data)
        {
            if (stristr($ua, $_data))
            {
                return false;
                break;
            }
        }
        return true;
    }

    private function getPenalityByIp($shopId)
    {
        $db = $this->container->get('dbal_connection');
        $shopData = $db->fetchAssoc(
            'SELECT * FROM s_core_shops WHERE active = 1 AND id = :id',
            ['id' => (int) $shopId]
        );

        return $shopData;
    }

    public function update(UpdateContext $updateContext)
    {
        if (version_compare($updateContext->getCurrentVersion(), '1.0.0', '<=')) {
            $this->createOldUrlTable();
        }
    }

    /**
     * @param InstallContext $context
     */
    public function install(InstallContext $context)
    {
        $this->createPenaltyTable();
    }

    public function createPenaltyTable()
    {
        $em = $this->container->get('models');
        $schemaTool = new SchemaTool($em);
        $schemaTool->updateSchema(
            [ $em->getClassMetadata(\LoewenstarkSpam\Models\Penalty::class) ],
            true
        );
    }

    /**
     * Adds the Resources/view/  directory.
     *
     * @param \Enlight_Controller_ActionEventArgs $args
     */
    public function addTemplateDir(\Enlight_Controller_ActionEventArgs $args)
    {
        $args->getSubject()->View()->addTemplateDir($this->getPath() . '/Resources/views');
    }

    /**
     * @param \Enlight_Controller_EventArgs $args
     * @throws \Enlight_Exception
     */
    public function onPostDispatch(\Enlight_Controller_ActionEventArgs $args)
    {
        $shop = false;
        if ($this->container->initialized('shop')) {
            $shop = $this->container->get('shop');
        }
        if (!$shop) {
            $shop = $this->container->get('models')->getRepository(\Shopware\Models\Shop\Shop::class)->getActiveDefault();
        }
        $controller = $args->getSubject();
        $view = $controller->View();
        $view->assign('loewenstark_spam_key', $this->getKey());
    }

    public function getKey()
    {
        return $this->getKeyPrefix() . $this->getNumberOfTheDay();
    }

    public function getKeyPrefix()
    {
        return '84O';
    }

    /*
     * Return every day a new number
     */
    public function getNumberOfTheDay()
    {
        return date('j') + 123;
    }
}
