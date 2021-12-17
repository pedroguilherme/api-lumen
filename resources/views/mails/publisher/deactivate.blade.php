@component('mail::layout')
    {{-- Header --}}
    @slot('header')
        @component('mail::header', ['url' => config('app.site_url')])
            <img class='logo' src="{{Storage::disk('s3')->url('app/site/geral/logo.png')}}" alt="Logo"/>
        @endcomponent
    @endslot

    <p style="text-align: justify;">
    @if($origin == 'admin')
        Nós da Empresa estamos <strong>desativando</strong> você,
        <br/>
        <br/>
    @else
        A
    @endif
        {{$publisher->type == 'J' ? 'Revenda ' : ''}}
        <strong>{{$publisher->company_name ?? $publisher->name}}</strong>
        de {{$publisher->type == 'J' ? 'CNPJ' : 'CPF'}} <strong>{{$publisher->cpf_cnpj}}</strong>
        @if($origin == 'publisher')
            realizou a desativação do cadastro
        @endif
        no dia {{\Illuminate\Support\Carbon::now()->format('d/m/Y á\s H:i:s')}} pelo motivo
        <strong>{{$publisher->deleted_reason}}</strong>.
    </p>
    <br/>
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


