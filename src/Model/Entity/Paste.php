<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Paste Entity
 *
 * @property int $id
 * @property int $user_id
 * @property string $slug
 * @property string|null $content
 *
 * @property \App\Model\Entity\User $user
 */
class Paste extends Entity
{
    protected $_accessible = [
        'user_id' => true,
        'text' => true,
    ];
}
