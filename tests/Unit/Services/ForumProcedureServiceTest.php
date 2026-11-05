<?php

namespace Tests\Unit\Services;

use App\Services\ForumProcedureService;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ForumProcedureServiceTest extends TestCase
{
    public static function outOfRangeIds(): array
    {
        return [
            'zero'       => [0],
            'negative'   => [-5],
            'discord id' => [395448095015567360],
            'int max+1'  => [ForumProcedureService::MAX_USER_ID + 1],
        ];
    }

    #[Test]
    #[DataProvider('outOfRangeIds')]
    public function it_skips_the_forum_for_ids_outside_the_int_range(int $userId): void
    {
        DB::shouldReceive('connection')->never();

        $service = new ForumProcedureService;

        $this->assertNull($service->getUser($userId));
        $this->assertNull($service->setUserRank($userId, 'Pvt'));
        $this->assertNull($service->setUserDivision($userId, 'Test'));
        $this->assertNull($service->setDiscordInfo($userId, '1', 'tag'));
    }

    #[Test]
    public function it_accepts_ids_inside_the_int_range(): void
    {
        $this->assertTrue(ForumProcedureService::isValidUserId(1));
        $this->assertTrue(ForumProcedureService::isValidUserId(ForumProcedureService::MAX_USER_ID));
    }
}
