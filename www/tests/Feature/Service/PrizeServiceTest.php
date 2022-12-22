<?php

namespace Tests\Feature\Service;

use App\Entity\Prize;
use App\Entity\User;
use App\Exception\LimitForPrizesEndException;
use App\Service\PrizeService;
use Tests\TestCase;

class PrizeServiceTest extends TestCase
{
    private User $user;
    private PrizeService $prizeService;

    /**
     * @throws \Exception
     */
    protected function setUp(): void
    {
        parent::setUp();

        $em = $this->getEntityManager();
        $user = $em->getRepository(User::class)->findOneBy(['email' => 'first@user.test']);

        $prize = $em->getRepository(Prize::class)->findOneBy(['name' => 'Телевизор']);

        if (!$user || !$prize) {
            throw new \Exception('User first@user.test not found for test!');
        }

        $prize->setNumber(3);
        $em->persist($prize);
        $em->flush();

        $this->user = $user;
        $this->prizeService = new PrizeService($this->user, $em);
    }

    public function testGiveawayFailedWhenLimitEnd(): void
    {
        $limitForGifts = [0, -1];

        foreach ($limitForGifts as $limit) {
            $this->expectException(LimitForPrizesEndException::class);

            list($giftCount, $giftDescription) = $this->prizeService->giveaway($limit);
        }
    }

    public function testGiveawayPrize(): void
    {
        $limitForGifts = 5;

        list($giftCount, $giftDescription) = $this->prizeService->giveaway($limitForGifts);

        $this->assertEquals(1, $giftCount);
        $this->assertNotEmpty($giftDescription);
    }
}