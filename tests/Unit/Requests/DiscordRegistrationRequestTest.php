<?php

namespace Tests\Unit\Requests;

use App\Http\Requests\Auth\DiscordRegistrationRequest;
use Illuminate\Contracts\Validation\Validator as ValidatorContract;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DiscordRegistrationRequestTest extends TestCase
{
    private function validateUsername(string $username): ValidatorContract
    {
        $request = DiscordRegistrationRequest::create('/register', 'POST', [
            'username'    => $username,
            'division_id' => null,
        ]);

        return Validator::make($request->all(), $request->rules());
    }

    #[Test]
    public function username_starting_with_tr_is_allowed()
    {
        $validator = $this->validateUsername('Travis');

        $this->assertFalse($validator->errors()->has('username'));
    }

    #[Test]
    public function username_starting_with_other_rank_abbreviation_is_rejected()
    {
        $validator = $this->validateUsername('Sgtfoobar');

        $this->assertTrue($validator->errors()->has('username'));
        $this->assertStringContainsString('rank abbreviation', $validator->errors()->first('username'));
    }

    #[Test]
    public function username_starting_with_aod_prefix_is_still_rejected()
    {
        $validator = $this->validateUsername('AOD_something');

        $this->assertTrue($validator->errors()->has('username'));
        $this->assertStringContainsString('AOD_', $validator->errors()->first('username'));
    }
}
