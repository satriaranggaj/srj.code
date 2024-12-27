@extends('layouts.main')

@section('container')
        <!-- Bio -->
        <section id="bio">
            <div class="container">
                <div class="title">
                    <h1>SATRIA RANGGA JATI</h1>
                </div>
                <div class="desc">
                    <p>
                        Satria Rangga Jati is a Fullstack Web Developer who started his career in the field in mid-2022. He graduated from Vocational High School majoring in Computer and Networking Engineering from SMK Negeri 4 Kota Bekasi in 2021. Since then, he has gained the basics of web design and development, allowing him to delve into advanced levels of various programming languages used in fullstack development.
                        As a developer, Satria is always excited to keep learning and keeping up with the latest trends in web technology. He constantly looks for opportunities to improve his skills in web development and contribute to creating exceptional online experiences for users.
                        Satria focuses on user experience (UX) and ensures that every element on the web page leaves a positive impression and is user-friendly. Additionally, he ensures that websites are responsive, making them accessible smoothly on various devices such as desktop computers, tablets, and smartphones.
                        As a talented young developer, Satria has successfully built a diverse and captivating project portfolio. He enjoys trying out new technologies and using them to create innovative and creative solutions.
                        Each web project is a challenge for Satria to create something unique and appealing. He is committed to delivering the best results for clients and end-users while continuously developing his skills in web development.
                        With passion and dedication as a Fullstack Web Developer, Satria looks forward to exploring his potential and creativity further in developing captivating and inspiring web solutions. He hopes to continue contributing to creating impressive web experiences that add value for users. If you need assistance with web development projects, Satria is more than happy to help!
                    </p>
                </div>
                <div class="button-bio">
                    <a href="/about">
                        Read Full His Bio
                    </a>
                </div>
            </div>
        </section>
        <!-- Bio -->
        <!-- Passion -->
        <section id="passion">
            <div class="container">
                <div class="title">
                    <h3>
                        <center>
                            FULL STACK WEB DEVELOPER
                        </center>
                    </h3>
                </div>
                <div class="desc">
                    <p>
                        As a Fullstack Web Developer, Satria's task is to design and implement both the front-end and back-end of a website. He creates layouts, designs, and functionalities that can be enjoyed by users. Satria focuses on the user experience (UX) aspect and ensures that every element on the web page leaves a positive impression and is user-friendly. Additionally, he ensures that the website is responsive, allowing smooth access on various devices such as desktop computers, tablets, and smartphones. Satria is always eager to continue learning and stay up-to-date with the latest developments in the web development industry. He enjoys experimenting with new technologies and leveraging them to create innovative and creative solutions. Each web project is a challenge for him to create something unique and captivating. Satria is committed to delivering the best outcomes for clients and end-users, as well as continuously developing his skills in web development. He aspires to continue contributing to the creation of impressive web experiences that add value to users. If you need assistance with your web development project, he is more than happy to help!
                    </p>
                </div>
                <div class="skill-carousel">
                    <div class="carousel-content" style="width: fit-content;">
                        @foreach ($skills as $skill)
                            <div class="carousel-slide" style="padding: 0 25px;">
                                <img src="{{ asset('storage/' . $skill->image) }}" alt="skill-logo">
                            </div>
                        @endforeach
                        @foreach ($skills as $skill)
                            <div class="carousel-slide" style="padding: 0 25px;">
                                <img src="{{ asset('storage/' . $skill->image) }}" alt="skill-logo">
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>
        <!-- Passion -->
        <!-- Project -->
        <section id="project">
            <div class="container-fluid">
                <div class="title">
                    <h3>
                        <center>
                            FEATURED PROJECT
                        </center>
                    </h3>
                </div>
                <div class="project-content">
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
        <!-- Project -->
        <!-- Feedback -->
        <section id="feedback">
            <div class="container">
                <div class="row text-center mb-3">
                  <div class="title">
                    <h3>
                            FEEDBACK
                    </h3>
                  </div>
                </div>
                <div class="row justify-content-center">
                  <div class="col-md-6">
                    <div class="alert alert-success alert-dismissible fade show d-none" role="alert">
                      <strong>Terimakasih!</strong> Pesan Anda Sudah Diterima.
                      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <form name="submit-to-google-sheet">
                      <div class="mb-3">
                        <label for="name" class="form-label">Full Name</label>
                        <input type="text" class="form-control" id="name" aria-describedby="name" name="nama" />
                      </div>
                      <div class="mb-3">
                        <label for="email" class="form-label">Email address</label>
                        <input type="email" class="form-control" id="email" aria-describedby="email" name="email" />
                      </div>
                      <div class="mb-3">
                        <label for="email" class="form-label">Subject</label>
                        <input type="subject" class="form-control" id="subject" aria-describedby="subject" name="subject" />
                      </div>
                      <div class="mb-3">
                        <label for="message" class="form-label">Text Message</label>
                        <textarea class="form-control" id="message" rows="3" name="pesan"></textarea>
                      </div>
                      <button type="submit" class="btn btn-submit">Send</button>

                      <button class="btn btn-loading d-none" type="button" disabled>
                        <span class="spinner-grow spinner-grow-sm" role="status" aria-hidden="true"></span>
                        Loading...
                      </button>
                    </form>
                  </div>
                </div>
              </div>
        </section>
        <!-- Feedback -->
@endsection
