<?php
/**
 * Created by PhpStorm.
 * User: sohrab
 * Date: 2018-08-25
 * Time: 17:32
 */

namespace App\Adapter;

use League\Flysystem\Sftp\SftpAdapter;

/**
 * Class AlaaSftpAdapter
 *
 * @package App\Adapter
 */
class AlaaSftpAdapter extends SftpAdapter
{
    protected $orgRoot;
    
    /**
     * @var string|null
     */
    protected $prefix;
    
    protected $dProtocol;
    
    protected $dHost;
    
    /**
     * @var array
     */
    protected $newConfigurableArray = [
        'prefix',
        'dProtocol',
        'dHost',
    ];
    
    /**
     * Constructor.
     *
     * @param  array  $config
     */
    public function __construct(array $config)
    {
        $this->configurable = array_merge($this->configurable, $this->newConfigurableArray);
        
        parent::__construct($config);
        
        $this->setOrgRoot(parent::getRoot());
        $this->setRoot(parent::getRoot().ltrim($this->getPrefix(), $this->separator));
    }
    
    /**
     * @return null|string
     */
    protected function getPrefix()
    {
        return $this->prefix;
    }
    
    /**
     *
     *
     * @param  string  $prefix
     *
     * @return $this
     */
    protected function setPrefix($prefix)
    {
        $this->prefix = rtrim($prefix, '\\/').$this->separator;
        
        return $this;
    }
    
    /**
     * @param $fileName
     *
     * @return string
     */
    public function getUrl($fileName)
    {
        $fileName = ltrim($fileName, $this->separator);
        
        /*
                $connection = $this->getConnection();
                if($connection instanceof \phpseclib\Net\SFTP)
                    dd($connection->stat($fileName));
                $info = $connection->stat($path);
                dd($this->getMetadata($fileName));
        */
        $prefixLink = str_replace($this->getOrgRoot(), $this->getDProtocol().$this->getDHost().'/',
            $this->getRoot());
    
        return $prefixLink.$fileName;
    }
    
    /**
     * @return mixed
     */
    protected function getOrgRoot()
    {
        return $this->orgRoot;
    }
    
    /**
     * @param  mixed  $orgRoot
     */
    protected function setOrgRoot($orgRoot): void
    {
        $this->orgRoot = $orgRoot;
    }
    
    /**
     * @return mixed
     */
    protected function getDProtocol()
    {
        return $this->dProtocol;
    }
    
    /**
     * @param  mixed  $dProtocol
     *
     * @return AlaaSftpAdapter
     */
    protected function setDProtocol($dProtocol)
    {
        $this->dProtocol = $dProtocol;
        
        return $this;
    }
    
    /**
     * @return mixed
     */
    protected function getDHost()
    {
        return $this->dHost;
    }
    
    /**
     * @param  mixed  $dHost
     *
     * @return AlaaSftpAdapter
     */
    protected function setDHost($dHost)
    {
        $this->dHost = $dHost;
        
        return $this;
    }
}