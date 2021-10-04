<?php

declare(strict_types=1);

namespace system\Controller;

use system\Template\TemplateEngine;
use system\CacheManager;
use system\Event\Event;

use configuration\Config;
use system\Event\EventManager;

abstract class Controller
{
    /** @var TemplateEngine $view */
    protected $view;

    /** @var EventManager $eventManager  */
    protected $eventManager;

    /** @var int $responseCode */
    protected $responseCode;
    

    /**
     * Sets default event listeners as well as extra event listeners
     * 
     * @return void
     */
    public function setEventListeners(): void
    {
        $this->eventManager->subscribe(
            'BeforeAction',
            \Closure::fromCallable([$this, 'beforeAction'])
        );
        $this->eventManager->subscribe(
            'AfterAction',
            \Closure::fromCallable([$this, 'afterAction'])
        );
    }

    /**
     * Invokes the action the user wants to (determined by URL)
     * 
     * @param string $action
     * @param array $parameters
     * @return void
     */
    public function doAction(string $action, $parameters): void
    {
        $this->eventManager->raise( new Event('BeforeAction', $this) );
        $this->$action($parameters);
        $this->eventManager->raise( new Event('AfterAction', $this) );
    }

    /**
     * Triggers 404 page
     * 
     * @return void
     */
    public function trigger404(): void
    {
        $this->setResponseCode(\system\Response::FILE_NOT_FOUND);
        $this->view->setPage('404.tpl')->compile()->display();
    }

    /**
     * Redirects to the given page (on the current domain)
     * 
     * The redirection part will be appended to the base url
     * 
     * @param string $redirection
     * @return void
     */
    public function redirect(string $redirection): void
    {
        $redirection = Config::BASE_URL . $redirection;
        header("Location: ${redirection}");
        exit;
    }

    /**
     * Everything that should take place before the action is called
     * 
     * If you need to overwrite beforeAction, make sure to call parent::beforeAction or
     * start a session before any output happens
     * 
     * @return void
     */
    protected function beforeAction(): void
    {
        session_name(Config::SESSION_NAME);
        session_start();
        if (Config::CURRENT_ENV === Config::ENVIRONMENT_DEBUG) {
            if (! in_array( $_SERVER['REMOTE_ADDR'], Config::IP_WHITELIST) ) exit("This site is in maintenance mode.");
            CacheManager::clear(CacheManager::TEMPLATE);
        }

        $this->view->registerVar('baseUrl', Config::BASE_URL);
        $this->view->registerVar('websiteIcon', Config::ICON);
        $this->view->registerVar('title', Config::DEFAULT_TITLE);
    }
    
    /**
     * Evertyhing that should take place after the action call
     * 
     * @return void
     */
    protected function afterAction(): void
    {
        http_response_code($this->responseCode ?? \system\Response::OK);
        $this->view->compile()->display();
    }
    
    public function setResponseCode(int $code): void
    {
        $this->responseCode = $code;
    }
    
    public function setView(TemplateEngine $view): void
    {
        $this->view = $view;
    }

    public function setEventManager(EventManager $eventManager): void
    {
        $this->eventManager = $eventManager;
    }
    
    public function View(): TemplateEngine
    {
        return $this->view;
    }

    public function EventManager(): EventManager
    {
        return $this->eventManager;
    }
}
