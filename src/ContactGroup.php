<?php

namespace Reloop;

/**
 * @property string $object
 * @property string $id
 * @property string $name
 * @property string $created_at
 * @property string $updated_at
 * @property string|null $event
 * @property bool|null $success
 * @property array{id: string, name: string}|null $group
 * @property list<Contact> $contacts
 * @property int|null $total
 * @property int|null $page
 * @property int|null $limit
 */
class ContactGroup extends Resource
{
}
