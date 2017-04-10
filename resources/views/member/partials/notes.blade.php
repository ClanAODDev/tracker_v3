@php
    $notes = collect([
        [
            'type' => 'panel-c-danger',
            'tags' => [
                12, 14
            ],
            'body' => "<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Accusantium alias doloremque porro sit veniam? Accusantium blanditiis dignissimos distinctio, doloremque eveniet iste modi, non numquam quibusdam reiciendis sit vel veritatis voluptatibus.</p>
            <p>Asperiores at dicta dolores eum ex fugit iste iusto laboriosam laudantium maiores numquam quibusdam, quos rerum saepe sit soluta velit? Ab at distinctio exercitationem fugiat fugit inventore labore magni, nesciunt?</p>
            <p>Adipisci aperiam corporis debitis deleniti distinctio eaque, eum iusto nemo, saepe ut vitae voluptatibus. Aperiam at commodi ex veniam voluptatem! A culpa doloremque dolorum error facere labore provident similique. Dolorum!</p>",
            'author' => 31832
        ]
    ]);
@endphp

@foreach ($notes as $note)
    <div class="panel panel-filled {{ $note['type'] }} collapsed">
        <div class="panel-heading panel-toggle">

            <span class="label label-danger text-uppercase">COC</span>
            <span class="label label-danger text-uppercase">TEAMSPEAK</span>

            <span class="text-muted pull-right">
            Aug 15th, 2017
        </span>
        </div>

        <div class="panel-body">
            {!! $note['body'] !!}
        </div>

        <div class="panel-footer">
            <span class="author text-muted">{{ $note['author'] }}</span>
            <button class="btn btn-default btn-xs pull-right"><i class="fa fa-comment"></i> View Discussion</button>
        </div>
    </div>
@endforeach