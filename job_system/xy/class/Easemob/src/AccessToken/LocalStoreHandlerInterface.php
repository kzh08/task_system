<?php

namespace Easemob\AccessToken;

interface LocalStoreHandlerInterface
{
    /*
     * get access token stored in local
     */
    public function get();

    /*
     * update local stored access token
     */
    public function update(AccessToken $accessToken);
}