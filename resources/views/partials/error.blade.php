@if ($errors->any())
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="alert alert-danger mt-3" role="alert">
                    <i class="fas fa-times-circle me-3"></i> Leider ist ein Fehler aufgetreten! Bitte beachten Sie folgende Dinge:
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li class="list-unstyled">{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endif
