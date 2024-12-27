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
                    Satria Rangga Jati is a Fullstack Web Developer who started his career in the field in mid-2022. He graduated from Vocational High School majoring in Computer and Networking Engineering from SMK Negeri 4 Kota Bekasi in 2021. Since then, he has gained the basics of web design and development, allowing him to delve into advanced levels of various programming languages used in fullstack development.
                    <br><br>
                    As a developer, Satria is always excited to keep learning and keeping up with the latest trends in web technology. He constantly looks for opportunities to improve his skills in web development and contribute to creating exceptional online experiences for users.
                    <br><br>
                    Satria focuses on user experience (UX) and ensures that every element on the web page leaves a positive impression and is user-friendly. Additionally, he ensures that websites are responsive, making them accessible smoothly on various devices such as desktop computers, tablets, and smartphones.
                    <br><br>
                    As a talented young developer, Satria has successfully built a diverse and captivating project portfolio. He enjoys trying out new technologies and using them to create innovative and creative solutions.
                    <br><br>
                    Each web project is a challenge for Satria to create something unique and appealing. He is committed to delivering the best results for clients and end-users while continuously developing his skills in web development.
                    <br><br>
                    With passion and dedication as a Fullstack Web Developer, Satria looks forward to exploring his potential and creativity further in developing captivating and inspiring web solutions. He hopes to continue contributing to creating impressive web experiences that add value for users. If you need assistance with web development projects, Satria is more than happy to help!
                </p>
            </div>
        </div>
    </div>
</section>
@endsection
