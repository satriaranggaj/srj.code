<nav class="navbar navbar-dark navbar-expand-lg fixed-top shadow-sm">
    <div class="container">
      <a class="navbar-brand" href="#"><img height="42px" src="{{ asset('/img/logo.webp') }}" alt=""></a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNavDropdown">
        <ul class="navbar-nav ms-auto">
          <li class="nav-item">
            <a class="nav-link {{ (request()->is('/')) ? 'active' : '' }}" aria-current="page" href="/">Beranda</a>
          </li>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
              Product
            </a>
            <ul class="dropdown-menu">
              <li><a class="dropdown-item" href="/product/haji">Haji</a></li>
              <li><a class="dropdown-item" href="/product/umroh">Umroh</a></li>
            </ul>
          </li>
          <li class="nav-item">
            <a class="nav-link {{ (request()->is('galeri/foto')) ? 'active' : '' }}" href="galeri/foto">Galeri</a>
          </li>
          <li class="nav-item">
            <a class="nav-link {{ (request()->is('/#contact-form')) ? 'active' : '' }}" href="/#contact-form">Kontak</a>
          </li>
        </ul>
      </div>
    </div>
  </nav>
