<!-- Navbar -->
<nav id="navbar">
    <div class="logo">
        <a href="/">
            <h1>SRJ</h1>
        </a>
    </div>
    <div class="nav-link">
        <ul>
            <li class ="{{ ($title === "Home") ? 'active' : '' }}"><a class="navbar-link" href="/">Home</a></li>
            <li class ="{{ ($title === "Bio") ? 'active' : '' }}"><a class="navbar-link" href="/about">Bio</a></li>
            <li class ="{{ ($title === "Projects") ? 'active' : '' }}"><a class="navbar-link" href="/projects">Project</a></li>
            <li class ="{{ ($title === "Blog") ? 'active' : '' }}"><a class="navbar-link" href="/blog">Blog</a></li>
            <li class ="{{ ($title === "Certificates") ? 'active' : '' }}"><a class="navbar-link" href="/certificates">Certificates</a></li>
            <li class ="{{ ($title === "Resume") ? 'active' : '' }}"><a class="navbar-link" href="/resume" target="_blank">Resume</a></li>
        </ul>
    </div>
    <div class="menu-toggle">
        <input type="checkbox"/>
        <span></span>
        <span></span>
        <span></span>
    </div>
    <div class="button-contact">
        <a href="/contact">
            <h6>Contact Me</h6>
        </a>
    </div>
</nav>
<!-- navbar -->