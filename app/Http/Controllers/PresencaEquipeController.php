<?php

namespace App\Http\Controllers;

use App\Models\PresencaEquipe;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class PresencaEquipeController extends Controller
{
    public function registrarEntrada(Request $request)
    {
        PresencaEquipe::updateOrCreate(
            [
                'user_id' => Auth::id(),
                'data' => Carbon::today()->toDateString(),
            ],
            [
                'entrada' => Carbon::now()->toTimeString(),
            ]
        );

        return back()->with('success', 'Entrada registrada com sucesso!');
    }

    public function registrarSaida(Request $request)
    {
        $presenca = PresencaEquipe::where('user_id', Auth::id())
            ->where('data', Carbon::today()->toDateString())
            ->first();

        if ($presenca) {
            $presenca->update([
                'saida' => Carbon::now()->toTimeString(),
            ]);
            return back()->with('success', 'Saída registrada com sucesso!');
        }

        return back()->with('error', 'Registro de entrada não encontrado para hoje.');
    }
}
