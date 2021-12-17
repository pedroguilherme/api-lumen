@component('mail::layout')
    {{-- Header --}}
    @slot('header')
        @component('mail::header', ['url' => config('app.site_url')])
            <img class='logo' src="{{Storage::disk('s3')->url('app/site/geral/logo.png')}}" alt="Logo"/>
        @endcomponent
    @endslot

    <h1 style="text-align: center;">Chegou uma nova oportunidade de compra!</h1>


    <p>
        Olá {{$offer->publisher->name}},
        <br/>
        O cliente <strong>{{$offer->client_name}}</strong> gostaria de vender seu veículo.
        <br/>
        <br/>
        Ele está vendendo o veículo abaixo:
        <br/>
        Marca: <strong>{{$offer->version->model->brand->name}}</strong><br/>
        Modelo: <strong>{{$offer->version->model->name}}</strong><br/>
        Versão: <strong>{{$offer->version->name}}</strong><br/>
        Ano: <strong>{{$offer->client_car_year_manufacture}} / {{$offer->client_car_year_model}}</strong><br/>
        Descrição: {{$offer->client_car_details ?? 'Nada a informar.'}}
        <br/>
        <br/>
        {{$offer->client_name}} está disposto a negociar em até
        <br/>
        <strong style="font-size: 16px">{{$offer->client_car_discount}} abaixo da fipe!</strong>
        <br/>
    </p>
    <hr/>
    <p>
        Entre em contato com ele e faça bons negócios!<br/>
        Nome: {{$offer->client_name}}<br/>
        E-mail: {{$offer->client_email}}<br/>
        Celular: {{$offer->client_contact}}<br/>
    </p>
    <table class="panel" width="100%" cellpadding="0" cellspacing="0" role="presentation">
        <tr>
            <td class="panel-content">
                <table width="100%" cellpadding="0" cellspacing="0" role="presentation">
                    <tr>
                        <td class="panel-item text-center">
                            Mensagem: "{{$offer->client_init_message}}"
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
    <hr/>
    <br/>
    Atenciosamente,
    <br/>
    Empresa.
    <br/>
    <br/>

    {{-- Footer --}}
    @slot('footer')
        @component('mail::footer')
            © {{ date('Y') }} {{ config('app.site_url') }}
        @endcomponent
    @endslot
@endcomponent


