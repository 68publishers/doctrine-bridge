<?php

declare(strict_types=1);

namespace SixtyEightPublishers\DoctrineBridge\Tests\Fixtures\Entity;

use Doctrine\ORM\Mapping as Orm;

#[Orm\Entity]
class EntityWithAttributesMapping implements EntityInterface
{
	#[Orm\Id]
	#[Orm\Column(type: 'integer')]
	#[Orm\GeneratedValue]
	private int|null $id = null;
}
