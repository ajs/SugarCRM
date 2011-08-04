<?php
/*********************************************************************************
 * SugarCRM Community Edition is a customer relationship management program developed by
 * SugarCRM, Inc. Copyright (C) 2004-2011 SugarCRM Inc.
 * 
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License version 3 as published by the
 * Free Software Foundation with the addition of the following permission added
 * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
 * IN WHICH THE COPYRIGHT IS OWNED BY SUGARCRM, SUGARCRM DISCLAIMS THE WARRANTY
 * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more
 * details.
 * 
 * You should have received a copy of the GNU Affero General Public License along with
 * this program; if not, see http://www.gnu.org/licenses or write to the Free
 * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
 * 02110-1301 USA.
 * 
 * You can contact SugarCRM, Inc. headquarters at 10050 North Wolfe Road,
 * SW2-130, Cupertino, CA 95014, USA. or at email address contact@sugarcrm.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * SugarCRM" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by SugarCRM".
 ********************************************************************************/


    require_once 'Zend/Oauth/Consumer.php';
    // use ZF oauth
    class SugarOAuth extends Zend_Oauth_Consumer
    {
        protected $_last = '';
        protected $_oauth_config = array();

        public function __construct($consumer_key , $consumer_secret, $params = null)
        {
            $this->_oauth_config = array(
                'consumerKey' => $consumer_key,
                'consumerSecret' => $consumer_secret,
            );
            if(!empty($params)) {
                $this->_oauth_config = array_merge($this->_oauth_config, $params);
            }
            parent::__construct($this->_oauth_config);
        }

        public function enableDebug()
        {
            return $this;
        }

        public function setToken($token, $secret)
        {
            $this->token = array($token, $secret);
        }

        public function makeRequestToken()
        {
            $token = new Zend_Oauth_Token_Request();
            $token->setToken($this->token[0]);
            $token->setTokenSecret($this->token[1]);
            return $token;
        }

        public function makeAccessToken()
        {
            $token = new Zend_Oauth_Token_Access();
            $token->setToken($this->token[0]);
            $token->setTokenSecret($this->token[1]);
            return $token;
        }

        public function getRequestToken($url, $callback = null, $params = array())
        {
            if(!empty($callback)) {
                $this->setCallbackUrl($callback);
            }
            list($clean_url, $query) = explode('?', $url);
            if($query) {
                $url = $clean_url;
                parse_str($query, $query_params);
                $params = array_merge($params, $query_params);
            }
            $this->setRequestTokenUrl($url);
            try{
                $this->_last = $token = parent::getRequestToken($params);
                return array('oauth_token' => $token->getToken(), 'oauth_token_secret' => $token->getTokenSecret());
            }catch(Zend_Oauth_Exception $e){
                return array('oauth_token' => '', 'oauth_token_secret' => '');
            }
        }

        public function getAccessToken($url)
        {
            $this->setAccessTokenUrl($url);
            $this->_last = $token = parent::getAccessToken($_REQUEST, $this->makeRequestToken());
            return array('oauth_token' => $token->getToken(), 'oauth_token_secret' => $token->getTokenSecret());
        }

       public function fetch($url, $params = null, $method = 'GET', $headers = null)
       {
           $acc = $this->makeAccessToken();
           if ( strpos($url,'?') ) {
               list($clean_url, $query) = explode('?', $url);
               if($query) {
                   $url = $clean_url;
                   parse_str($query, $query_params);
                   $params = array_merge($params?$params:array(), $query_params);
               }
           }
            $client = $acc->getHttpClient($this->_oauth_config, $url);
            $client->setMethod($method);
            if(!empty($headers)) {
                $client->setHeaders($headers);
            }
            if(!empty($params)) {
                if($method == 'GET') {
                    $client->setParameterGet($params);
                } else {
                    $client->setParameterPost($params);
                }
            }
            $this->_last = $resp = $client->request();
            $this->_lastReq = $client->getLastRequest();
            return $resp->getBody();
       }

       public function getClient()
       {
            $acc = $this->makeAccessToken();
            return $acc->getHttpClient($this->_oauth_config);
       }

       public function getLastResponse()
       {
            return $this->_last;
       }

       public function getLastRequest()
       {
            return $this->_lastReq;
       }
    }
