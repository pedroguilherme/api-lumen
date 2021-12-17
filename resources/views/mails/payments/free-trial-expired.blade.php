@component('mail::layout')
    {{-- Header --}}
    @slot('header')
        @component('mail::header', ['url' => config('app.site_url')])
            <img class='logo' src="{{Storage::disk('s3')->url('app/site/geral/logo.png')}}" alt="Logo"/>
        @endcomponent
    @endslot

    <p style="text-align: justify;">
        Revenda {{$publisher->name}} de CNPJ {{$publisher->cpf_cnpj}} falta pouco para o término de sua carência e nós
        da Empresa gostaríamos de tê-lo conosco.
        <br/>
        <br/>
        Temos 3 modelos de planos, escolha o que melhor servir para sua loja.
        <br/>
        <a href="{{ config('app.site_url') }}/admin/meu-plano">Veja nossos planos!</a>
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


