@extends('layouts.main')

@section('container')
<section id="blog-post">
    <div class="container">
        <div class="thumbnail">
            <img src="frontend/img/project/{{ $post->thumbnail }}" alt="thumbnail-blog">
        </div>
        <div class="teks">
            <div class="date">
                <p>{{ $post->category->name }} - {{ $post->date }}</p>
            </div>
            <div class="title">
                <h1>{{ $post->title }}</h1>
            </div>
            <div class="desc">
                {!! $post->body !!}
            </div>
        </div>
            <a href="/blog">Previous</a> 
    </div>    
</section>
@endsection
