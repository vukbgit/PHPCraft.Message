<?php

namespace PHPCraft\Message;

use PHPCraft\Cookie;

/**
 * Manages application messages storing them onto various supports (currently only cookies implemented)
 *
 * @author vuk <info@vuk.bg.it>
 */
class Message
{
    private $cookieBuilder;
    private $innerMessages;

    /**
     * Constructor.
     **/
    public function __construct()
    {
    }

    /**
    * Sets optional dependency for cookies support
    *
    * @param PHPCraft\Cookie\CookieBuilderInterface $cookieBuilder
    */
    public function setCookieBuilder(\PHPCraft\Cookie\CookieBuilderInterface $cookieBuilder)
    {
        $this->cookieBuilder = $cookieBuilder;
    }
    
    /**
    * Saves a message
    *
    * @param string $support: support to save message to, so far only 'cookies'
    * @param string $category: used to index message; for example Bootstrap contextual background helper classes (http://getbootstrap.com/css/#helper-classes-backgrounds) may be used for template benefit 
    * @throws Exception if $support is 'cookies' and $this->cookieBuilder has not been set
    * @throws DomainException if $support is not handled
    */
    function save($support,$category,$message)
    {
        switch($support) {
            case 'inner':
                if(!$this->innerMessages) $this->innerMessages = array();
                if(!isset($this->innerMessages[$category])) $this->innerMessages[$category] = array(); 
                $this->innerMessages[$category][] = $message;
            break;
            case 'cookies':
                if(!$this->cookieBuilder) throw new \Exception('cookieBuilder must be set');
                $messages = (array) json_decode($this->cookieBuilder->get('messages'));
                if(!$messages) $messages = array();
                if(!isset($messages[$category])) $messages[$category] = array();
                $messages[$category][] = $message;
                $this->cookieBuilder->set('messages', json_encode($messages));
            break;
            default:
                throw new DomainException(sprintf('Unknown support \'%s\' for message: %s',$support,$message));
            break;
        }
    }
    
    /**
    * gets all of messages and deletes them from support
    *
    * @param string $support: support messages are saved to, if not specified messages on all of implementd supports are retrieved
    * @throws DomainException if $support is not handled
    * @return array of messages indexed by categories
    */
    function get($support = false)
    {
        switch($support) {
            case 'inner':
                $messages = (array) $this->innerMessages;
            break;
            case 'cookies':
                $messages = (array) json_decode($this->cookieBuilder->get('messages'));
                $this->cookieBuilder->delete('messages');
            break;
            case false:
                $messages = array_merge_recursive($this->get('inner'), $this->get('cookies'));
            break;
            default:
                throw new DomainException(sprintf('Unknown required support \'%s\' while getting messages',$support));
            break;
        }
        return $messages;
    }
}