<?php

require_once 'Zend/Rest/Client.php';

/**
 * Phing class for sending deployment messages to newrelic service
 */
class PhingNewRelic extends Task
{
    /**
     * Authentication api key from newrelic
     * @var string
     */
    private $_apiKey;
    
    /**
     * newrelic app id
     * @var string
     */
    private $_appId;
    
    /**
     * newrelic deploy user
     * @var string
     */
    private $_deployUser;
    
    
    
    /**
     * Set authenticated api key
     */
    public function setAPIKey($apiKey) {
    
        if(empty($apiKey)) {
            throw new BuildException("Make sure you set your authentication api key", $this->location);
        }
        
        $this->_apiKey = $apiKey;
    }
    
    /**
     * Set newrelic app id
     */
    public function setAppId($appId) {
    
    	if(empty($appId)) {
    		throw new BuildException("Make sure you set your newrelic app id", $this->location);
    	}
    
    	$this->_appId = $appId;
    }
    
    
    /**
     * description message to send
     */
    public function setMessage($message) {
    
        $this->_message = $message;
    }
    
    /**
     * deploy user name
     */
    public function setDeployUser($deployUser) {
    
    	$this->_deployUser = $deployUser;
    }
    
    public function main() {
    
        $config = array(
        	'deployment[application_id]' => $this->_appId,
        );
        
        if (!empty($this->_deployUser)) {
        	$config['deployment[user]'] = $this->_deployUser;
        }
        	
        if (!empty($this->_message)) {
           	$config['deployment[description]'] = $this->_message;
        }
        
        try {
        	$client = new Zend_Rest_Client;
        	$client->setUri('https://rpm.newrelic.com/deployments.xml');
        	$httpClient = $client->getHttpClient();
        	$httpClient->setHeaders('x-api-key', $this->_apiKey);
        	$client->setHttpClient($httpClient);
        
        	$response = $client->restPost('/deployments.xml', $config);
        	if ($response->getStatus()!=201) {
        		$this->log( "Failed notifying deployment message to newrelic - " . $response->getMessage(), Project::MSG_ERR);
        	}
        	$this->log( "Deployment message sent to new relic" );
        } catch (Exception $e) {
        	$this->log( "Failed notifying deployment message to newrelic", Project::MSG_ERR);
        
        }
	}

}
