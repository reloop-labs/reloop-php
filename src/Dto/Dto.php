<?php

namespace Reloop\Dto;

use Reloop\Dto\Request\CreateChannelParams;
use Reloop\Dto\Request\CreateContactParams;
use Reloop\Dto\Request\CreateGroupParams;
use Reloop\Dto\Request\CreatePropertyParams;
use Reloop\Dto\Request\ListChannelsParams;
use Reloop\Dto\Request\ListContactsParams;
use Reloop\Dto\Request\ListGroupsParams;
use Reloop\Dto\Request\ListPropertiesParams;
use Reloop\Dto\Request\UpdateChannelParams;
use Reloop\Dto\Request\UpdateContactChannelParams;
use Reloop\Dto\Request\UpdateContactParams;
use Reloop\Dto\Request\UpdateGroupParams;
use Reloop\Dto\Request\UpdatePropertyParams;
use Reloop\Dto\Request\AddContactToChannelParams;
use Reloop\Dto\Request\AddContactToGroupParams;
use Reloop\Dto\Request\RemoveContactFromGroupParams;

final class Dto
{
    public static function body(object|array $params): array
    {
        if (is_array($params)) {
            return $params;
        }

        return $params->toArray();
    }

    public static function query(object|array $params): array
    {
        if (is_array($params)) {
            return $params;
        }

        return $params->toArray();
    }
}
