@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="view-header">
                    <div class="pull-right text-right" style="line-height: 14px">
                        <small>App Pages<br>Basic<br> <span class="c-white">Support</span></small>
                    </div>
                    <div class="header-icon">
                        <i class="pe page-header-icon pe-7s-help2"></i>
                    </div>
                    <div class="header-title">
                        <h3>Support</h3>
                        <small>
                            Show user data in special designed profile page
                        </small>
                    </div>
                </div>
                <hr>
            </div>
        </div>

        <div class="row">

            <div class="col-md-12">

                <div class="panel panel-filled">

                    <div class="panel-body">

                        <h3>Support </h3>

                        <p>
                            Fill the form and find answer to your question or contact with us on support email.
                        </p>

                        <div class="form-group">
                            <input class="form-control" placeholder="What are you looking for ?">
                        </div>

                    </div>

                </div>

            </div>
        </div>
        <div class="row">

            <div class="col-md-4">

                <div class="panel-group">

                    <ul class="list-unstyled">
                        <li class="panel panel-filled support-question active">
                            <a href="#answer1" data-toggle="tab">
                                <div class="panel-body">
                                    <p class="font-bold c-white">Support question 1</p>
                                    <p>
                                        Various versions have evolved over the years, sometimes by accident, sometimes on purpose.
                                    </p>
                                </div>
                            </a>
                        </li>
                        <li class="panel panel-filled support-question">
                            <a href="#answer2" data-toggle="tab">
                                <div class="panel-body">
                                    <p class="font-bold c-white">Support question 2</p>
                                    <p>
                                        Have evolved over the years, sometimes by accident, sometimes.
                                    </p>
                                </div>
                            </a>
                        </li>
                        <li class="panel panel-filled support-question">
                            <a href="#answer3" data-toggle="tab">
                                <div class="panel-body">
                                    <p class="font-bold c-white">Support question 3</p>
                                    <p>
                                        Various have evolved over the years, sometimes by accident.
                                    </p>
                                </div>
                            </a>
                        </li>
                        <li class="panel panel-filled support-question">
                            <a href="#answer4" data-toggle="tab">
                                <div class="panel-body">
                                    <p class="font-bold c-white">Support question 4</p>
                                    <p>
                                        Versions have evolved over the years, sometimes by accident, sometimes on purpose
                                    </p>
                                </div>
                            </a>
                        </li>
                    </ul>

                </div>

            </div>
            <div class="col-md-8">

                <div class="panel">
                    <div class="panel-body">
                        <div class="tab-content">
                            <div id="answer1" class="tab-pane active">
                                <h3>
                                    Question 1
                                </h3>
                                <p>
                                    Many desktop publishing packages and web page editors now use Lorem Ipsum as their default model text, and a search for 'lorem ipsum' will uncover many web sites still in their infancy.
                                </p>
                                <ul>
                                    <li>Lorem ipsum dolor sit amet, comes from</li>
                                    <li>There are many variations of passages of Lorem Ipsum available</li>
                                    <li>All the Lorem Ipsum generators on the Internet</li>
                                </ul>
                                <p class="font-bold c-white">
                                    Contrary to popular belief, Lorem Ipsum is not
                                </p>
                                <p>
                                    McClintock, a Latin professor at Hampden-Sydney College in Virginia, looked up one of the more obscure Latin words, consectetur, from a Lorem Ipsum passage, and going through the cites of the word in classical literature, discovered the undoubtable source.
                                </p>
                                <p>
                                    It is a long established fact that a reader will be distracted by the readable content of a page when looking at its layout. The point of using Lorem Ipsum is that it has a more-or-less normal distribution of letters, as opposed to using 'Content here, content here', making it look like readable English.
                                </p>
                                <p>Many desktop publishing packages and web page editors now use Lorem Ipsum as their default model text, and a search for 'lorem ipsum' will uncover many web sites still in their infancy. Various versions have evolved over the years, sometimes by accident, sometimes on purpose (injected humour and the like).
                                </p>

                            </div>

                            <div id="answer2" class="tab-pane">
                                <h3>
                                    Question 2
                                </h3>
                                <p>
                                    Many desktop publishing packages and web page editors now use Lorem Ipsum as their default model text, and a search for 'lorem ipsum' will uncover many web sites still in their infancy.
                                </p>
                                <ul>
                                    <li>Lorem ipsum dolor sit amet, comes from</li>
                                    <li>There are many variations of passages of Lorem Ipsum available</li>
                                    <li>All the Lorem Ipsum generators on the Internet</li>
                                    <li>Lorem ipsum dolor sit amet, comes from</li>
                                    <li>All the Lorem Ipsum generators on the Internet</li>
                                    <li>There are many variations of passages of Lorem Ipsum</li>
                                    <li>All the Lorem Ipsum generators on the Internet</li>
                                </ul>
                                <p class="font-bold c-white">
                                    Contrary to popular belief, Lorem Ipsum is not
                                </p>
                                <p>
                                    McClintock, a Latin professor at Hampden-Sydney College in Virginia, looked up one of the more obscure Latin words, consectetur, from a Lorem Ipsum passage, and going through the cites of the word in classical literature, discovered the undoubtable source.
                                </p>
                                <p>
                                    McClintock, a Latin professor at Hampden-Sydney College in Virginia, looked up one of the more obscure Latin words, consectetur, from a Lorem Ipsum passage, and going through the cites of the word in classical literature, discovered the undoubtable source.
                                </p>

                            </div>
                            <div id="answer3" class="tab-pane">
                                <h3>
                                    Question 3
                                </h3>
                                <p>
                                    Many desktop publishing packages and web page editors now use Lorem Ipsum as their default model text, and a search for 'lorem ipsum' will uncover many web sites still in their infancy.
                                </p>
                                <ul>
                                    <li>Lorem ipsum dolor sit amet, comes from</li>
                                    <li>There are many variations of passages of Lorem Ipsum available</li>
                                    <li>All the Lorem Ipsum generators on the Internet</li>
                                </ul>
                                <p class="font-bold c-white">
                                    Contrary to popular belief, Lorem Ipsum is not
                                </p>
                                <p>
                                    McClintock, a Latin professor at Hampden-Sydney College in Virginia, looked up one of the more obscure Latin words, consectetur, from a Lorem Ipsum passage, and going through the cites of the word in classical literature, discovered the undoubtable source.
                                </p>
                                <p>
                                    It is a long established fact that a reader will be distracted by the readable content of a page when looking at its layout. The point of using Lorem Ipsum is that it has a more-or-less normal distribution of letters, as opposed to using 'Content here, content here', making it look like readable English.
                                </p>
                                <p>Many desktop publishing packages and web page editors now use Lorem Ipsum as their default model text, and a search for 'lorem ipsum' will uncover many web sites still in their infancy. Various versions have evolved over the years, sometimes by accident, sometimes on purpose (injected humour and the like).
                                </p>

                            </div>
                            <div id="answer4" class="tab-pane">
                                <h3>
                                    Question 4
                                </h3>
                                <p>
                                    Many desktop publishing packages and web page editors now use Lorem Ipsum as their default model text, and a search for 'lorem ipsum' will uncover many web sites still in their infancy.
                                </p>
                                <ul>
                                    <li>Lorem ipsum dolor sit amet, comes from</li>
                                    <li>There are many variations of passages of Lorem Ipsum available</li>
                                    <li>All the Lorem Ipsum generators on the Internet</li>
                                    <li>Lorem ipsum dolor sit amet, comes from</li>
                                    <li>All the Lorem Ipsum generators on the Internet</li>
                                    <li>There are many variations of passages of Lorem Ipsum</li>
                                    <li>All the Lorem Ipsum generators on the Internet</li>
                                </ul>
                                <p class="font-bold c-white">
                                    Contrary to popular belief, Lorem Ipsum is not
                                </p>
                                <p>
                                    McClintock, a Latin professor at Hampden-Sydney College in Virginia, looked up one of the more obscure Latin words, consectetur, from a Lorem Ipsum passage, and going through the cites of the word in classical literature, discovered the undoubtable source.
                                </p>
                                <p>
                                    McClintock, a Latin professor at Hampden-Sydney College in Virginia, looked up one of the more obscure Latin words, consectetur, from a Lorem Ipsum passage, and going through the cites of the word in classical literature, discovered the undoubtable source.
                                </p>

                            </div>
                        </div>




                    </div>

                </div>

            </div>
        </div>
    </div>

@stop
