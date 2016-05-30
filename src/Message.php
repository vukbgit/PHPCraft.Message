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
    private $cookie;
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
    * @param PHPCraft\Cookie\CookieInterface $cookie
    */
    public function setCookie(\PHPCraft\Cookie\CookieInterface $cookie)
    {
        $this->cookie =& $cookie;
    }
    
    /**
    * Saves a message
    *
    * @param string $support: support to save message to, so far only 'cookies'
    * @param string $category: used to index message; for example Bootstrap contextual background helper classes (http://getbootstrap.com/css/#helper-classes-backgrounds) may be used for template benefit 
    * @throws Exception if $support is 'cookies' and $this->cookie has not been set
    * @throws DomainException if $support is not handled
    * @return mixed, depending on support:
    *                   cookies: Psr\Http\Message\ResponseInterface implementation object modified by cookie addition
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
                if(!$this->cookie) throw new \Exception('cookie must be set');
                $messages = (array) json_decode($this->cookie->get('messages'));
                if(!$messages) $messages = array();
                if(!isset($messages[$category])) $messages[$category] = array();
                $messages[$category][] = $message;
                return $this->cookie->set('messages', json_encode($messages));
            break;
            default:
                throw new DomainException(sprintf('Unknown support \'%s\' for message: %s',$support,$message));
            break;
        }
    }
    
    /**
    * gets all of messages
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
                $messages = (array) json_decode($this->cookie->get('messages'));
                $this->cookie->delete('messages');
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
    
    /**
    * clears all of messages from support
    *
    * @param string $support: support messages are saved to, if not specified messages on all of implementd supports are retrieved
    * @throws DomainException if $support is not handled
    * @return mixed, depending on support:
    *                   cookies: Psr\Http\Message\ResponseInterface implementation object modified by cookie addition
    */
    function clear($support = false)
    {
        switch($support) {
            case 'inner':
            break;
            case 'cookies':
                return $this->cookie->delete('messages');
            break;
            default:
                throw new DomainException(sprintf('Unknown required support \'%s\' while clearing messages',$support));
            break;
        }
    }
}