@extends('layouts.app')

@section('content')
    <section class="my-5">
        <div class="container my-3">
            <div class="row">
                <div class="col-md-12">
                    <h1>ShopWare 6 Synchronisierung</h1>
                </div>
            </div>
        </div>
        <div class="container mb-3">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <a class="btn btn-primary" href="/shopware/sync/category">Synchronisiere Kategorien</a>
                            <a class="btn btn-primary" href="/shopware/sync/subcategory">Synchronisiere SubKategorien</a>
                            <a class="btn btn-primary" href="/shopware/sync/product">Synchronisiere Produkte</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="container mb-3">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <a class="btn btn-primary" href="/shopware/sync/migrate-category">Migriere SubKategorie</a>
                            <a class="btn btn-primary" href="/shopware/sync/migrate-productclass">Migriere Produktklasse</a>
                            <a class="btn btn-primary" href="/shopware/sync/migrate-product">Migriere Produkt</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </section>
@endsection
