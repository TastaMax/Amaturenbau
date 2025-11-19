@extends('layouts.app')
@section('content')

    @include('partials.error')
    <section class="my-5" id="settings">
        <div class="container my-3">
            <div class="row">
                <div class="col-md-12">
                    <h1>Einstellungen</h1>
                </div>
            </div>
        </div>
        <div class="container px-4 mb-4">
            <div class="row">
                <div class="col-md-12 mb-4">
                    <div class="card px-5 py-3" id="shopware">
                        <div class="card-header">
                            <h2>ShopWare 6 API</h2>
                            <p>Richten Sie hier den Zugang für ShopWare 6 ein.</p>
                        </div>
                        <form action="{{ route('shopware') }}" method="POST">
                            <div class="card-body">
                                @csrf
                                <div class="row">
                                    <div class="col-md-12">
                                        <h3>Zugangsdaten</h3>
                                        <div class="form-outline @if(!$shopwareConnection) mb-5 @else mb-3 @endif" data-mdb-input-init>
                                            <input type="text"
                                                   class="form-control @if(!$shopwareConnection) is-invalid @endif"
                                                   id="shopwareurl" name="shopwareurl" value="{{ $url }}" required/>
                                            <label for="shopwareurl" class="form-label">ShopWare Adresse</label>
                                            @if(!$shopwareConnection)
                                                <div class="invalid-feedback">Leider konnte keine Verbindung zur ShopWare
                                                    API hergestellt werden.
                                                </div>
                                            @endif
                                        </div>
                                        <div class="form-outline @if(!$shopwareToken) mb-5 @else mb-3 @endif" data-mdb-input-init>
                                            <input type="text" class="form-control @if(!$shopwareToken) is-invalid @endif"
                                                   id="shopwareid" name="shopwareid" value="{{ $shopwareId }}" required/>
                                            <label for="shopwareid" class="form-label">Zugangs-ID</label>
                                            @if(!$shopwareToken)
                                                <div class="invalid-feedback"> @if(!$shopwareConnection)
                                                        Der Token konnte nicht überprüft werden. Überprüfen Sie die
                                                        Verbindung mit der ShopWare API.
                                                    @else
                                                        Der Token ist leider ungültig.
                                                    @endif</div>
                                            @endif
                                        </div>
                                        <div class="form-outline @if(!$shopwareToken) mb-5 @else mb-3 @endif" data-mdb-input-init>
                                            <input type="text" class="form-control @if(!$shopwareToken) is-invalid @endif"
                                                   id="shopwaretoken" name="shopwaretoken" value="{{ $token }}" required/>
                                            <label for="shopwaretoken" class="form-label">Sicherheitsschlüssel</label>
                                            @if(!$shopwareToken)
                                                <div class="invalid-feedback"> @if(!$shopwareConnection)
                                                        Der Token konnte nicht überprüft werden. Überprüfen Sie die
                                                        Verbindung mit der ShopWare API.
                                                    @else
                                                        Der Token ist leider ungültig.
                                                    @endif</div>
                                            @endif
                                        </div>
                                        <h3>Allgemein</h3>
                                        @if(!$shopwareConnection || !$shopwareToken)
                                            <div class="form-outline @if(!$shopwareConnection || !$shopwareToken) mb-5 @else mb-3 @endif" data-mdb-input-init>
                                                <input type="text" class="form-control is-invalid" id="shopwaretoken"
                                                       name="shopwaretoken" value="{{ env('SHOPWARE_SALES_CHANNEL_ID')  }}" disabled required/>
                                                <label for="shopwaretoken" class="form-label">Verkaufskanal</label>
                                                <div class="invalid-feedback"> Leider kann der Verkaufskanal aufgrund der
                                                    Verbindung oder dem Token nicht geändert werden!
                                                </div>
                                            </div>
                                        @else
                                            <div class="mb-3">
                                                <select class="select" name="shopwaresaleschannelid" data-mdb-placeholder="Verkaufskanal" data-mdb-select-init>
                                                    @if($shopwareSalesChannelId === '') <option value="" selected disabled> Kein Verkaufskanal gewählt </option> @endif
                                                    @foreach($shopwareSalesChannels['body']['data'] as $salesChannel)
                                                        <option value="{{ $salesChannel['id'] }}"
                                                                data-mdb-secondary-text="{{ $salesChannel['id'] }}"
                                                                @if($shopwareSalesChannelId === $salesChannel['id']) selected @endif>{{ $salesChannel['attributes']['name'] }}</option>
                                                    @endforeach
                                                </select>
                                                <label class="form-label select-label">Verkaufskanal</label>
                                            </div>

                                        @endif


                                        @if(!$shopwareConnection || !$shopwareToken)
                                            <div class="form-outline mb-3" data-mdb-input-init>
                                                <input type="text" class="form-control is-invalid" id="shopwaredefaultcategory"
                                                       name="shopwaredefaultcategory" value="{{ env('SHOPWARE_SALES_CHANNEL_ID')  }}" disabled required/>
                                                <label for="shopwaredefaultcategory" class="form-label">Kategorie</label>
                                                <div class="invalid-feedback"> Leider kann die Kategorie aufgrund der
                                                    Verbindung oder dem Token nicht geändert werden!
                                                </div>
                                            </div>
                                        @else
                                            <div class="mb-3">
                                                <select class="select" name="shopwaredefaultcategory" data-mdb-placeholder="Kategorie" data-mdb-select-init>
                                                    @if($shopwareDefaultCategory === '') <option value="" selected disabled> Keine Kategorie gewählt </option> @endif
                                                    @foreach($shopwareCategory['body']['data'] as $category)
                                                        <option value="{{ $category['id'] }}"
                                                                data-mdb-secondary-text="{{ $category['id'] }}"
                                                                @if($shopwareDefaultCategory === $category['id']) selected @endif>{{ $category['attributes']['name'] }}</option>
                                                    @endforeach
                                                </select>
                                                <label class="form-label select-label">Standard Kategorie</label>
                                            </div>
                                        @endif

                                        @if(!$shopwareConnection || !$shopwareToken)
                                            <div class="form-outline mb-3" data-mdb-input-init>
                                                <input type="text" class="form-control is-invalid" id="shopwaredefaultmanufacturer"
                                                       name="shopwaredefaultmanufacturer" value="{{ env('SHOPWARE_DEFAULT_MANUFACTURER')  }}" disabled required/>
                                                <label for="shopwaredefaultmanufacturer" class="form-label">Hersteller</label>
                                                <div class="invalid-feedback"> Leider kann der Hersteller aufgrund der
                                                    Verbindung oder dem Token nicht geändert werden!
                                                </div>
                                            </div>
                                        @else
                                            <div class="mb-3">
                                                <select class="select" name="shopwaredefaultmanufacturer" data-mdb-placeholder="Hersteller" data-mdb-select-init>
                                                    @if($shopwareDefaultManufacturer === '') <option value="" selected disabled> Keine Kategorie gewählt </option> @endif
                                                    @foreach($shopwareManufacturers['body']['data'] as $manufacturer)
                                                        <option value="{{ $manufacturer['id'] }}"
                                                                data-mdb-secondary-text="{{ $manufacturer['id'] }}"
                                                                @if($shopwareDefaultManufacturer === $manufacturer['id']) selected @endif>{{ $manufacturer['attributes']['name'] }}</option>
                                                    @endforeach
                                                </select>
                                                <label class="form-label select-label">Standard Hersteller</label>
                                            </div>
                                        @endif

                                        @if(!$shopwareConnection || !$shopwareToken)
                                            <div class="form-outline mb-3" data-mdb-input-init>
                                                <input type="text" class="form-control is-invalid" id="shopwaredefaulttax"
                                                       name="shopwaredefaulttax" value="{{ env('SHOPWARE_DEFAULT_TAX') }}" disabled required/>
                                                <label for="shopwaredefaulttax" class="form-label">Steuersatz</label>
                                                <div class="invalid-feedback"> Leider kann der Steuersatz aufgrund der Verbindung oder dem Token nicht geändert werden!</div>
                                            </div>
                                        @else
                                            <div class="mb-3">
                                                <select class="select" name="shopwaredefaulttax" data-mdb-placeholder="Steuersatz" data-mdb-select-init>
                                                    @if($shopwareDefaultTax === '') <option value="" selected disabled> Kein Steuersatz gewählt </option> @endif
                                                    @foreach($shopwareTaxes['body']['data'] as $tax)
                                                        <option value="{{ $tax['id'] }}"
                                                                data-mdb-secondary-text="{{ $tax['id'] }}"
                                                                @if($shopwareDefaultTax === $tax['id']) selected @endif>{{ $tax['attributes']['name'] }}</option>
                                                    @endforeach
                                                </select>
                                                <label class="form-label select-label">Standard Steuersatz</label>
                                            </div>
                                        @endif

                                        @if(!$shopwareConnection || !$shopwareToken)
                                            <div class="form-outline mb-3" data-mdb-input-init>
                                                <input type="text" class="form-control is-invalid" id="shopwaredefaultcurrency"
                                                       name="shopwaredefaultcurrency" value="{{ env('SHOPWARE_DEFAULT_CURRENCY') }}" disabled required/>
                                                <label for="shopwaredefaultcurrency" class="form-label">Währung</label>
                                                <div class="invalid-feedback"> Leider kann die Währung aufgrund der Verbindung oder dem Token nicht geändert werden!</div>
                                            </div>
                                        @else
                                            <div class="mb-3">
                                                <select class="select" name="shopwaredefaultcurrency" data-mdb-placeholder="Währung" data-mdb-select-init>
                                                    @if($shopwareDefaultCurrency === '') <option value="" selected disabled> Keine Währung gewählt </option> @endif
                                                    @foreach($shopwareCurrencys['body']['data'] as $currency)
                                                        <option value="{{ $currency['id'] }}"
                                                                data-mdb-secondary-text="{{ $currency['id'] }}"
                                                                @if($shopwareDefaultCurrency === $currency['id']) selected @endif>{{ $currency['attributes']['name'] }}</option>
                                                    @endforeach
                                                </select>
                                                <label class="form-label select-label">Standard Währung</label>
                                            </div>
                                        @endif


                                    </div>



                                </div>
                            </div>
                            <div class="card-footer">
                                <button type="submit" class="btn btn-primary float-end">Speichern</button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="col-md-12 mb-4">
                    <div class="card px-5 py-3" id="mail">
                        <div class="card-header">
                            <h2>Mailsystem</h2>
                            <p>
                                Treffen Sie hier Ihre Einstellungen für den E-Mail-Versand ein.
                                Der Mail Versand verwendet ausschließlich SMTP.
                            </p>


                            <div class="accordion accordion-flush" id="accordionFlushExample">
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="flush-headingOne">
                                        <button
                                            data-mdb-collapse-init
                                            class="accordion-button collapsed"
                                            type="button"
                                            data-mdb-toggle="collapse"
                                            data-mdb-target="#flush-collapseOne"
                                            aria-expanded="false"
                                            aria-controls="flush-collapseOne"
                                        >
                                            Empfohlene Konfiguration
                                        </button>
                                    </h2>
                                    <div
                                        id="flush-collapseOne"
                                        class="accordion-collapse collapse"
                                        aria-labelledby="flush-headingOne"
                                        data-mdb-parent="#accordionFlushExample"
                                    >
                                        <div class="accordion-body">
                                            <table class="table">
                                                <thead>
                                                <tr>
                                                    <th scope="col">Name</th>
                                                    <th scope="col">Host</th>
                                                    <th scope="col">Port</th>
                                                    <th scope="col">Verschlüsselung</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                <tr class="table-primary">
                                                    <th scope="row">Office365</th>
                                                    <th>smtp.office365.com</th>
                                                    <td>587</td>
                                                    <td>TLS</td>
                                                </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <form action="{{ route('mail') }}" method="POST">
                            <div class="card-body">
                                @csrf
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-outline mb-3" data-mdb-input-init>
                                            <input type="text" class="form-control" id="smtphost" name="smtphost"
                                                   value="{{ config('mail.mailers.smtp.host') }}" required/>
                                            <label for="smtphost" class="form-label">Host</label>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-outline mb-3" data-mdb-input-init>
                                            <input type="text" class="form-control" id="smtpport" name="smtpport"
                                                   value="{{ config('mail.mailers.smtp.port') }}" required/>
                                            <label for="smtpport" class="form-label">Port</label>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-outline mb-3" data-mdb-input-init>
                                            <input type="text" class="form-control" id="smtpusername" name="smtpusername"
                                                   value="{{ config('mail.mailers.smtp.username') }}" required/>
                                            <label for="smtpusername" class="form-label">Benutzername/E-Mail</label>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-outline mb-3" data-mdb-input-init>
                                            <input type="password" class="form-control" id="smtppassword"
                                                   name="smtppassword" value="{{ config('mail.mailers.smtp.password') }}"/>
                                            <label for="smtppassword" class="form-label">Passwort</label>
                                        </div>
                                    </div>
                                    <div class="col-md-12 mb-3">
                                        <select class="select" id="smtpencryption" name="smtpencryption" data-mdb-select-init>
                                            <option value="null" @if( config('mail.mailers.smtp.encryption') == null) selected @endif>Keine
                                                Auswahl
                                            </option>
                                            <option value="tls" @if(config('mail.mailers.smtp.encryption') == 'tls') selected @endif>TLS
                                            </option>
                                            <option value="ssl" @if(config('mail.mailers.smtp.encryption') == 'ssl') selected @endif>SSL
                                            </option>
                                        </select>
                                        <label class="form-label select-label" for="smtpencryption">Verschlüsselung</label>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-outline mb-3" data-mdb-input-init>
                                            <input type="email" class="form-control" id="smtpemail" name="smtpemail"
                                                   value="{{ config('mail.from.address') }}" required/>
                                            <label for="smtpemail" class="form-label">E-Mail-Adresse</label>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-outline mb-3" data-mdb-input-init>
                                            <input type="text" class="form-control" id="smtpname" name="smtpname"
                                                   value="{{ config('mail.from.name') }}" required/>
                                            <label for="smtpname" class="form-label">E-Mail-Name</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer">
                                <button type="submit" class="btn btn-primary float-end">Speichern</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
