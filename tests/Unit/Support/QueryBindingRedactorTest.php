<?php

namespace Tests\Unit\Support;

use App\Support\QueryBindingRedactor;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class QueryBindingRedactorTest extends TestCase
{
    private const REDACTED = QueryBindingRedactor::REDACTED;

    private function redactor(): QueryBindingRedactor
    {
        return new QueryBindingRedactor(['email', 'date_of_birth', 'session']);
    }

    #[Test]
    public function it_redacts_sensitive_columns_in_comparisons(): void
    {
        $bindings = $this->redactor()->redact(
            'update `users` set `email` = ?, `name` = ? where `id` = ?',
            ['guy@example.com', 'Guybrush', 5],
        );

        $this->assertSame([self::REDACTED, 'Guybrush', 5], $bindings);
    }

    #[Test]
    public function it_redacts_table_qualified_and_aliased_columns(): void
    {
        $bindings = $this->redactor()->redact(
            'select * from `users` as `u` where `u`.`email` = ? and `u`.`name` = ?',
            ['guy@example.com', 'Guybrush'],
        );

        $this->assertSame([self::REDACTED, 'Guybrush'], $bindings);
    }

    #[Test]
    public function it_maps_insert_values_to_their_columns_across_rows(): void
    {
        $bindings = $this->redactor()->redact(
            'insert into `users` (`name`, `email`, `date_of_birth`) values (?, ?, ?), (?, ?, ?)',
            ['Guybrush', 'guy@example.com', '1990-01-01', 'Elaine', 'elaine@example.com', '1991-02-02'],
        );

        $this->assertSame(
            ['Guybrush', self::REDACTED, self::REDACTED, 'Elaine', self::REDACTED, self::REDACTED],
            $bindings,
        );
    }

    #[Test]
    public function it_redacts_every_value_in_an_in_list(): void
    {
        $bindings = $this->redactor()->redact(
            'select * from `users` where `email` in (?, ?, ?) and `role` = ?',
            ['a@example.com', 'b@example.com', 'c@example.com', 3],
        );

        $this->assertSame([self::REDACTED, self::REDACTED, self::REDACTED, 3], $bindings);
    }

    #[Test]
    public function it_maps_between_bounds_and_function_wrapped_columns(): void
    {
        $bindings = $this->redactor()->redact(
            'select * from `users` where `date_of_birth` between ? and ? and date(`created_at`) = ?',
            ['1990-01-01', '1999-12-31', '2026-09-25'],
        );

        $this->assertSame([self::REDACTED, self::REDACTED, '2026-09-25'], $bindings);
    }

    #[Test]
    public function it_redacts_named_bindings_by_key(): void
    {
        $bindings = $this->redactor()->redact(
            'CALL check_session(:session, :user_id)',
            ['session' => 'abc123', 'user_id' => 42],
        );

        $this->assertSame(['session' => self::REDACTED, 'user_id' => 42], $bindings);
    }

    #[Test]
    public function it_redacts_placeholders_it_cannot_map_to_a_column(): void
    {
        $bindings = $this->redactor()->redact('select ?, ? from dual', ['anything', null]);

        $this->assertSame([self::REDACTED, null], $bindings);
    }

    #[Test]
    public function it_ignores_question_marks_inside_string_literals(): void
    {
        $bindings = $this->redactor()->redact(
            "select * from `users` where `name` = 'who?' and `email` = ? and `id` = ?",
            ['guy@example.com', 5],
        );

        $this->assertSame([self::REDACTED, 5], $bindings);
    }

    #[Test]
    public function it_redacts_encrypted_payloads_in_any_column(): void
    {
        $bindings = $this->redactor()->redact(
            'update `users` set `settings` = ? where `id` = ?',
            ['eyJpdiI6IkFCQyIsInZhbHVlIjoiWFlaIn0=', 5],
        );

        $this->assertSame([self::REDACTED, 5], $bindings);
    }
}
