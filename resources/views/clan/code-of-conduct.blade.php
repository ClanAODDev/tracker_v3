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
                    <p>The Angels of Death Code of Conduct is the face of our clan. It is not only a guideline for member
                        conduct — it is a statement about what kind of gamers we are, and who we're looking for. AOD is
                        an honor clan first. This includes <strong>zero tolerance for hacking or any other malicious game
                            modding.</strong></p>
                    <p>All members agree to the Code of Conduct upon being accepted into the clan, and are expected to
                        uphold it at all times. Our code ensures that members conduct themselves respectfully and
                        preserve the respected image of the clan.</p>
                </div>

                <div class="coc-grid">
                    <div class="coc-card animate-fade-in-up" style="animation-delay: .04s;">
                        <span class="coc-card-number">1</span>
                        <p class="coc-card-text">Strive to conduct ourselves in an appropriate manner</p>
                    </div>
                    <div class="coc-card animate-fade-in-up" style="animation-delay: .08s;">
                        <span class="coc-card-number">2</span>
                        <p class="coc-card-text">Offer help to anyone who seeks it, if it is possible</p>
                    </div>
                    <div class="coc-card animate-fade-in-up" style="animation-delay: .12s;">
                        <span class="coc-card-number">3</span>
                        <p class="coc-card-text">Project a positive image of yourself and the AOD clan to others</p>
                    </div>
                    <div class="coc-card animate-fade-in-up" style="animation-delay: .16s;">
                        <span class="coc-card-number">4</span>
                        <p class="coc-card-text">Put the needs of the clan first, above personal goals</p>
                    </div>
                    <div class="coc-card animate-fade-in-up" style="animation-delay: .20s;">
                        <span class="coc-card-number">5</span>
                        <p class="coc-card-text">Support members of AOD</p>
                    </div>
                    <div class="coc-card animate-fade-in-up" style="animation-delay: .24s;">
                        <span class="coc-card-number">6</span>
                        <p class="coc-card-text">Attempt to resolve personal differences directly with the concerned individual(s)</p>
                    </div>
                    <div class="coc-card animate-fade-in-up" style="animation-delay: .28s;">
                        <span class="coc-card-number">7</span>
                        <p class="coc-card-text">Remind other AOD members of expected conduct in a tactful, non-threatening way, and if possible, in private</p>
                    </div>
                    <div class="coc-card animate-fade-in-up" style="animation-delay: .32s;">
                        <span class="coc-card-number">8</span>
                        <p class="coc-card-text">Promote fellowship within the game community</p>
                    </div>
                    <div class="coc-card animate-fade-in-up" style="animation-delay: .36s;">
                        <span class="coc-card-number">9</span>
                        <p class="coc-card-text">Do not condemn or humiliate other clan members</p>
                    </div>
                    <div class="coc-card animate-fade-in-up" style="animation-delay: .40s;">
                        <span class="coc-card-number">10</span>
                        <p class="coc-card-text">Win and lose games honorably — show sportsmanship</p>
                    </div>
                    <div class="coc-card animate-fade-in-up" style="animation-delay: .44s;">
                        <span class="coc-card-number">11</span>
                        <p class="coc-card-text">Maintain AOD loyalty</p>
                    </div>
                    <div class="coc-card animate-fade-in-up" style="animation-delay: .48s;">
                        <span class="coc-card-number">12</span>
                        <p class="coc-card-text">No comments about politics, religion, skin color, sexual preferences, or sexual conduct. We are here to play games.</p>
                    </div>
                </div>

                <div class="coc-commitment">
                    <i class="fa fa-exclamation-circle"></i>
                    <p><strong>Violation of the Code of Conduct will result in removal from AOD.</strong> By joining the
                        clan, every member has agreed to uphold these standards at all times — in game, on the forums,
                        and in any AOD-affiliated space.</p>
                </div>

            </div>
        </div>
    </div>

@endsection
