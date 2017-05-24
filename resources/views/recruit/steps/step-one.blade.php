
<div class="step-container step-one">

    <div class="alert alert-warning" style="cursor: pointer"
         onclick="window.Recruiting.scrollTo('.has-error:first-of-type')">
        There was a problem with your recruitment. Please review the issues marked in red.
        <i class="fa fa-arrow-circle-right"></i>
    </div>

    <h3>Getting Started</h3>

    <p>This is an introductory content paragraph for divisions to provide specific instructions regarding a recruitment process. This is a good place to talk about division-specific policies that may make or break interest, ie., division in-game requirements, must join platoon, must wear tags, etc.</p>
    <p>Additionally, recruiters should mention the clan-wide membership requirements:</p>
    <ul class="c-white">
        <li>Maintain minimum forum activity. Inactivity can result in removal from AOD</li>
        <li>Engage on TeamSpeak when playing a game AOD supports</li>
        <li>Strive to be a contributing member of your division</li>
        <li>Always be respectful of other clan members and leadership</li>
    </ul>

    <h3 class="m-t-xl"><i class="fa fa-address-card text-accent" aria-hidden="true"></i> Step 1: Member Data</h3>
    <hr />

    @include ('recruit.partials.trainer-bar')
    @include ('recruit.forms.member-information')
    @include ('recruit.forms.assignment')

    <button type="submit" class="btn btn-success pull-right">Continue <i class="fa fa-arrow-right"></i></button>
</div>