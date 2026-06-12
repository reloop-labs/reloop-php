<?php

namespace Reloop\Dto\Enum;

enum SubscriptionStatus: string
{
    case OptIn = 'opt_in';
    case OptOut = 'opt_out';
}
