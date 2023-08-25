@extends('layouts.main')

@section('container')
<section id="blog-page">
    <div class="container">
        <div class="title">
            <h1>
                <center>
                    BLOG
                </center>
            </h1>
        </div>
        <div class="content">
            @foreach ($posts as $post)
                <article>
                    <div class="blog-card">
                        <div class="thumbnail">
                            <img src="frontend/img/project/{{ $post->thumbnail }}" alt="thumbnail-blog">
                        </div>
                        <div class="teks">
                            <div class="title">
                                <h5>{{ $post->title }}</h5>
                            </div>
                            <div class="date">
                                <p>{{ $post->date }}</p>
                            </div>
                            <div class="desc">
                                <p>{{ $post->excerpt }}</p>
                            </div>
                            <center>
                                <div class="button-more">
                                    <a href="/{{ $post->slug }}">Read More</a>
                                </div>
                            </center>
                        </div> 
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>
@endsection