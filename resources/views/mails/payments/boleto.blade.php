@component('mail::layout')
    {{-- Header --}}
    @slot('header')
        @component('mail::header', ['url' => config('app.site_url')])
            <img class='logo' src="{{Storage::disk('s3')->url('app/site/geral/logo.png')}}" alt="Logo"/>
        @endcomponent
    @endslot

    <p style="text-align: justify;">
        Olá {{$publisher->name}},
        <br/>
        <br/>
        O boleto para a renovação do seu plano foi gerado.
        <br/>
        Clique no link abaixo para efetuar o pagamento.
        <br/>
        <a href="{{$billing->boleto_url}}">Boleto Empresa</a>
        <br/>
        <br/>
        Obrigado por escolher a Empresa.
    </p>
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


