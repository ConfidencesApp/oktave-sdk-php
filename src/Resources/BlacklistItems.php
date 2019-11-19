<?php

namespace Oktave\Resources;

use Oktave\Resource as Resource;

class BlacklistItems extends Resource
{
    /**
     * {@inheritDoc}
     */
    public $resourceCollection = 'blacklist-items';

    /**
     * {@inheritDoc}
     */
    public $resource = 'blacklist-item';
}
