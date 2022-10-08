<?php

namespace App\Service;
 
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class VersioningService {
    
    public function __construct(
        ParameterBagInterface $params,
        private RequestStack $requestStack,
    ){
        $this->defaultVersion = $params->get('default_api_version');
    }
    
    public function getVersion(): string
    {  
        $version = $this->defaultVersion;
 
        $request = $this->requestStack->getCurrentRequest();
        $accept = $request->headers->get('Accept');
        // Retrieving the version number from the accept string
        $headers = explode(';', $accept);
       
        foreach ($headers as $header) {
            if (false !== strpos($header, 'version')) {
                $version = explode('=', $header);
                $version = $version[1];
                break;
            }
        }
        return $version;
    }
    
}
