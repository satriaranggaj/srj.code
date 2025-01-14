<!-- Footer -->
<footer id="footer">
    <div class="container">
        <div class="title">
            <h1>
                <center>
                    SATRIA RANGGA JATI
                </center>
            </h1>
        </div>
        <div class="foot-link">
            <ul>
                <li class ="{{ ($title === "Home") ? 'active' : '' }}"><a class="navbar-link" href="{{ asset(route('home')) }}">Home</a></li>
                <li class ="{{ ($title === "Bio") ? 'active' : '' }}"><a class="navbar-link" href="{{ asset(route('about')) }}">Bio</a></li>
                <li class ="{{ ($title === "Projects") ? 'active' : '' }}"><a class="navbar-link" href="{{ asset(route('project')) }}">Project</a></li>
                <li class ="{{ ($title === "Certificates") ? 'active' : '' }}"><a class="navbar-link" href="{{ asset(route('certificate')) }}">Certificates</a></li>
                <li><a class="navbar-link" href="https://drive.google.com/file/d/1TIWRqOEkxd1mdV1vQfNSW1S9P5X5djHm/view?usp=sharing" target="_blank">Resume</a></li>
            </ul>
        </div>
        <div class="others">
            <div class="sosmed">
                <div class="ig">
                    <a href="https://www.instagram.com/satria_rangga_j" target="_blank"><i class="fa-brands fa-instagram"></i></a>
                </div>
                <div class="fb">
                    <a href="https://web.facebook.com/satriaranggajati.jati" target="_blank"><i class="fa-brands fa-facebook-f"></i></a>
                </div>
                <div class="git">
                    <a href="https://github.com/satriaranggaj" target="_blank"><i class="fa-brands fa-github"></i></a>
                </div>
                <div class="li">
                    <a href="https://www.linkedin.com/in/satriaranggamyid/" target="_blank"><i class="fa-brands fa-linkedin-in"></i></a>
                </div>
            </div>
        </div>
        <div class="copyright">
            <p><i class="fa-regular fa-copyright"></i> 2023 Created By Satria</p>
        </div>
    </div>
</footer>
<!-- Footer -->