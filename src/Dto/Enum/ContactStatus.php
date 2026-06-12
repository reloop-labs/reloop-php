<?php

namespace Reloop\Dto\Enum;

enum ContactStatus: string
{
    case Subscribed = 'subscribed';
    case Unsubscribed = 'unsubscribed';
    case Blocked = 'blocked';
}
