<?php

namespace Tests\Feature\Service;

use App\Entity\User;
use App\Service\PointService;
use Tests\TestCase;

class PointServiceTest extends TestCase
{
    private User $user;
    private PointService $pointService;

    protected function setUp(): void
    {
        parent::setUp();

        $em = $this->getEntityManager();
        $this->user = $em->getRepository(User::class)->findOneBy(['email' => 'first@user.test']);
        $this->pointService = new PointService($this->user, $em);
    }

    public function testGiveawayPoint()
    {
        $limitForGifts = 5;
        $userPoints = empty($this->user->getUserPoint()) ? 0 : $this->user->getUserPoint()->getPoint();

        list($giftCount, $giftDescription) = $this->pointService->giveaway($limitForGifts);

        $this->assertGreaterThan(1, $giftCount);
        $this->assertNotEmpty($giftDescription);
        $this->assertEquals($userPoints + $giftCount, $this->user->getUserPoint()->getPoint());
    }
}