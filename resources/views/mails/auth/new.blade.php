@component('mail::layout')
    {{-- Header --}}
    @slot('header')
        @component('mail::header', ['url' => config('app.site_url')])
            <img class='logo' src="{{Storage::disk('s3')->url('app/site/geral/logo.png')}}" alt="Logo"/>
        @endcomponent
    @endslot

    Olá {{$user->name}}, seja bem-vindo a Empresa.
    <br/>
    <br/>
    Sua senha para acessar o portal encontra-se abaixo:


    <table class="panel" width="100%" cellpadding="0" cellspacing="0" role="presentation">
        <tr>
            <td class="panel-content">
                <table width="100%" cellpadding="0" cellspacing="0" role="presentation">
                    <tr>
                        <td class="panel-item text-center">
                            {{ $password }}
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    Não se preocupe, você pode altera-lá quando fizer seu primeiro Login.
    <br/>
    <br/>
    Para fazer seu primeiro login clique no botão abaixo

    @component('mail::button', ['url' => config('app.site_url').'entrar', 'color' => 'primary'])
        Acesse sua conta!
    @endcomponent

    <br/>
    <hr/>
    <br/>

    Caso o botão acima não funcione copie e cole o link a seguir em seu navegador:
    <a href="{{config('app.site_url').'entrar'}}" class="text-wrapper">{{config('app.site_url').'entrar'}}</a>

    {{-- Footer --}}
    @slot('footer')
        @component('mail::footer')
            © {{ date('Y') }} {{ config('app.site_url') }}.
        @endcomponent
    @endslot
@endcomponent


