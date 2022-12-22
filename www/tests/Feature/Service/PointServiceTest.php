<?php

namespace Tests\Feature\Service;

use App\Entity\User;
use App\Service\PointService;
use Tests\TestCase;

class PointServiceTest extends TestCase
{
    private User $user;
    private PointService $pointService;

    /**
     * @throws \Exception
     */
    protected function setUp(): void
    {
        parent::setUp();

        $em = $this->getEntityManager();
        $user = $em->getRepository(User::class)->findOneBy(['email' => 'first@user.test']);

        if (!$user) {
            throw new \Exception('User first@user.test not found for test!');
        }
        $this->user = $user;
        $this->pointService = new PointService($this->user, $em);
    }

    public function testGiveawayPoint(): void
    {
        $limitForGifts = 5;
        $userPoints = empty($this->user->getUserPoint()) ? 0 : $this->user->getUserPoint()->getPoint();

        list($giftCount, $giftDescription) = $this->pointService->giveaway($limitForGifts);

        $this->assertGreaterThan(1, $giftCount);
        $this->assertNotEmpty($giftDescription);
        $userPoint = $this->user->getUserPoint();
        $point = is_null($userPoint) ? 0 : $userPoint->getPoint();
        $this->assertEquals($userPoints + $giftCount, $point);
    }
}