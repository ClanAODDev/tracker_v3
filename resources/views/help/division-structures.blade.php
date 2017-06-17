@extends('application.base')

@section('content')

    @component ('application.components.view-heading')
        @slot ('currentPage')
            Documentation
        @endslot
        @slot ('icon')
            <i class="pe page-header-icon pe-7s-help2"></i>
        @endslot
        @slot ('heading')
            AOD Tracker
        @endslot
        @slot ('subheading')
            Division Structures
        @endslot
    @endcomponent

    <div class="container-fluid">
        <p>Divisions can customize their division structures with ease by making use of Twig, a templating engine, in conjunction with normal bb-code for styling. Read the following documentation on how to get started using Twig and what data is available for divisions to use.</p>

        <h3 class="m-t-xl">Basic Syntax</h3>
        <p>Twig makes use of three types of syntax: <code>@{{ output statements }}</code>,
            <code>{% control structures %}</code>, and
            <code>{# comments #}</code>. Output statements are used to echo strings, like name, or rank abbreviation, or position name. Control structures allow you to iterate over items, or operate on conditionals. An example of this would be:
        </p>

        <pre><code class="language-twig">@include('help.snippets.syntax')</code></pre>

        <p>Control structures typically have a beginning element and an ending element. <code>for</code> and
            <code>if</code> are the primary types of control structures.</p>

        <h3 class="m-t-xl">Loops and nesting</h3>
        <p>In order to properly build a division structure with tracker data, nested loops are required. A loop is a control structure that iterates over a series of items. Nesting loops entails using a control structure inside of another control structure. In order to access a division's squads, you must first iterate over the platoons. Consider the following example:</p>

        <pre><code class="language-twig">@include('help.snippets.loops')</code></pre>

        <p class="m-t-lg m-b-lg">This becomes more interesting as you wrap the twig logic with bb-code. In this example, I may want to output the platoon name and the squad name, respectively.</p>

        <pre><code class="language-twig">@include('help.snippets.nested')</code></pre>

        <p class="m-t-lg m-b-lg">Ultimately, we will want to output the squad members of each squad. As you might have guessed, this requires one additional nested control structure. Note the added bb-code for context.</p>
        <pre><code class="language-twig">@include('help.snippets.list')</code></pre>

        <h3 class="m-t-xl">Available Properties</h3>
        <h4 class="m-t-xl">Division</h4>
        @include('help.partials.division-properties')

        <h4 class="m-t-xl">Platoon</h4>
        @include('help.partials.platoon-properties')

        <h4 class="m-t-xl">Squad</h4>
        @include('help.partials.squad-properties')

        <h4>Member</h4>
        @include('help.partials.member-properties')
    </div>
@stop
