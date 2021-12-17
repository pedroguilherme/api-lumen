@component('mail::layout')
    {{-- Header --}}
    @slot('header')
        @component('mail::header', ['url' => config('app.site_url')])
            <img class='logo' src="{{Storage::disk('s3')->url('app/site/geral/logo.png')}}" alt="Logo"/>
        @endcomponent
    @endslot

    Olá {{$user->name}}, você solicitou a troca de senha.
    <br/>
    <br/>
    Clique no botão abaixo para informar uma nova senha.

    @component('mail::button', ['url' => config('app.site_url').'resetar-senha?token='.$token, 'color' => 'primary'])
        Trocar senha
    @endcomponent

    Esse link irá expirar em 60 minutos.
    <br/>
    <br/>
    Se não foi você que pediu a troca de senha, nenhuma ação é necessária.
    <br/>
    <hr/>
    <br/>

    Caso o botão acima não funcione copie e cole o link a seguir em seu navegador:
    <a href="{{config('app.site_url').'resetar-senha?token='.$token}}" class="text-wrapper">
        {{config('app.site_url').'resetar-senha?token='.$token}}
    </a>

    {{-- Footer --}}
    @slot('footer')
        @component('mail::footer')
            © {{ date('Y') }} {{ config('app.site_url') }}.
        @endcomponent
    @endslot
@endcomponent


