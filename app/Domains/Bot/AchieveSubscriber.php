<?php
/**
 * This file is part of GitterBot package.
 *
 * @author Serafim <nesk@xakep.ru>
 * @date 11.10.2015 8:30
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Domains\Bot;

use Domains\Achieve;
use Domains\Bot\Achievements\DocsAchieve;
use Domains\Bot\Achievements\Karma100Achieve;
use Domains\Bot\Achievements\Karma10Achieve;
use Domains\Bot\Achievements\Karma500Achieve;
use Domains\Bot\Achievements\Karma50Achieve;
use Domains\Bot\Achievements\Thanks100Achieve;
use Domains\Bot\Achievements\Thanks10Karma0Achieve;
use Domains\Bot\Achievements\Thanks20Achieve;
use Domains\Bot\Achievements\Thanks50Achieve;
use Domains\Room;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Support\Collection;
use Interfaces\Gitter\Subscriber\SubscriberInterface;

/**
 * Class AchieveSubscriber
 */
class AchieveSubscriber implements
    SubscriberInterface,
    Arrayable,
    Jsonable
{
    /**
     * @var array
     */
    protected $achievements = [
        Karma10Achieve::class,
        Karma50Achieve::class,
        Karma100Achieve::class,
        Karma500Achieve::class,
        Thanks20Achieve::class,
        Thanks50Achieve::class,
        Thanks100Achieve::class,
        Thanks10Karma0Achieve::class,
        DocsAchieve::class,
    ];

    /**
     * @var array
     */
    protected $instances = [];

    /**
     * AchieveSubscriber constructor.
     */
    public function __construct()
    {
        foreach ($this->achievements as $achieve) {
            $this->instances[] = ($instance = new $achieve);
            $instance->handle();
        }
    }

    /**
     * Subscribe achievements
     */
    public function handle()
    {
        Achieve::created(function (Achieve $achieve) {
            $room = \App::make(Room::class);

            $room->write(
                \Lang::get('achieve.receiving', [
                    'user'        => $achieve->user->login,
                    'title'       => $achieve->title,
                    'description' => $achieve->description,
                    'image'       => $achieve->image,
                ])
            );
        });
    }

    /**
     * @param int $options
     * @return string
     */
    public function toJson($options = 0): string
    {
        return json_encode($this->toArray(), $options);
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return $this
            ->toCollection()
            ->toArray();
    }

    /**
     * @return Collection
     */
    public function toCollection(): Collection
    {
        return new Collection($this->getAchievementInstances());
    }

    /**
     * @return array
     */
    public function getAchievementInstances(): array
    {
        return $this->instances;
    }
}
