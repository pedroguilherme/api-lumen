@component('mail::layout')
    {{-- Header --}}
    @slot('header')
        @component('mail::header', ['url' => config('app.site_url')])
            <img class='logo' src="{{Storage::disk('s3')->url('app/site/geral/logo.png')}}" alt="Logo"/>
        @endcomponent
    @endslot

    <h1 style="text-align: center;">Chegou uma nova proposta de financiamento para o seu anúncio!</h1>


    <p style="text-align: center;">
        Olá {{$offer->publisher->name}},
        <br/>
        Você recebeu uma nova proposta de financiamento de, {{$offer->client_name}}
        no veículo <strong>{{$offer->vehicle->brand->name}} {{$offer->vehicle->model->name}}</strong>
    </p>
    <hr/>
    <p>
        Nome: {{$offer->client_name}}<br/>
        E-mail: {{$offer->client_email}}<br/>
        Celular: {{$offer->client_contact}}<br/>
        Veículo: <a
            href="{{$offer->vehicle->url}}">{{$offer->vehicle->brand->name.' '.$offer->vehicle->model->name.' '.$offer->vehicle->model->version}}</a>
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


