@component('mail::layout')
    {{-- Header --}}
    @slot('header')
        @component('mail::header', ['url' => config('app.site_url')])
            <img class='logo' src="{{Storage::disk('s3')->url('app/site/geral/logo.png')}}" alt="Logo"/>
        @endcomponent
    @endslot

    <h1 style="text-align: center;">Alguém está interessado em seu anúncio!</h1>


    <p style="text-align: center;">
        Olá {{$offer->publisher->name}},
        <br/>
        O veículo <strong>{{$offer->vehicle->brand->name}} {{$offer->vehicle->model->name}}</strong> recebeu um novo
        possível cliente.
        <br/>
        {{$offer->client_name}} pediu para visualizar seu número de contato, caso ainda ele não tenha entrado em
        contato, mande uma mensagem ou ligue para ele!
    </p>
    <hr/>
    <p>
        Nome: {{$offer->client_name}}<br/>
        E-mail: {{$offer->client_email}}<br/>
        Celular: {{$offer->client_contact}}<br/>
        Veículo: <a
            href="#">{{$offer->vehicle->brand->name.' '.$offer->vehicle->model->name.' '.$offer->vehicle->model->version}}</a>
    </p>
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


