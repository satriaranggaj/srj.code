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
                <button>
                    <span>
                        <a href="{{ $certificate->link }}" target="_blank">View Certificate</a>
                    </span>
                    <span class="icon">
                      <svg
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 448 512"
                        class="svg-icon"
                      >
                        <path
                          d="M438.6 278.6c12.5-12.5 12.5-32.8 0-45.3l-160-160c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3L338.8 224 32 224c-17.7 0-32 14.3-32 32s14.3 32 32 32l306.7 0L233.4 393.4c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0l160-160z"
                        ></path>
                      </svg>
                    </span>
                </button>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endsection
