@component('mail::layout')
    {{-- Header --}}
    @slot('header')
        @component('mail::header', ['url' => config('app.site_url')])
            <img class='logo' src="{{Storage::disk('s3')->url('app/site/geral/logo.png')}}" alt="Logo"/>
        @endcomponent
    @endslot

    <p style="text-align: justify;">
        Olá {{$offer["client_name"]}},
        <br/>
        <br/>
        A sua mensagem foi enviada, em breve o anunciante retornará.
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


