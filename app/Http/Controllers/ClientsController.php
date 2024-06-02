<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\JsonResponse;

class ClientsController extends Controller
{
    public function index (): JsonResponse
    {
        $ids = Client::all()->pluck('id');

        return response()->json($ids);
    }
}
