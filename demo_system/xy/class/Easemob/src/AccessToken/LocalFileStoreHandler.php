<?php

namespace Easemob\AccessToken;

class LocalFileStoreHandler implements LocalStoreHandlerInterface
{
    private $path;

    public function __construct($path = '')
    {
        $this->path = $path ? : __DIR__ . DIRECTORY_SEPARATOR . 'easemob_access_token.txt';
    }

    /**
     * get access token object stored in local file
     *
     * @author Sam Lu
     * @return null | AccessToken
     */
    public function get()
    {
        $accessToken = @file_get_contents($this->path);
        if (empty($accessToken)) {
            return new AccessToken('', '');
        }

        return unserialize($accessToken);
    }

    /**
     * update access token stored in local file
     *
     * @author Sam Lu
     *
     * @param  AccessToken $accessToken
     *
     * @return mixed
     */
    public function update(AccessToken $accessToken)
    {
        return file_put_contents($this->path, serialize($accessToken));
    }
}