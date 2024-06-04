@extends('layouts.main')
@section('container')
<section id="certificate">
    <div class="container">
        <div class="title">
            <h1>
                <center>
                    CERTIFICATES
                </center>
            </h1>
        </div>
        <div class="content">
            @foreach ($certificates as $certificate )
            <div class="list-certificate">
                <p>{{ $certificate->title }}</p>
                <a href="{{ $certificate->link }}" target="_blank">View Certificate</a>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endsection