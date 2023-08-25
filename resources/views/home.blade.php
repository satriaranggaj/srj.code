@extends('layouts.main')

@section('container')
        <!-- Bio -->
        <section id="bio">
            <div class="container">
                <div class="title">
                    <h1>{{ $name }}</h1>
                </div>
                <div class="desc">
                    <p>
                        {!! $biography !!}
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
                            {{ $passionTitle }}
                        </center>
                    </h3>
                </div>
                <div class="desc">
                    <p>
                        {{ $descPassion }}
                    </p>
                </div>
                <div class="skill-carousel">
                    <div class="carousel-content">
                        <div class="carousel-slide">
                            <img src="frontend/img/skill-logo/html-logo.png" alt="skill-logo">
                        </div>
                        <div class="carousel-slide">
                            <img src="frontend/img/skill-logo/css-logo.png" alt="skill-logo">
                        </div>
                        <div class="carousel-slide">
                            <img src="frontend/img/skill-logo/js-logo.png" alt="skill-logo">
                        </div>
                        <div class="carousel-slide">
                            <img src="frontend/img/skill-logo/sass-logo.png" alt="skill-logo">
                        </div>
                        <div class="carousel-slide">
                            <img src="frontend/img/skill-logo/bootstrap-logo.png" alt="skill-logo">
                        </div>
                        <div class="carousel-slide">
                            <img src="frontend/img/skill-logo/tailwind-logo.png" alt="skill-logo">
                        </div>
                        <div class="carousel-slide">
                            <img src="frontend/img/skill-logo/vue-logo.png" alt="skill-logo">
                        </div>
                        <div class="carousel-slide">
                            <img src="frontend/img/skill-logo/react-logo.png" alt="skill-logo">
                        </div>
                        <div class="carousel-slide">
                            <img src="frontend/img/skill-logo/jquery-logo.png" alt="skill-logo">
                        </div>
                        <div class="carousel-slide">
                            <img src="frontend/img/skill-logo/git-logo.png" alt="skill-logo">
                        </div>
                        <div class="carousel-slide">
                            <img src="frontend/img/skill-logo/figma-logo.png" alt="skill-logo">
                        </div>
                        {{-- kedua --}}
                        <div class="carousel-slide">
                            <img src="frontend/img/skill-logo/html-logo.png" alt="skill-logo">
                        </div>
                        <div class="carousel-slide">
                            <img src="frontend/img/skill-logo/css-logo.png" alt="skill-logo">
                        </div>
                        <div class="carousel-slide">
                            <img src="frontend/img/skill-logo/js-logo.png" alt="skill-logo">
                        </div>
                        <div class="carousel-slide">
                            <img src="frontend/img/skill-logo/sass-logo.png" alt="skill-logo">
                        </div>
                        <div class="carousel-slide">
                            <img src="frontend/img/skill-logo/bootstrap-logo.png" alt="skill-logo">
                        </div>
                        <div class="carousel-slide">
                            <img src="frontend/img/skill-logo/tailwind-logo.png" alt="skill-logo">
                        </div>
                        <div class="carousel-slide">
                            <img src="frontend/img/skill-logo/vue-logo.png" alt="skill-logo">
                        </div>
                        <div class="carousel-slide">
                            <img src="frontend/img/skill-logo/react-logo.png" alt="skill-logo">
                        </div>
                        <div class="carousel-slide">
                            <img src="frontend/img/skill-logo/jquery-logo.png" alt="skill-logo">
                        </div>
                        <div class="carousel-slide">
                            <img src="frontend/img/skill-logo/git-logo.png" alt="skill-logo">
                        </div>
                        <div class="carousel-slide">
                            <img src="frontend/img/skill-logo/figma-logo.png" alt="skill-logo">
                        </div>
                        {{-- ketiga --}}
                        <div class="carousel-slide">
                            <img src="frontend/img/skill-logo/html-logo.png" alt="skill-logo">
                        </div>
                        <div class="carousel-slide">
                            <img src="frontend/img/skill-logo/css-logo.png" alt="skill-logo">
                        </div>
                        <div class="carousel-slide">
                            <img src="frontend/img/skill-logo/js-logo.png" alt="skill-logo">
                        </div>
                        <div class="carousel-slide">
                            <img src="frontend/img/skill-logo/sass-logo.png" alt="skill-logo">
                        </div>
                        <div class="carousel-slide">
                            <img src="frontend/img/skill-logo/bootstrap-logo.png" alt="skill-logo">
                        </div>
                        <div class="carousel-slide">
                            <img src="frontend/img/skill-logo/tailwind-logo.png" alt="skill-logo">
                        </div>
                        <div class="carousel-slide">
                            <img src="frontend/img/skill-logo/vue-logo.png" alt="skill-logo">
                        </div>
                        <div class="carousel-slide">
                            <img src="frontend/img/skill-logo/react-logo.png" alt="skill-logo">
                        </div>
                        <div class="carousel-slide">
                            <img src="frontend/img/skill-logo/jquery-logo.png" alt="skill-logo">
                        </div>
                        <div class="carousel-slide">
                            <img src="frontend/img/skill-logo/git-logo.png" alt="skill-logo">
                        </div>
                        <div class="carousel-slide">
                            <img src="frontend/img/skill-logo/figma-logo.png" alt="skill-logo">
                        </div>
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
                    <div class="left-project">
                        <div class="project-card">
                            <div class="title-project">
                                <h5>Company Profile Coorperation KLM</h5>
                            </div>
                            <a href="https://satriaranggaj.github.io/koperasi.klm/index.html">View Project</a>
                        </div>
                    </div>
                    <div class="right-project">
                        <div class="container-project one">
                            <div class="project-card">
                                <div class="thumbnail">
                                    <img src="frontend/img/project/my-portfolio.png" alt="Project Thumbnail" height="100%">
                                </div>
                                <div class="title">
                                    <h6>My First Portfolio</h6>
                                    <a href="https://satriaranggaj.github.io/">View Project</a>
                                </div>
                            </div>
                            <div class="project-card">
                                <div class="thumbnail">
                                    <img src="frontend/img/project/not-found.png" alt="Project Thumbnail" height="100%">
                                </div>
                                <div class="title">
                                    <h6>Comming</h6>
                                    <a href="#">View Project</a>
                                </div>
                            </div>
                            <div class="project-card">
                                <div class="thumbnail">
                                    <img src="frontend/img/project/not-found.png" alt="Project Thumbnail" height="100%">
                                </div>
                                <div class="title">
                                    <h6>Comming</h6>
                                    <a href="#">View Project</a>
                                </div>
                            </div>
                        </div>
                        <div class="container-project two">
                            <div class="project-card">
                                <div class="thumbnail">
                                    <img src="frontend/img/project/not-found.png" alt="Project Thumbnail" height="100%">
                                </div>
                                <div class="title">
                                    <h6>Comming</h6>
                                    <a href="#">View Project</a>
                                </div>
                            </div>
                            <div class="project-card">
                                <div class="thumbnail">
                                    <img src="frontend/img/project/not-found.png" alt="Project Thumbnail" height="100%">
                                </div>
                                <div class="title">
                                    <h6>Comming</h6>
                                    <a href="#">View Project</a>
                                </div>
                            </div>
                            <div class="project-card">
                                <div class="thumbnail">
                                    <img src="frontend/img/project/not-found.png" alt="Project Thumbnail" height="100%">
                                </div>
                                <div class="title">
                                    <h6>Comming</h6>
                                    <a href="#">View Project</a>
                                </div>
                            </div>
                        </div>
                        <div class="container-project three">
                            <div class="project-card">
                                <div class="thumbnail">
                                    <img src="frontend/img/project/not-found.png" alt="Project Thumbnail" height="100%">
                                </div>
                                <div class="title">
                                    <h6>Comming</h6>
                                    <a href="#">View Project</a>
                                </div>
                            </div>
                            <div class="project-card">
                                <div class="thumbnail">
                                    <img src="frontend/img/project/not-found.png" alt="Project Thumbnail" height="100%">
                                </div>
                                <div class="title">
                                    <h6>Comming</h6>
                                    <a href="#">View Project</a>
                                </div>
                            </div>
                            <div class="project-card">
                                <div class="thumbnail">
                                    <img src="frontend/img/project/not-found.png" alt="Project Thumbnail" height="100%">
                                </div>
                                <div class="title">
                                    <h6>Comming</h6>
                                    <a href="#">View Project</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- Project -->
        <!-- Blog -->
        <section id="blog">
            <div class="container">
                <div class="title">
                    <h3>
                        <center>
                            BLOG
                        </center>
                    </h3>
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
        <!-- Blog -->
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
   