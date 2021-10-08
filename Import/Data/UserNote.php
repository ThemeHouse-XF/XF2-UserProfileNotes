<?php

namespace ThemeHouse\UserNotes\Import\Data;

use XF\Import\Data\AbstractEmulatedData;
use XF\Import\Data\HasDeletionLogTrait;

class UserNote extends AbstractEmulatedData
{
    use HasDeletionLogTrait;

    protected $loggedIp;

    public function getImportType()
    {
        return 'th_usernotes_usernote';
    }

    protected function getEntityShortName()
    {
        return 'ThemeHouse\UserNotes:UserNote';
    }
}