@extends('application.base-tracker')

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
        <div class="m-b-xl">
            <h4>Contents</h4>
            <ul>
                <li><a class="smooth-scroll" href="#basic-syntax">Basic syntax</a></li>
                <li><a class="smooth-scroll" href="#loops">Loops and nesting</a></li>
                <li><a class="smooth-scroll" href="#iterator-loop">Iterator loop variable</a></li>
                <li><a class="smooth-scroll" href="#empty-cases">Handling empty properties and arrays</a></li>
                <li><a class="smooth-scroll" href="#replace">String replacement and filters</a></li>
                <li><a class="smooth-scroll" href="#properties">Available properties</a></li>
            </ul>
        </div>

        <p>Divisions can customize their division structures with ease by making use of Twig, a templating engine, in conjunction with normal bb-code for styling. Read the following documentation on how to get started using Twig and what data is available for divisions to use.</p>

        <h3 class="m-t-xl" id="basic-syntax">Basic Syntax</h3>
        <hr />
        <p>Twig makes use of three types of syntax: <code>@{{ output statements }}</code>,
            <code>{% control structures %}</code>, and
            <code>{# comments #}</code>. Output statements are used to echo strings, like name, or rank abbreviation, or position name. Control structures allow you to iterate over items, or operate on conditionals. An example of this would be:
        </p>

        <pre><code class="language-twig line-numbers">@include('help.snippets.syntax')</code></pre>

        <p>Control structures typically have a beginning element and an ending element. <code>for</code> and
            <code>if</code> are the primary types of control structures.</p>

        <h3 class="m-t-xl" id="loops">Loops and nesting</h3>
        <hr />
        <p>In order to properly build a division structure with tracker data, nested loops are required. A loop is a control structure that iterates over a series of items. Nesting loops entails using a control structure inside of another control structure. In order to access a division's squads, you must first iterate over the platoons. Consider the following example:</p>

        <pre><code class="language-twig line-numbers">@include('help.snippets.loops')</code></pre>

        <p class="m-t-lg m-b-lg">This becomes more interesting as you wrap the twig logic with bb-code. In this example, I may want to output the platoon name and the squad name, respectively.</p>

        <pre><code class="language-twig line-numbers">@include('help.snippets.nested')</code></pre>

        <p class="m-t-lg m-b-lg">Ultimately, we will want to output the squad members of each squad. As you might have guessed, this requires one additional nested control structure. Note the added bb-code for context.</p>
        <pre><code class="language-twig line-numbers">@include('help.snippets.list')</code></pre>

        <h3 class="m-t-xl" id="iterator-loop">Iterator loop variables</h3>
        <hr />
        <p>Loops have a
            <code>loop</code> variable that can be used to perform various conditional actions. For example, if you want to limit squads to 2 per row, you can leverage the loop variable and the modulus
            <code>is divisible by()</code> method:</p>

        <p>Note that the <code>index</code> property starts at
            <code>1</code> by default, reflecting the current iteration. If you want the index to start at 0, use
            <code>index0</code> instead.</p>
        <pre><code class="language-twig line-numbers">@include('help.snippets.iterator-1')</code></pre>

        <p class="m-t-lg m-b-lg">
            You can access other properties pertaining to the loop as well:
        </p>
        <pre><code class="language-twig line-numbers">@include('help.snippets.iterator-2')</code></pre>

        <h3 class="m-t-xl" id="empty-cases">Handling Empty Properties and Arrays</h3>
        <hr />
        <p>Often you will run into a situation where the array or property you're trying to print could possibly be empty. In these cases, you may wish to do nothing, or you might want to provide an alternative. Here's how you manage that:</p>
        <pre><code class="language-twig line-numbers">@include('help.snippets.empty-cases')</code></pre>

        <h3 class="m-t-xl" id="replace">String Replacement And Filters</h3>
        <hr />
        <p>Sometimes a string may not contain exactly what you need to output. For example, Overwatch tracks members by their battlenet name, or
            <code>MyName#9999</code>. But in order to link to the public profile, the
            <code>#</code> character must be replaced by a <code>-</code> character.</p>
        <p>To facilitate this, we can use a filter <code>replace()</code> method:</p>
        <p>Note: the hyphens in the twig tags are whitespace delimiters. Ex.
            <code>{%-</code> indicates that leading whitespace should be trimmed.
            <code>-%}</code> indicates that trailing whitespace should be trimmed. This allows us to omit line breaks in the resulting code.
        </p>
        <pre><code class="language-twig line-numbers">@include('help.snippets.replace')</code></pre>
        <p class="m-t-lg m-b-lg">Additional filters allow us to make other transformations</p>
        <pre><code class="language-twig line-numbers">@include('help.snippets.transform')</code></pre>

        <h3 class="m-t-xl" id="properties">Available Properties</h3>
        <hr />
        <h4>Division</h4>
        @include('help.partials.division-properties')

        <h4 class="m-t-xl">Platoon</h4>
        @include('help.partials.platoon-properties')

        <h4 class="m-t-xl">Squad</h4>
        @include('help.partials.squad-properties')

        <h4 class="m-t-xl">Member</h4>
        @include('help.partials.member-properties')
    </div>
@endsection
