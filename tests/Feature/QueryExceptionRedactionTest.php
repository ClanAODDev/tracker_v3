<?php

namespace Tests\Feature;

use App\Support\QueryBindingRedactor;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Log\Events\MessageLogged;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class QueryExceptionRedactionTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function reported_query_exceptions_hide_sensitive_values_but_keep_the_rest(): void
    {
        $logged = [];
        Event::listen(MessageLogged::class, function (MessageLogged $event) use (&$logged) {
            $logged[] = $event;
        });

        try {
            DB::table('users')->insert([
                'name'           => 'Guybrush',
                'email'          => 'guybrush@example.com',
                'missing_column' => 'x',
            ]);
            $this->fail('Expected the insert to fail.');
        } catch (QueryException $e) {
            report($e);
        }

        $this->assertCount(1, $logged);
        $this->assertStringNotContainsString('guybrush@example.com', $logged[0]->message);
        $this->assertStringNotContainsString('Guybrush', $logged[0]->message);
        $this->assertSame(
            ['Guybrush', QueryBindingRedactor::REDACTED, 'x'],
            $logged[0]->context['bindings'],
        );
        $this->assertStringContainsString('insert into `users`', $logged[0]->context['sql']);
    }
}
