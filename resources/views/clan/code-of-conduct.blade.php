@extends('application.base-tracker')

@section('content')

    @component('application.components.view-heading')
        @slot('currentPage') Clan Information @endslot
        @slot('icon') <i class="pe page-header-icon pe-7s-shield"></i> @endslot
        @slot('heading') Angels of Death @endslot
        @slot('subheading') Code of Conduct @endslot
    @endcomponent

    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">

                <div class="coc-preamble">
                    <p>The Angels of Death Code of Conduct is the face of our clan. It is not only a guideline for member conduct — it is a statement about what kind of gamers we are, and who we're looking for. AOD is an honor clan first. This includes <strong>zero tolerance for hacking or any other malicious game modding.</strong></p>
                    <p>All members agree to the Code of Conduct upon being accepted into the clan, and are expected to uphold it at all times. Our code ensures that members conduct themselves respectfully and preserve the respected image of the clan.</p>
                </div>

                <ol class="coc-list">
                    <li class="coc-item animate-fade-in-up animate-stagger">
                        <span>Strive to conduct ourselves in an appropriate manner</span>
                    </li>
                    <li class="coc-item animate-fade-in-up animate-stagger">
                        <span>Offer help to anyone who seeks it, if it is possible</span>
                    </li>
                    <li class="coc-item animate-fade-in-up animate-stagger">
                        <span>Project a positive image of yourself and the AOD clan to others</span>
                    </li>
                    <li class="coc-item animate-fade-in-up animate-stagger">
                        <span>Put the needs of the clan first, above personal goals</span>
                    </li>
                    <li class="coc-item animate-fade-in-up animate-stagger">
                        <span>Support members of AOD</span>
                    </li>
                    <li class="coc-item animate-fade-in-up animate-stagger">
                        <span>Attempt to resolve personal differences directly with the concerned individual(s)</span>
                    </li>
                    <li class="coc-item animate-fade-in-up animate-stagger">
                        <span>Remind other AOD members of expected conduct in a tactful, non-threatening way, and if possible, in private</span>
                    </li>
                    <li class="coc-item animate-fade-in-up animate-stagger">
                        <span>Promote fellowship within the game community</span>
                    </li>
                    <li class="coc-item animate-fade-in-up animate-stagger">
                        <span>Do not condemn or humiliate other clan members</span>
                    </li>
                    <li class="coc-item animate-fade-in-up animate-stagger">
                        <span>Win and lose games honorably — show sportsmanship</span>
                    </li>
                    <li class="coc-item animate-fade-in-up animate-stagger">
                        <span>Maintain AOD loyalty</span>
                    </li>
                    <li class="coc-item animate-fade-in-up animate-stagger">
                        <span>No comments about politics, religion, skin color, sexual preferences, or sexual conduct. We are here to play games.</span>
                    </li>
                </ol>

                <div class="coc-commitment">
                    <i class="fa fa-exclamation-circle"></i>
                    <p><strong>Violation of the Code of Conduct will result in removal from AOD.</strong> By joining the clan, every member has agreed to uphold these standards at all times — in game, on the forums, and in any AOD-affiliated space.</p>
                </div>

            </div>
        </div>
    </div>

@endsection
