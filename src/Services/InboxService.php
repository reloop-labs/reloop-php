<?php

namespace Reloop\Services;

use Reloop\ReloopClient;

class InboxService
{
    public InboxMailboxesService $mailboxes;
    public InboxMessagesService $messages;
    public InboxThreadsService $threads;

    public function __construct(ReloopClient $client)
    {
        $this->mailboxes = new InboxMailboxesService($client);
        $this->messages = new InboxMessagesService($client);
        $this->threads = new InboxThreadsService($client);
    }
}
