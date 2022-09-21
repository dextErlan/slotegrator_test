<?php

namespace Tests\Feature\Service;

use App\Entity\User;
use App\Exception\LimitForPrizesEndException;
use App\Service\PrizeService;
use Tests\TestCase;

class PrizeServiceTest extends TestCase
{
    private User $user;
    private PrizeService $prizeService;

    protected function setUp(): void
    {
        parent::setUp();

        $em = $this->getEntityManager();
        $this->user = $em->getRepository(User::class)->findOneBy(['email' => 'first@user.test']);
        $this->prizeService = new PrizeService($this->user, $em);
    }

    public function testGiveawayFailedWhenLimitEnd()
    {
        $limitForGifts = [0, -1];

        foreach ($limitForGifts as $limit) {
            $this->expectException(LimitForPrizesEndException::class);

            list($giftCount, $giftDescription) = $this->prizeService->giveaway($limit);
        }
    }

    public function testGiveawayPrize()
    {
        $limitForGifts = 5;

        list($giftCount, $giftDescription) = $this->prizeService->giveaway($limitForGifts);

        $this->assertEquals(1, $giftCount);
        $this->assertNotEmpty($giftDescription);
    }
}