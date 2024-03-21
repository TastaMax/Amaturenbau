<nav class="navbar bg-body navbar-expand-lg">
    <div class="container-fluid">
        <a class="navbar-brand mt-lg-0" href="{{ url('/') }}" title="Home">
            <img
                src="{{ Vite::asset('resources/images/logo/logo.png') }}"
                alt="logo"
                width="100"
            />
        </a>
        <div class="d-flex gap-3">
            <button
                data-mdb-collapse-init
                class="navbar-toggler"
                type="button"
                data-mdb-target="#navbarSupportedContent"
                aria-controls="navbarSupportedContent"
                aria-expanded="false"
                aria-label="Toggle navigation"
            >
                <i class="fas fa-bars"></i>
            </button>
        </div>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item p-1 d-flex justify-content-center @if(request()->is('/')) active @endif">
                    <a class="nav-link nav-link-ltr" href="{{ url('/') }}" title="Startseite">Startseite</a>
                </li>
                <li class="nav-item p-1 d-flex justify-content-center @if(Str::startsWith(request()->path(), 'shopware/kategorie')) active @endif">
                    <a class="nav-link nav-link-ltr" href="{{ url('/shopware/kategorie') }}" title="Kategorien">Kategorien</a>
                </li>
                <li class="nav-item p-1 d-flex justify-content-center @if(Str::startsWith(request()->path(), 'shopware/produktklasse')) active @endif">
                    <a class="nav-link nav-link-ltr" href="{{ url('/shopware/produktklasse') }}" title="Produktklassen">Produktklassen</a>
                </li>
                <li class="nav-item p-1 d-flex justify-content-center">
                    <a class="nav-link nav-link-ltr" href="{{ url('/shopware/produkte') }}" title="Produkte">Produkte</a>
                </li>
                <li class="nav-item p-1 d-flex justify-content-center @if(Str::startsWith(request()->path(), 'shopware/sync')) active @endif">
                    <a class="nav-link nav-link-ltr" href="{{ url('/shopware/sync') }}" title="Synchronisieren">Sync</a>
                </li>
                <li class="nav-item p-1 d-flex justify-content-center @if(request()->is('faq')) active @endif">
                    <a class="nav-link nav-link-ltr" href="{{ url('/faq') }}" title="Synchronisieren">FAQ</a>
                </li>
            </ul>
            <ul class="navbar-nav flex-row">
                <li class="nav-item align-items-center d-flex mx-5">
                    <i class="fas fa-sun"></i>
                    <!-- Default switch -->
                    <div class="mx-1 form-check form-switch">
                        <input class="form-check-input" type="checkbox" role="switch" id="themingSwitcher" />
                    </div>
                    <i class="fas fa-moon"></i>
                </li>
                <li class="nav-item mx-2 @if(request()->is('einstellungen')) active @endif">
                    <a class="nav-link" href="{{ url('einstellungen') }}">Einstellungen</a>
                </li>
                <li class="nav-item p-1 d-flex justify-content-center align-items-center">
                    <form action="{{ route('logout') }}" method="post">
                        @csrf
                        <button type="submit" class="btn btn-light" title="Logout">Logout</button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</nav>
