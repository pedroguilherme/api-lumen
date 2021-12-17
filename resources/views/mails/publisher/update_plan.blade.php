@component('mail::layout')
    {{-- Header --}}
    @slot('header')
        @component('mail::header', ['url' => config('app.site_url')])
            <img class='logo' src="{{Storage::disk('s3')->url('app/site/geral/logo.png')}}" alt="Logo"/>
        @endcomponent
    @endslot

    <p style="text-align: justify;">
        {{$publisher->type == 'J' ? 'A Revenda ' : ''}}
        <strong>{{$publisher->company_name ?? $publisher->name}}</strong>
        de {{$publisher->type == 'J' ? 'CNPJ' : 'CPF'}} <strong>{{$publisher->cpf_cnpj}}</strong> realizou a troca do
        plano <strong>{{$oldPlan->name}}</strong> no
        dia {{\Illuminate\Support\Carbon::make($publisher->updated_at)->format('d/m/Y á\s H:i:s')}} para o plano
        <strong>{{$newPlan->name}}</strong>.
        <br/>
        <br/>
        O início da utilização do novo plano será no término do plano vigente.
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


