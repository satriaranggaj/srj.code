@extends('layouts.main')

@section('container')   
<section id="biografi">
    <div class="container">
        <div class="title">
            <h1>
                <center>
                    BIOGRAPHY
                </center>
            </h1>
        </div>
        <div class="data-diri">
            <div class="foto-profil">
                <img src="frontend/img/photo-profil/{{$image}}" alt="Profile Photos">
            </div>
            <div class="desc">
                <p style="text-align: justify;">
                   {!! $biography !!}
                </p>
            </div>
        </div>
    </div>
</section>
@endsection
