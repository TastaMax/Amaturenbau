@extends('layouts.app')

@section('content')

    <section class="intro">
        <div class="h-100">
            <div class="mask d-flex align-items-center h-100"
                 style="min-height: 100vh; background-image: url('{{ Vite::asset('resources/images/login/background.jpg') }}');">
                <div class="container">
                    <div class="row d-flex justify-content-center align-items-center">
                        <div class="col-12 col-lg-8 col-xl-8">
                            <div class="card login-card" style="border-radius: 1rem;" id="cardLogin">
                                <div class="row g-0">
                                    <div class="col-md-4 d-none d-md-block">
                                        <img
                                            src="{{ Vite::asset('resources/images/login/login.jpg') }}"
                                            alt="login form"
                                            class="imglogin"
                                        />
                                    </div>
                                    <div class="col-md-8 d-flex align-items-center">
                                        <div class="card-body py-5 px-4 p-md-5">
                                            <div class="col-12 float-end mb-5">
                                                <img src="{{ Vite::asset('resources/images/logo/logo.png') }}" alt="Logo"
                                                     class="float-end"
                                                     style="width: 250px">
                                            </div>
                                            <div class="col-12">
                                                <h1 class="fw-bold mb-4">ShopWare 6 Schnittstelle</h1>
                                                <p class="mb-4"></p>
                                            </div>

                                            @include('partials.error')

                                            <form action="{{ route('login') }}" method="POST">
                                                @csrf
                                                <div class="form-outline mb-4" data-mdb-input-init>
                                                    <input type="email" id="email" name="email" class="form-control" required/>
                                                    <label class="form-label" for="email">E-Mail Adresse</label>
                                                </div>

                                                <div class="form-outline mb-4" data-mdb-input-init>
                                                    <input type="password" id="password" name="password" class="form-control"
                                                           required/>
                                                    <label class="form-label" for="password">Passwort</label>
                                                </div>
                                                <div class="d-flex justify-content-end pt-1 mb-4">
                                                    <button class="btn btn-primary btn-rounded" id="login" type="submit">Login
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

@endsection
