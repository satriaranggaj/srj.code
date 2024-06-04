@extends('layouts.main')
@section('container')
<section id="project-page">
    <div class="container">
        <div class="title">
            <h1>
                <center>
                    FEATURED PROJECT
                </center>
            </h1>
        </div>
        <div class="content">
            @foreach ($projects as $project )
                    <div class="project-card">
                        <div class="picture">
                            <iframe src="{{ $project->link }}" frameborder="0"></iframe>
                        </div>
                        <div class="text">
                            <div class="title">
                                <h5>{{ $project->title }}</h5>
                            </div>
                            <div class="button-more">
                                <a href="{{ $project->link }}"  target="_blank">View Project</a>
                            </div>
                        </div>
                    </div>
            @endforeach
        </div>
    </div>
</section>
@endsection