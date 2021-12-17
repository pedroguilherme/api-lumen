<?php

namespace App\Http\Controllers\V1\Testes;

use App\Exceptions\CustomException;
use App\Http\Controllers\Controller;
use App\Mail\SuccessPayment;
use App\Models\Publisher;
use App\Services\ChargesService;
use App\Services\PagarMeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use PagarMe\PagarMe;

class ExampleController extends Controller
{
    public function get(Request $request)
    {
        dd(Hash::make($request->get('senha')));
    }

    /**
     * @throws CustomException
     */
    public function post(Request $request)
    {
        dd(":) 1.0");
//        $publisher = Publisher::find(55);
//        $chargeService = new ChargesService();
//        $diff = $chargeService->updateFreeActive($publisher);
//        dd($diff);
    }

    public function phpInfo(Request $request)
    {
        phpinfo();
    }
}
