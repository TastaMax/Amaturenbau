<header>
    <nav class="navbar navbar-expand-lg bg-body" style="background-color: rgba(0, 0, 0, 0.03)!important;">
        <div class="container-fluid">
            <button
                data-mdb-collapse-init
                class="navbar-toggler"
                type="button"
                data-mdb-target="#navigationbar"
                aria-controls="navigationbar"
                aria-expanded="false"
                aria-label="Toggle navigation"
            >
                <i class="fas fa-bars"></i>
            </button>
            <div class="collapse navbar-collapse" id="navigationbar">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    @foreach($items as $item)
                        <li class="nav-item @if($item['active']) active @endif">
                            <a class="nav-link" @if($item['active'])aria-current="page"@endif href="{{ url($item['url']) }}">{{ $item['name'] }}</a>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </nav>
</header>
