<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Convert existing division recruiting thread entries to full thread URLs.
     * Thread ID can fall off naturally.
     */
    public function up(): void
    {

        DB::statement(<<<'SQL'
UPDATE divisions d
JOIN (
  SELECT
    d.id,
    JSON_ARRAYAGG(
      JSON_SET(
        jt.item,
        '$.thread_url',
        CONCAT(
          'https://www.clanaod.net/forums/showthread.php?t=',
          JSON_UNQUOTE(JSON_EXTRACT(jt.item, '$.thread_id'))
        )
      ) ORDER BY jt.idx
    ) AS new_threads
  FROM divisions d
  JOIN JSON_TABLE(
    d.settings,
    '$.recruiting_threads[*]'
    COLUMNS (
      idx  FOR ORDINALITY,
      item JSON PATH '$'
    )
  ) AS jt
  WHERE JSON_CONTAINS_PATH(d.settings, 'one', '$.recruiting_threads')
  GROUP BY d.id
) x ON x.id = d.id
SET d.settings = JSON_SET(d.settings, '$.recruiting_threads', x.new_threads);
SQL);

    }
};
