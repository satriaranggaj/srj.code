<!-- Navbar -->
<nav id="navbar">
    <div class="logo">
        <a href="/">
            <h1>SRJ</h1>
        </a>
    </div>
    <div class="nav-link">
        <ul>
            <li class ="{{ ($title === "Home") ? 'active' : '' }}"><a class="navbar-link" href="{{ asset(route('home')) }}">Home</a></li>
            <li class ="{{ ($title === "Bio") ? 'active' : '' }}"><a class="navbar-link" href="{{ asset(route('about')) }}">Bio</a></li>
            <li class ="{{ ($title === "Projects") ? 'active' : '' }}"><a class="navbar-link" href="{{ asset(route('project')) }}">Project</a></li>
            <li class ="{{ ($title === "Certificates") ? 'active' : '' }}"><a class="navbar-link" href="{{ asset(route('certificate')) }}">Certificates</a></li>
            <li class ="{{ ($title === "Resume") ? 'active' : '' }}"><a class="navbar-link" href="{{ asset(route('resume')) }}" target="_blank">Resume</a></li>
        </ul>
    </div>
    <div class="menu-toggle">
        <input type="checkbox"/>
        <span></span>
        <span></span>
        <span></span>
    </div>
    <div class="button-contact">
        <a href="{{ asset(route('contact')) }}">
            <h6>Contact Me</h6>
        </a>
    </div>
</nav>
<!-- navbar -->
