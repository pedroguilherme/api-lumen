@component('mail::layout')
    {{-- Header --}}
    @slot('header')
        @component('mail::header', ['url' => config('app.site_url')])
            <img class='logo' src="{{Storage::disk('s3')->url('app/site/geral/logo.png')}}" alt="Logo"/>
        @endcomponent
    @endslot

    <p style="text-align: justify;">
        @if($publisher->type == 'J')
            Revenda <strong>{{$publisher->name}}</strong>, viemos lembrá-lo(a) do vencimento da fatura,
            no valor de R$ {{number_format($billing->value, 2, ',', '.')}}, com a
            data {{\Carbon\Carbon::parse($publisher->payment_nextcheck)->format('d/m/Y')}} encontra-se em aberto.
            <br/>
            Qualquer dúvida ou problema para efetuar o pagamento, entre em contato
            conosco {{!empty($cellphone) ? 'pelo telefone '.$cellphone.' ou' : ''}} responda este e-mail.
            <br/>
            Clique no link abaixo para efetuar o pagamento:
            <br/>
            <a href="{{($billing->boleto_url ?? config('app.site_url'))}}">Link para efetuar pagamento</a>
        @else
            Prezado <strong>{{$publisher->name}}</strong>, viemos lembrá-lo(a) do vencimento da fatura,
            no valor de R$ {{number_format($billing->value, 2, ',', '.')}},
            com a data {{\Carbon\Carbon::parse($billing->expiration)->format('d/m/Y')}} encontra-se em aberto.
            <br/>
            Qualquer dúvida ou problema para efetuar o pagamento, entre em contato
            conosco {{!empty($cellphone) ? 'pelo telefone '.$cellphone.' ou' : ''}} responda este e-mail.
            <br/>
            Clique no link abaixo para efetuar o pagamento:
            <br/>
            <a href="{{(config('app.site_url'))}}">Link para efetuar pagamento</a>
        @endif
        <br/>
        Caso o pagamento já tenha sido efetuado, por favor, desconsidere este aviso.
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


